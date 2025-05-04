<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;

    /**
     * Create a new event instance.
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('private-tasks');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'task.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith()
    {
        if ($this->task instanceof LengthAwarePaginator) {
            return [
                'tasks' => $this->task->items(),
                'pagination' => [
                    'total' => $this->task->total(),
                    'per_page' => $this->task->perPage(),
                    'current_page' => $this->task->currentPage(),
                    'last_page' => $this->task->lastPage(),
                ]
            ];
        }

        if ($this->task instanceof Collection) {
            return [
                'tasks' => $this->task->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $task->status,
                        'completed' => $task->completed,
                        'due_date' => $task->due_date,
                        'priority' => $task->priority,
                        'user' => $task->user,
                        'created_at' => $task->created_at,
                        'updated_at' => $task->updated_at,
                    ];
                })->toArray()
            ];
        }

        return [
            'id' => $this->task->id,
            'title' => $this->task->title,
            'description' => $this->task->description,
            'status' => $this->task->status,
            'completed' => $this->task->completed,
            'due_date' => $this->task->due_date,
            'priority' => $this->task->priority,
            'user' => $this->task->user,
            'created_at' => $this->task->created_at,
            'updated_at' => $this->task->updated_at,
        ];
    }
} 