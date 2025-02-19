<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear {days=30 : The number of days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old log data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = array_keys(iterator_to_array(
            Finder::create()
                ->in(storage_path('logs'))
                ->ignoreDotFiles(true)
                ->ignoreVCS(true)
                ->name('*.log')
                ->filter(function (SplFileInfo $fileInfo): bool {
                    $isDaily = preg_match('/^.*-\d{4}-\d{2}-\d{2}.log$/', $fileInfo->getBasename());
                    if (! $isDaily) {
                        return 'laravel';
                    }

                    return Date::createFromTimestamp($fileInfo->getMTime())->diffInDays(now()) > $this->argument('days');
                })
                ->files()
        ));

        $this->output->listing($files);
        File::delete($files);
        $this->info(__('Logs have been cleared!'));
        Log::info(__('Log Cleared.'));
    }
}
