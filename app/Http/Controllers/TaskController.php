<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Mail\TaskCreatedNotification;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::with('user')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:' . implode(',', Task::getStatusOptions()),
            'due_date' => 'nullable|date|after:today',
            'priority' => 'required|in:' . implode(',', Task::getPriorityOptions())
        ]);

        $user = $request->user();

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'completed' => $validated['status'] === Task::STATUS_COMPLETED,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'user_id' => $user->id,
        ]);

        Mail::to($user)->queue(new TaskCreatedNotification($task, $user));

        return $this->successResponse($task->load('user'), 'Task created successfully', 201);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        return $this->successResponse($task->load('user'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:' . implode(',', Task::getStatusOptions()),
            'due_date' => 'nullable|date|after:today',
            'priority' => 'sometimes|required|in:' . implode(',', Task::getPriorityOptions())
        ]);

        if (isset($validated['status'])) {
            $validated['completed'] = $validated['status'] === Task::STATUS_COMPLETED;
        }

        $task->update($validated);
        $task->load('user');

        return $this->successResponse($task, 'Task updated successfully');
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully', 204);
    }

    public function markAsCompleted(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        $task->update([
            'status' => Task::STATUS_COMPLETED,
            'completed' => true
        ]);
        $task->load('user');

        return $this->successResponse($task, 'Task marked as completed');
    }

    public function getOverdueTasks(Request $request)
    {
        $user = $request->user();
        $cacheKey = "user_{$user->id}_overdue_tasks";
        
        $overdueTasks = Cache::remember($cacheKey, 60, function () use ($user) {
            return $user->tasks()
                ->overdue()
                ->pending()
                ->get();
        });

        return $this->successResponse($overdueTasks);
    }

    public function getHighPriorityTasks(Request $request)
    {
        $user = $request->user();
        $cacheKey = "user_{$user->id}_high_priority_tasks";
        
        $highPriorityTasks = Cache::remember($cacheKey, 60, function () use ($user) {
            return $user->tasks()
                ->highPriority()
                ->pending()
                ->get();
        });

        return $this->successResponse($highPriorityTasks);
    }

    private function clearUserTasksCache($userId)
    {
        $patterns = [
            "user_{$userId}_tasks_*",
            "user_{$userId}_overdue_tasks",
            "user_{$userId}_high_priority_tasks"
        ];

        foreach ($patterns as $pattern) {
            $keys = Cache::get($pattern);
            if ($keys) {
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
        }
    }

    private function generateCacheKey($userId, $params)
    {
        $params['user_id'] = $userId;
        ksort($params);
        return 'user_' . $userId . '_tasks_' . md5(json_encode($params));
    }
}
