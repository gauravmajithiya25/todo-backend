<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_todo_using_validated_input(): void
    {
        $response = $this->postJson('/api/todos', [
            'title' => 'Buy groceries',
            'description' => 'Milk and bread',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Buy groceries')
            ->assertJsonPath('data.description', 'Milk and bread')
            ->assertJsonPath('data.is_completed', false);

        $this->assertDatabaseHas('todos', ['title' => 'Buy groceries']);
    }

    public function test_it_validates_todo_creation_input(): void
    {
        $this->postJson('/api/todos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_it_updates_a_todo_using_validated_input(): void
    {
        $todo = Todo::create(['title' => 'Old title']);

        $this->patchJson("/api/todos/{$todo->id}", [
            'title' => 'Updated title',
            'is_completed' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.is_completed', true);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'Updated title',
            'is_completed' => true,
        ]);
    }

    public function test_it_lists_todos_with_request_filters(): void
    {
        Todo::create(['title' => 'Active task']);
        Todo::create(['title' => 'Completed task', 'is_completed' => true]);

        $this->getJson('/api/todos?status=completed&search=Completed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Completed task');
    }
}
