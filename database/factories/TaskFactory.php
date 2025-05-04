<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3), // Título com 3 palavras
            'description' => fake()->paragraph(2), // Descrição com 2 parágrafos
            'status' => fake()->randomElement(['PENDING', 'COMPLETED']), // Status aleatório
            'priority' => fake()->randomElement(['LOW', 'MEDIUM', 'HIGH']), // Prioridade aleatória
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'), // Data de vencimento opcional no próximo mês
            'user_id' => User::factory(), // Cria um novo usuário ou associa a um existente
            // 'completed' será definido com base no status se necessário no teste ou controller
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'COMPLETED',
        ]);
    }
}
