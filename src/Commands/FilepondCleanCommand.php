<?php

namespace Sawirricardo\LaravelFilepond\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FilepondCleanCommand extends Command
{
    public $signature = 'filepond:clean {days=7}';

    public $description = 'Clean temporary files uploaded from FilePond';

    public function handle(): int
    {
        collect(Storage::disk(config('filament.disk'))->listContents(config('filepond.directory'), true))
            ->each(function ($file) {
                if ($file['type'] != 'file') {
                    return;
                }

                if ($file['timestamp'] < now()->subDays($this->argument('days'))->getTimestamp()) {
                    Storage::disk(config('filament.disk'))
                        ->deleteDirectory(dirname($file['path']));
                }
            });

        $this->comment('All done');

        return self::SUCCESS;
    }
}
