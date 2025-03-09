<?php

namespace Tests\Feature;

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Status::insert([
            [
                'id' => 1,
                'name' => 'Выполнено',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Невыполнено',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $this->user = User::factory()->create();
    }

    /** @test */
    public function can_create_task()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/task/create', [
                'title' => 'Test Task',
                'text' => 'Test Description'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'task' => [
                    'id',
                    'title',
                    'text',
                    'status_id'
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'text' => 'Test Description',
            'user_id' => $this->user->id,
            'status_id' => 2
        ]);
    }

    /** @test */
    public function can_list_tasks()
    {
        Task::create([
            'title' => 'Task 1',
            'text' => 'Description 1',
            'user_id' => $this->user->id,
            'status_id' => 2
        ]);

        Task::create([
            'title' => 'Task 2',
            'text' => 'Description 2',
            'user_id' => $this->user->id,
            'status_id' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/task/index');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'status_id',
                        'title',
                        'text',
                        'created_at',
                        'updated_at',
                        'status' => [
                            'id',
                            'name',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]);
    }

    /** @test */
    public function can_update_own_task()
    {
        $task = Task::create([
            'title' => 'Original Title',
            'text' => 'Original Description',
            'user_id' => $this->user->id,
            'status_id' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/task/update/{$task->id}", [
                'title' => 'Updated Title',
                'text' => 'Updated Description',
                'status_id' => 1
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'task'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'text' => 'Updated Description',
            'status_id' => 1
        ]);
    }

    /** @test */
    public function can_delete_own_task()
    {
        $task = Task::create([
            'title' => 'Task to Delete',
            'text' => 'This will be deleted',
            'user_id' => $this->user->id,
            'status_id' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/task/destroy/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => __('actions.deleted')]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function can_search_tasks()
    {
         Task::create([
            'title' => 'Unique Task Title',
            'text' => 'Searchable Description',
            'user_id' => $this->user->id,
            'status_id' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/task/search?query=Unique');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tasks' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'text',
                            'status_id'
                        ]
                    ]
                ]
            ]);
    }
}
