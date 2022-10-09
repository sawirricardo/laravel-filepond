<?php

namespace Sawirricardo\LaravelFilepond;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class Filepond
{
    public function getFilePathFromServerId($serverId)
    {
        abort_if(str($serverId)->trim()->isEmpty(), 400, 'The given file path was invalid');

        $filePath = Crypt::decryptString($serverId);

        $isFilePathValid = str($filePath)->startsWith(config('filepond.directory'))
            || str($filePath)->startsWith(config('filepond.chunk_directory'));

        if (is_dir($path = Storage::disk(config('filepond.disk'))->path($filePath))) {
            $filePath = collect(Storage::disk(config('filepond.disk'))->files($path))->first();
            $isFilePathValid = ! empty($filePath);
        }

        abort_unless($isFilePathValid, 400, 'The given file path was invalid');

        return $filePath;
    }
}
