<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_only_see_their_own_tasks()
    {
        // Create two users
        /** @var \App\Models\User $userA */
        $userA = User::factory()->create();
        /** @var \App\Models\User $userB */
        $userB = User::factory()->create();

        // User A creates a task
        /** @var \App\Models\Task $taskA */
        $taskA = Task::factory()->create([
            'user_id' => $userA->id,
            'title' => 'Task for User A',
        ]);

        // User B creates a task
        /** @var \App\Models\Task $taskB */
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'title' => 'Task for User B',
        ]);

        // Act & Assert for User A
        $responseA = $this->actingAs($userA)->getJson('/api/tasks');
        $responseA->assertStatus(200);
        $responseA->assertJsonCount(1);
        $responseA->assertJsonFragment(['id' => $taskA->id]);
        $responseA->assertJsonMissing(['id' => $taskB->id]);

        // Act & Assert for User B
        $responseB = $this->actingAs($userB)->getJson('/api/tasks');
        $responseB->assertStatus(200);
        $responseB->assertJsonCount(1);
        $responseB->assertJsonFragment(['id' => $taskB->id]);
        $responseB->assertJsonMissing(['id' => $taskA->id]);
    }

    public function test_user_cannot_update_others_task()
    {
        /** @var \App\Models\User $userA */
        $userA = User::factory()->create();
        /** @var \App\Models\User $userB */
        $userB = User::factory()->create();

        /** @var \App\Models\Task $taskA */
        $taskA = Task::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->putJson("/api/tasks/{$taskA->id}", [
            'title' => 'Hacked Title'
        ]);

        $response->assertStatus(403);
    }
}
