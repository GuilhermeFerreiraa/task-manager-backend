<?php

namespace App\Console\Commands;

use App\Mail\TaskCreatedNotification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendTestEmail extends Command
{
    protected $signature = 'email:test {email?}';

    protected $description = 'Send a test email to Mailtrap';

    public function handle()
    {
        $email = $this->argument('email') ?? 'test_' . Str::random(8) . '@example.com';
        
        $this->info("Sending test email to: {$email}");

        $user = User::factory()->create([
            'email' => $email,
            'name' => 'Test User'
        ]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test task via command',
            'description' => 'This is a real test email to Mailtrap via command line.',
            'status' => 'PENDING',
            'priority' => 'HIGH'
        ]);

        Mail::to($user)->queue(new TaskCreatedNotification($task, $user));

        $this->info('Email sent successfully! Check your Mailtrap inbox.');
        
        return Command::SUCCESS;
    }
} 