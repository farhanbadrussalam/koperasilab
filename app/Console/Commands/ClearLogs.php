<?php
// In app/Console/Commands/ClearLogs.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class ClearLogs extends Command
{
    protected $signature = 'log:clear';
    protected $description = 'Clear all Laravel log files';

    public function handle()
    {
        foreach (glob(storage_path('logs') . '/*.log') as $file) {
            file_put_contents($file, '');
        }
        $this->info('Logs cleared successfully.');
    }
}
