<?php

namespace Sawirricardo\LaravelFilepond;

use Illuminate\Support\Facades\Crypt;

class Filepond
{
    public function getFilePathFromServerId($serverId)
    {
        abort_if(str($serverId)->trim()->isEmpty(), 400, 'The given file path was invalid');

        $filePath = Crypt::decryptString($serverId);

        $isFilePathValid = str($filePath)->startsWith(config('filepond.directory'))
            || str($filePath)->startsWith(config('filepond.chunk_directory'));

        abort_unless($isFilePathValid, 400, 'The given file path was invalid');

        return $filePath;
    }
}
