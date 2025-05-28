<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_list_of_users(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name' => $userData['name']
        ]);
    }

    public function test_can_update_user(): void
    {
        $updateData = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/users/{$this->user->_id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            '_id' => $this->user->_id,
            'email' => $updateData['email'],
            'name' => $updateData['name']
        ]);
    }

    public function test_can_delete_user(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/users/{$this->user->_id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            '_id' => $this->user->_id
        ]);
    }

    public function test_cannot_create_user_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/users', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}