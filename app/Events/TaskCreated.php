<?php

namespace App\Events;

use App\Models\Task; // Importe seu model Task
use App\Models\User; // Importe seu model User
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Importante!
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated implements ShouldBroadcast // Implementar ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Task $task; // Dados que você quer enviar (opcional)
    private int $userId; // ID do usuário para o canal privado

    /**
     * Create a new event instance.
     *
     * @param Task $task A tarefa que foi criada
     * @param int $userId O ID do usuário dono da tarefa
     */
    public function __construct(Task $task, int $userId)
    {
        $this->task = $task;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Define o canal privado para o usuário específico
        // O nome DEVE corresponder exatamente ao que o frontend está ouvindo
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     * Define o nome do evento que o frontend vai ouvir (o que vem depois do ponto)
     * Se omitido, o Laravel usa o nome completo da classe (App\Events\TaskCreated)
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'TaskCreated'; // DEVE corresponder ao '.TaskCreated' no frontend
    }

    /**
     * Get the data to broadcast.
     * Define o payload (dados) que será enviado junto com o evento.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Você pode enviar os dados da tarefa, ou apenas um sinalizador
        return ['task' => $this->task->toArray()];
        // Ou return ['message' => 'Nova tarefa criada!'];
        // Ou return []; // Se não precisar enviar dados
    }
}
