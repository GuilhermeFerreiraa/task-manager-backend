<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Events\TaskUpdated;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        
        $query = $user->tasks();
        
        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }
        
        // Ordenação
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $cacheKey = $this->generateCacheKey($user->id, $request->all());
        
        $tasks = Cache::remember($cacheKey, 60, function () use ($query, $perPage) {
            return $query->paginate($perPage);
        });

        broadcast(new TaskUpdated($tasks))->toOthers();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,completed',
            'due_date' => 'nullable|date|after:today',
            'priority' => 'required|in:low,medium,high'
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'user_id' => auth()->id(),
        ]);

        $this->clearUserTasksCache($request->user()->id);

        broadcast(new TaskUpdated($task))->toOthers();

        return response()->json($task->load('user'), 201);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $cacheKey = "task_{$task->id}";
        
        $cachedTask = Cache::remember($cacheKey, 60, function () use ($task) {
            return $task;
        });

        broadcast(new TaskUpdated($cachedTask))->toOthers();

        return response()->json($cachedTask);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:pending,completed',
            'due_date' => 'nullable|date|after:today',
            'priority' => 'sometimes|required|in:low,medium,high'
        ]);

        $task->update($validated);
        $task->load('user');

        Cache::forget("task_{$task->id}");
        $this->clearUserTasksCache($request->user()->id);

        broadcast(new TaskUpdated($task))->toOthers();

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        Cache::forget("task_{$task->id}");
        $this->clearUserTasksCache($request->user()->id);

        broadcast(new TaskUpdated($task))->toOthers();

        return response()->json(null, 204);
    }

    public function markAsCompleted(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update(['status' => 'completed']);

        Cache::forget("task_{$task->id}");
        $this->clearUserTasksCache($request->user()->id);

        broadcast(new TaskUpdated($task))->toOthers();

        return response()->json($task);
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

        broadcast(new TaskUpdated($overdueTasks))->toOthers();

        return response()->json($overdueTasks);
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

        broadcast(new TaskUpdated($highPriorityTasks))->toOthers();

        return response()->json($highPriorityTasks);
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
