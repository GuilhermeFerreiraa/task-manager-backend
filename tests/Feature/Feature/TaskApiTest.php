<?php

namespace Tests\Feature;

use App\Mail\TaskCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendQueuedMailable;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Testa se um usuário autenticado pode listar suas tarefas.
     *
     * @return void
     */
    public function test_authenticated_user_can_list_tasks(): void
    {
        // 1. Cria um usuário
        $user = User::factory()->create();

        // 2. Cria tarefas para este usuário
        Task::factory()->count(3)->create(['user_id' => $user->id]);

        // 3. Cria uma tarefa para outro usuário (para garantir que não seja listada)
        $otherUser = User::factory()->create();
        Task::factory()->create(['user_id' => $otherUser->id]);

        // 4. Autentica o usuário usando o guardião 'api' (JWT)
        $this->actingAs($user, 'api');

        // 5. Faz a requisição GET para a API
        $response = $this->getJson('/api/v1/tasks');

        // 6. Verifica se a resposta foi OK (200)
        $response->assertStatus(200);

        // 7. Verifica se a resposta contém exatamente 3 tarefas (as do usuário autenticado)
        $response->assertJsonCount(3, 'data');

        // 8. Verifica se os dados de uma das tarefas estão corretos (opcional, mas bom)
        $response->assertJsonFragment(['user_id' => $user->id]);

        // 9. Verifica se a tarefa do outro usuário NÃO está na resposta
        $response->assertJsonMissing(['user_id' => $otherUser->id]);
    }

    /**
     * Testa se um usuário autenticado pode criar uma tarefa.
     *
     * @return void
     */
    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $taskData = [
            'title' => 'Nova Tarefa de Teste',
            'description' => 'Descrição da tarefa de teste.',
            'status' => 'PENDING',
            'priority' => 'MEDIUM',
            'due_date' => now()->addDay()->format('Y-m-d')
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201) // Apenas verificar status 201 Created
                 ->assertJsonFragment([ // Verificar se os dados enviados estão na resposta
                     'title' => 'Nova Tarefa de Teste',
                     'user_id' => $user->id,
                     'priority' => 'MEDIUM'
                 ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Nova Tarefa de Teste',
            'user_id' => $user->id
        ]);
    }

    /**
     * Testa se um usuário autenticado pode ver sua própria tarefa.
     *
     * @return void
     */
    public function test_authenticated_user_can_view_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'api');

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $task->id,
                     'title' => $task->title,
                     'user_id' => $user->id
                 ]);
    }

    /**
     * Testa se um usuário não pode ver tarefa de outro usuário.
     *
     * @return void
     */
    public function test_user_cannot_view_others_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user, 'api'); // Autentica como $user

        $response = $this->getJson("/api/v1/tasks/{$otherTask->id}");

        // Esperamos um 403 (Forbidden) agora por causa da Policy
        $response->assertStatus(403);
    }

    /**
     * Testa se um usuário autenticado pode atualizar sua própria tarefa.
     *
     * @return void
     */
    public function test_authenticated_user_can_update_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'api');

        $updateData = [
            'title' => 'Título Atualizado',
            'status' => 'COMPLETED',
            'priority' => 'HIGH'
        ];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => 'Título Atualizado',
                     'status' => 'COMPLETED',
                     'priority' => 'HIGH'
                 ]);

        // Verificar se o banco de dados foi atualizado
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Atualizado'
        ]);
    }

    /**
     * Testa se um usuário não pode atualizar tarefa de outro usuário.
     *
     * @return void
     */
    public function test_user_cannot_update_others_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user, 'api'); // Autentica como $user

        $updateData = ['title' => 'Tentativa de Atualização'];

        $response = $this->putJson("/api/v1/tasks/{$otherTask->id}", $updateData);

        // Esperamos 403 (Forbidden) agora por causa da Policy
        $response->assertStatus(403);

        // Garantir que a tarefa original não foi modificada
        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'title' => $otherTask->title // Título original
        ]);
    }

    /**
     * Testa se um usuário autenticado pode excluir sua própria tarefa.
     *
     * @return void
     */
    public function test_authenticated_user_can_delete_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'api');

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(204); // Verificar status 204 No Content

        // Verificar se a tarefa foi removida do banco (ou marcada como excluída se usar SoftDeletes)
        // Como a migration adiciona SoftDeletes, usamos assertSoftDeleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Testa se um usuário não pode excluir tarefa de outro usuário.
     *
     * @return void
     */
    public function test_user_cannot_delete_others_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user, 'api'); // Autentica como $user

        $response = $this->deleteJson("/api/v1/tasks/{$otherTask->id}");

        // Esperamos 403 (Forbidden) agora por causa da Policy
        $response->assertStatus(403);

        // Garantir que a tarefa ainda existe no banco
        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
        ]);
    }

    /**
     * Testa se a criação de tarefa falha sem um título.
     *
     * @return void
     */
    public function test_create_task_fails_without_title(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $taskData = [
            // 'title' => 'Título Ausente', // Campo obrigatório faltando
            'description' => 'Descrição válida.',
            'status' => 'PENDING',
            'priority' => 'LOW',
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(422) // Verificar status 422 Unprocessable Entity
                 ->assertJsonValidationErrors('title'); // Verificar erro de validação no campo 'title'
    }

    /**
     * Testa se o email de notificação é enfileirado na criação da tarefa.
     *
     * @return void
     */
    public function test_email_is_queued_when_task_is_created(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $taskData = [
            'title' => 'Tarefa para Testar Email',
            'description' => 'Descrição da tarefa para testar email.',
            'status' => 'PENDING',
            'priority' => 'MEDIUM',
            'due_date' => now()->addDay()->format('Y-m-d')
        ];

        $this->postJson('/api/v1/tasks', $taskData);

        Mail::assertQueued(TaskCreatedNotification::class);
    }

    /**
     * Testa se um usuário autenticado pode marcar sua própria tarefa como concluída.
     *
     * @return void
     */
    public function test_authenticated_user_can_mark_task_as_completed(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'PENDING',
            'completed' => false
        ]);
        
        $this->actingAs($user, 'api');

        $response = $this->patchJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'COMPLETED',
                     'completed' => true
                 ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'COMPLETED',
            'completed' => true
        ]);
    }

    /**
     * Testa se um usuário não pode marcar como concluída a tarefa de outro usuário.
     *
     * @return void
     */
    public function test_user_cannot_mark_others_task_as_completed(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'PENDING',
            'completed' => false
        ]);

        $this->actingAs($user, 'api'); // Autentica como $user

        $response = $this->patchJson("/api/v1/tasks/{$otherTask->id}/complete");

        // Esperamos 403 (Forbidden) agora por causa da Policy
        $response->assertStatus(403);

        // Garantir que a tarefa não foi modificada
        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'status' => 'PENDING',
            'completed' => false
        ]);
    }

    public function test_notification_is_sent_via_mailtrap(): void
    {
        Mail::fake();
        
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);
        
        $this->actingAs($user, 'api');

        $taskData = [
            'title' => 'Tarefa com Notificação Real',
            'description' => 'Descrição da tarefa com notificação real.',
            'status' => 'PENDING',
            'priority' => 'HIGH',
            'due_date' => now()->addDays(5)->format('Y-m-d')
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);
        
        $response->assertStatus(201);
        
        Mail::assertQueued(TaskCreatedNotification::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
    
    public function test_real_email_delivery_to_mailtrap(): void
    {
        $user = User::factory()->create([
            'email' => 'test-real@example.com'
        ]);
        
        $this->actingAs($user, 'api');

        $taskData = [
            'title' => 'Tarefa para Mailtrap',
            'description' => 'Esta tarefa deve enviar um e-mail real para o Mailtrap.',
            'status' => 'PENDING',
            'priority' => 'HIGH',
            'due_date' => now()->addDays(3)->format('Y-m-d')
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);
        
        $response->assertStatus(201);
        
        $this->assertTrue(true, 'Verifique sua caixa de entrada do Mailtrap para confirmar o recebimento do e-mail');
    }
}
