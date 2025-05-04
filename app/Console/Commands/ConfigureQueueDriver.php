<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ConfigureQueueDriver extends Command
{
    protected $signature = 'queue:configure {driver=database : The queue driver to configure (sync, database, redis)}';

    protected $description = 'Configure the queue driver at runtime';

    public function handle()
    {
        $driver = $this->argument('driver');
        
        if (!in_array($driver, ['sync', 'database', 'redis'])) {
            $this->error("Invalid queue driver: {$driver}");
            return Command::FAILURE;
        }
        
        Config::set('queue.default', $driver);
        
        $this->info("Queue driver configured to: {$driver}");
        
        $this->info('Current configuration:');
        $this->table(
            ['Key', 'Value'],
            [
                ['Driver', Config::get('queue.default')],
                ['Connection', Config::get('queue.connections.' . $driver . '.driver')],
            ]
        );
        
        return Command::SUCCESS;
    }
} 