<?php

namespace App\Http\Controllers;

use App\Services\BenchmarkService;
use Illuminate\Http\Request;

class BenchmarkController extends Controller
{
    private $benchmarkService;

    public function __construct(BenchmarkService $benchmarkService)
    {
        $this->benchmarkService = $benchmarkService;
    }

    public function record(Request $request)
    {
        $headers = $request->headers;
        $content = $request->getContent();

        $hmac = $headers->get('X-Sphene-Hmac-Sha256');
        $checkHmac = base64_encode(hash_hmac('sha256', $content, env('SPHENE_SECRET'), false));

        if ($hmac !== $checkHmac) {
            return response()->json([
                'errors' => [
                    'message' => 'Invalid HMAC '.$hmac.' vs '.$checkHmac,
                ]
            ], 400);
        }

        $content = json_decode($content, true)[0];

        if (!isset($content['hash'])) {
            abort(400, 'Missing hash');
        }

        if ($this->benchmarkService->record($content)) {
            return response()->json([
                'success' => true,
            ]);
        }

        abort(500);
    }

    public function leaderboard(Request $request)
    {
        $this->validate($request, [
            'page' => 'numeric',
            'limit' => 'numeric',
        ]);

        $page = $request->get('page') ?? 0;
        $limit = $request->get('limit') ?? -1;

        $hash = request()->get('hash') ?? null;
        $type = request()->get('type') ?? 'overall';

        $isLegacy = boolval(request()->get('legacy') ?? false);

        return response()->json($this->benchmarkService->leaderboard($hash, $type, $isLegacy, $page, $limit));
    }

    public function history()
    {
        $hash = request()->get('hash') ?? null;
        $type = request()->get('type') ?? 'overall';

        if (!isset($hash)) {
            abort(400, 'Missing hash');
        }

        return response()->json($this->benchmarkService->history($hash, $type));
    }
}
