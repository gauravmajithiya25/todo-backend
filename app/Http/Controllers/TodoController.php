<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $todos = Todo::query()
        ->when($request->status === 'completed', fn ($q) => $q->where('is_completed', true))
        ->when($request->status === 'active', fn ($q) => $q->where('is_completed', false))
        ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
        ->latest()
        ->paginate($request->integer('per_page', 10));

        return TodoResource::collection($todos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTodoRequest $request)
    {
        $todo = Todo::create($request->validated());
        return (new TodoResource($todo))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        return new TodoResource($todo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        $todo->delete();

        return response()->noContent();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTodoRequest $request, Todo $todo)
    {
       $todo->update($request->validated());
        return new TodoResource($todo);
    }

    /**
     * Remove the specified resource from storage.
     */
    // GET /api/todos/trashed
    public function trashed(Request $request)
    {
        return TodoResource::collection(
            Todo::onlyTrashed()->latest('deleted_at')->paginate($request->integer('per_page', 10))
        );
    }

    // PATCH /api/todos/{id}/restore
    public function restore(int $id)
    {
        $todo = Todo::onlyTrashed()->findOrFail($id);
        $todo->restore();
        return new TodoResource($todo);
    }

    // DELETE /api/todos/{id}/force
    public function forceDelete(int $id)
    {
        Todo::onlyTrashed()->findOrFail($id)->forceDelete();
        return response()->noContent();
    }
}
