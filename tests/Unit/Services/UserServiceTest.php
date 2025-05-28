<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class UserServiceTest extends TestCase
{
    use WithFaker;

    protected $userRepository;
    protected $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->userService = new UserService($this->userRepository);
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password123'
        ];

        $user = new User($userData);
        $user->_id = $this->faker->uuid;

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userData) {
                return $arg['name'] === $userData['name'] 
                    && $arg['email'] === $userData['email'] 
                    && password_verify($userData['password'], $arg['password']);
            }))
            ->andReturn($user);

        $result = $this->userService->create($userData);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userData['name'], $result->name);
        $this->assertEquals($userData['email'], $result->email);
    }

    public function test_can_find_user_by_email(): void
    {
        $email = $this->faker->email;
        $user = new User(['email' => $email]);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $result = $this->userService->findByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->email);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}