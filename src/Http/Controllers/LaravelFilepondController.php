<?php

namespace Sawirricardo\LaravelFilepond\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sawirricardo\LaravelFilepond\Filepond;

class LaravelFilepondController
{
    public function head(Request $request)
    {
        $request->validate(['patch' => ['required']]);

        try {
            $finalPath = Crypt::decryptString($request->patch);
        } catch (DecryptException $e) {
            abort(400, 'Invalid encryption for id');
        }
        $id = basename($finalPath);
        $baseDir = config('filepond.chunk_directory').DIRECTORY_SEPARATOR.$id;

        return response()->withHeaders(['Upload-Offset' => $this->getChunksSize($baseDir)]);
    }

    public function store(Request $request)
    {
        $randomId = Str::random();
        $fileLocation = config('filepond.directory').DIRECTORY_SEPARATOR.$randomId;

        if ($request->hasHeader('Upload-Length')) {
            $file = Storage::disk(config('filepond.disk'))
                ->put($fileLocation, '');

            abort_unless($file, 500, 'Could not save file', ['Content-Type' => 'text/plain']);

            $filepondId = Crypt::encryptString($fileLocation);

            return response($filepondId, 200, ['Content-Type' => 'text/plain']);
        }

        $request->validate(['filepond' => config('filepond.rules')]);

        $filename = $request->boolean('use_original_filename')
            ? $request->file('filepond')->getClientOriginalName()
            : $request->file('filepond')->hashName();

        $file = $request->file('filepond')->storeAs($fileLocation, $filename);

        abort_unless($file, 500, 'Could not save file', ['Content-Type' => 'text/plain']);

        $filepondId = Crypt::encryptString($file);

        return response($filepondId, 200, ['Content-Type' => 'text/plain']);
    }

    public function update(Request $request)
    {
        $request->validate(['patch' => ['required']]);

        try {
            $finalPath = Crypt::decryptString($request->patch);
        } catch (DecryptException $e) {
            abort(400, 'Invalid encryption for id');
        }
        $id = basename($finalPath);
        $baseDir = config('filepond.chunk_directory').DIRECTORY_SEPARATOR.$id;

        $length = $request->header('Upload-Length');
        $offset = $request->header('Upload-Offset');
        $isInvalid = ! is_numeric($length) || ! is_numeric($offset);
        abort_if($isInvalid, 400, 'Invalid chunk length or offset', ['Content-Type' => 'text/plain']);

        $chunkPath = $baseDir.DIRECTORY_SEPARATOR.'patch.'.$offset;

        Storage::disk(config('filepond.disk'))
            ->put($chunkPath, $request->getContent(), ['mimetype' => 'application/octet-stream']);

        if ($this->isChunkUploadsInProgress($baseDir, $length)) {
            return response()->noContent();
        }

        Storage::disk(config('filepond.disk'))
            ->put($finalPath, $this->createFinalFile($baseDir), ['mimetype' => 'application/octet-stream']);

        Storage::disk(config('filepond.disk'))
            ->deleteDirectory($baseDir);

        return response()->noContent();
    }

    public function destroy(Request $request, Filepond $filepond)
    {
        $folderPath = dirname(
            $filepond->getFilePathFromServerId($request->getContent())
        );

        abort_unless(
            Storage::disk(config('filepond.disk', 'local'))
                ->deleteDirectory($folderPath),
            500,
        );

        return response()->noContent();
    }

    private function isChunkUploadsInProgress($baseDir, $length)
    {
        return $this->getChunksSize($baseDir) < $length;
    }

    private function getChunksSize($baseDir)
    {
        $chunks = Storage::disk(config('filepond.disk'))->files($baseDir);

        return collect($chunks)
            ->reduce(function ($result, $value) {
                return $result + Storage::disk(config('filepond.disk'))->size($value);
            })
            ?? 0;
    }

    private function createFinalFile($baseDir)
    {
        $chunks = collect(Storage::disk(config('filepond.disk'))->files($baseDir));

        // Sort chunks
        $chunks = $chunks->keyBy(function ($chunk) {
            return substr($chunk, strrpos($chunk, '.') + 1);
        });
        $chunks = $chunks->sortKeys();

        // Append each chunk to the final file
        $data = '';
        foreach ($chunks as $chunk) {
            // Get chunk contents
            $chunkContents = Storage::disk('filepond.disk')->get($chunk);

            // Laravel's local disk implementation is quite inefficient for appending data to existing files
            // To be at least a bit more efficient, we build the final content ourselves, but the most efficient
            // Way to do this would be to append using the driver's capabilities
            $data .= $chunkContents;
            unset($chunkContents);
        }

        return $data;
    }
}
