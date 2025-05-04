<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessEmailQueue extends Command
{
    protected $signature = 'queue:process-emails';

    protected $description = 'Process the email queue';

    public function handle()
    {
        $this->info('Starting to process email queue...');
        
        $this->call('queue:work', [
            '--queue' => 'default',
            '--tries' => 3,
            '--stop-when-empty' => true,
        ]);
        
        $this->info('Queue processing completed.');
        
        return Command::SUCCESS;
    }
} 