<?php

namespace App\Http\Controllers\task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreRequest;
use App\Http\Requests\Task\UpdateRequest;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Rakutentech\LaravelRequestDocs\Attributes as LRD;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * @class TaskController
 */
class TaskController extends Controller
{
    #[LRD\Endpoint(
        title: "Получить список задач",
        description: "Возвращает список задач, принадлежащих пользователю."
    )]
    #[LRD\Response([
        'data' => [
            [
                'id' => 1,
                'title' => 'Первая задача',
                'text' => 'Описание задачи',
                'status_id' => 2,
                'created_at' => '2025-03-08T12:00:00Z'
            ]
        ],
        'links' => [],
        'meta' => []
    ], status: 200)]
    public function index(Request $request)
    {
        $query = $request->user()->tasks()->with('status');

        if ($request->has('status')) {
            $query->where('status_id', $request->input('status'));
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order', 'asc');

            if (in_array($sortBy, ['created_at', 'status_id'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        return response()->json($query->paginate(5));
    }

    #[LRD\Endpoint(
        title: "Создать новую задачу",
        description: "Создаёт новую задачу для пользователя."
    )]
    #[LRD\Response([
        'task' => [
            'id' => 10,
            'title' => 'Новая задача',
            'text' => 'Описание новой задачи',
            'status_id' => 2
        ]
    ], status: 201)]
    public function store(StoreRequest $request)
    {
        $request->validated();

        $task = $request->user()->tasks()->create([
            'title' => $request->input('title'),
            'text' => $request->input('text'),
            'status_id' => 2
        ]);

        return response()->json([
            'task' => $task
        ], 201);
    }

    #[LRD\Endpoint(
        title: "Обновить задачу",
        description: "Обновляет существующую задачу."
    )]
    #[LRD\Response([
        'message' => 'Задача обновлена',
        'task' => [
            'id' => 10,
            'title' => 'Обновленная задача',
            'text' => 'Обновленное описание',
            'status_id' => 1
        ]
    ], status: 200)]
    #[LRD\Response([
        'message' => 'Ошибка авторизации'
    ], status: 403)]
    public function update(UpdateRequest $request, Task $task)
    {
        try {
            Gate::authorize('update', $task);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => __('http-statuses.404')],404);
        }

        $data = $request->validated();

        $task->update($data);

        return response()->json(['message' => __('actions.updated'), 'task' => $task]);
    }

    #[LRD\Endpoint(
        title: "Удалить задачу",
        description: "Удаляет задачу по ID."
    )]
    #[LRD\Response([
        'message' => 'Задача удалена'
    ], status: 200)]
    #[LRD\Response([
        'message' => 'Ошибка авторизации'
    ], status: 403)]
    public function destroy(Task $task)
    {
        try {
            Gate::authorize('update', $task);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => __('http-statuses.404')],404);
        }

        $task->delete();

        return response()->json(['message' => __('actions.deleted')]);
    }

    #[LRD\Endpoint(
        title: "Поиск задач",
        description: "Позволяет искать задачи по названию или тексту."
    )]
    #[LRD\Response([
        'tasks' => [
            [
                'id' => 5,
                'title' => 'Найденная задача',
                'text' => 'Описание найденной задачи',
                'status_id' => 2
            ]
        ]
    ], status: 200)]
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255'
        ]);

        $searchTerm = $request->input('query');

        $tasks = Task::search($searchTerm)
            ->query(function (Builder $query) use ($request) {
                $query->select(['id', 'title', 'text', 'status_id'])
                    ->where('user_id', $request->user()->id);
            })->paginate(5);

        $tasks->load('status');

        return response()->json([
            'tasks' => $tasks
        ]);
    }
}
