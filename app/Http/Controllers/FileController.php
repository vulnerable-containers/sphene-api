<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function indexInfo(Request $request, FileService $fileService)
    {
        $this->validate($request, [
            'files' => 'required|string',
        ]);

        $files = explode(',', $request->get('files'));
        $fileInfo = $fileService->getFileInfo($files);

        return response()->json($fileInfo);
    }
}
