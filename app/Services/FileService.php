<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function getFileInfo(array $files) : ?array
    {
        $filesInfo = [];

        foreach ($files as $filePath) {
            $matchedFiles = [];
            $storageDisk = Storage::disk('cdn');

            if (strpos($filePath, '*') !== false) {
                $pathMatches = [];
                $folder = '';
                
                if (preg_match('/^((?:[^\*]\/?)+)(.+)/', $filePath, $pathMatches)) {
                    $folder = $pathMatches[1] ?? '';
                    $filePath = $pathMatches[2] ?? '';
                } else {
                    $filePath = $filePath;
                }

                $filePath = preg_quote($filePath, '/');
                $filePath = str_replace('\*', '.+?', $filePath);

                $files = $storageDisk->files($folder, true);
                
                foreach ($files as $file) {
                    if (preg_match('/^'.$filePath.'$/', $file)) {
                        $matchedFiles[] = $file;
                    }
                }
            } else {
                $matchedFiles[] = $filePath;
            }
            
            foreach ($matchedFiles as $filePath) {
                $fileExists = $storageDisk->exists($filePath);

                if (!$fileExists) {
                    $filesInfo[$filePath] = [
                        'found' => false
                    ];
                } else {
                    $cacheKey = $filePath.$storageDisk->lastModified($filePath);

                    if (Cache::has($cacheKey)) {
                        $filesInfo[$filePath] = Cache::get($cacheKey);
                    } else {
                        $fullPath = $storageDisk->path($filePath);

                        $fileData = [
                            'found' => true,
                            'size' => $storageDisk->size($filePath),
                            'hash' => hash_file('sha256', $fullPath),
                        ];

                        Cache::put($cacheKey, $fileData, 60 * 60 * 24);
                        $filesInfo[$filePath] = $fileData;
                    }
                }
            }
        }

        return $filesInfo;
    }
}