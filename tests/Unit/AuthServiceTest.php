<?php

namespace Tests\Unit;

use App\Exceptions\CustomMessageException;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    // Testa o registro de um novo usuário
    public function test_register_success()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $result = $this->authService->register($data);

        $this->assertArrayHasKey('token', $result);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    // Testa o login com credenciais corretas
    public function test_login_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('token', $result);
    }

    // Testa o login com credenciais incorretas
    public function test_login_failure()
    {
        $this->expectException(CustomMessageException::class);

        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ];

        $this->authService->login($credentials);
    }
}
