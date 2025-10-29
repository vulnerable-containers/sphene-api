<?php

namespace App\Services;

use App\Models\BenchmarkRecord;
use Illuminate\Support\Facades\Http;

class BenchmarkService
{
    const ACCEPTABLE_TYPES = [
        'overall',
        'idleInstructions',
        'scmInstructions',
        'copScene',
        'vehicleCarrec',
        '10Threads',
        '50Threads',
        '100Threads',
        'kickstartStadium',
        'vehiclePath1',
        'vehiclePath2',
    ];

    public function record($content)
    {
        $content['overall'] = floor($content['overall']);
        $content['benchmarkVersion'] = $content['benchmarkVersion'] ?? 1;

        $record = BenchmarkRecord::create($content);

        if ($record) {
            // @TODO: Run this in a async job instead.
            try {
                $position = $this->getPosition($record);

                $color = 0xfff;

                switch ($position) {
                    case 1:
                        $color = 0xffd700;
                        break;
                    case 2:
                        $color = 0xc0c0c0;
                        break;
                    case 3:
                        $color = 0xcd7f32;
                        break;
                    default:
                        $color = 0x5772cc;
                        break;
                }

                $webhookData = [
                    "content" => "",
                    "tts" => false,
                    "embeds" => [
                        [
                            "type" => "rich",
                            "title" => 'New Sphene benchmark result!',
                            "description" => 'A new benchmark score of **'.$record->overall.'** has been recorded. This places it at position **'.$position.'** in the leaderboards.',
                            "color" => $color,
                            "timestamp" => date("c", strtotime("now")),
                            "footer" => [
                                "text" => 'Benchmark v'.$content['benchmarkVersion'].' | mtasa://benchmark.sphene.dev'
                            ]
                        ]
                    ]
                ];
            
                Http::post(config('services.discord.webhook_url'), $webhookData)->body();
            } catch (\Exception $e) {}
            return $record;
        }

        return false;
    }

    public function leaderboard(?string $hash = null, string $type = 'overall', bool $isLegacy = false, int $page = 0, int $limit = -1)
    {
        if (!in_array($type, self::ACCEPTABLE_TYPES)) {
            return [];
        }

        if ($limit == -1) {
            $limit = 50;
        }

        $records = BenchmarkRecord::orderBy($type, 'desc');
        
        if ($isLegacy) {
            $records = $records->where('benchmarkVersion', '<=', 2);
        } else {
            $records = $records->where('benchmarkVersion', '>', 2);
        }

        $records = $records->skip($page * $limit)->take($limit)->get()->toArray();
        
        $foundSelf = false;

        foreach ($records as $index => $record)
        {
            $records[$index] = [
                'position' => $index + 1,
                'score' => $record[$type],
                'date' => date('j F Y', strtotime($record['created_at'])),
                'version' => $record['benchmarkVersion'],
            ];

            if (isset($hash) && $record['hash'] == $hash) {
                $records[$index]['self'] = true;
                $foundSelf = true;
            }
        }

        if (!$foundSelf) {
            $bestScore = BenchmarkRecord::where('hash', $hash);
            
            if ($isLegacy) {
                $bestScore = $bestScore->where('benchmarkVersion', '<=', 2);
            } else {
                $bestScore = $bestScore->where('benchmarkVersion', '>', 2);
            }

            $bestScore = $bestScore->orderBy($type, 'desc')->first();
            
            if (isset($bestScore)) {
                $position = BenchmarkRecord::where($type, '>', $bestScore->overall);
                
                if ($isLegacy) {
                    $position = $position->where('benchmarkVersion', '<=', 2);
                } else {
                    $position = $position->where('benchmarkVersion', '>', 2);
                }

                $bestScore = $bestScore->count() + 1;

                $records[] = [
                    'position' => $position,
                    'score' => $bestScore->overall,
                    'date' => date('j F Y', strtotime($bestScore->created_at)),
                    'version' => $bestScore->benchmarkVersion,
                    'self' => true,
                ];
            }
        }

        return $records;
    }

    public function history(string $hash, string $type = 'overall')
    {
        if (!in_array($type, self::ACCEPTABLE_TYPES)) {
            return [];
        }

        $records = BenchmarkRecord::where('hash', $hash)->orderBy($type, 'desc')->take(50)->get()->toArray();

        foreach ($records as $index => $record)
        {
            $records[$index] = [
                'position' => $index + 1,
                'score' => $record[$type],
                'version' => $record['benchmarkVersion'],
                'date' => date('j F Y', strtotime($record['created_at'])),
            ];
        }

        return $records;
    }

    public function getPosition(BenchmarkRecord $record)
    {
        return BenchmarkRecord::where('overall', '>', $record->overall)->where('benchmarkVersion', '>=', 3)->count() + 1;
    }
}