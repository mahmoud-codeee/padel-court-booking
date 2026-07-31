<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_log_in_with_correct_credentials_and_receive_a_token(): void
    {
        Admin::query()->create(['name' => 'Admin', 'email' => 'admin@padel.local', 'password' => 'secret123']);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@padel.local',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token', 'admin' => ['id', 'name', 'email']]);
    }

    #[Test]
    public function login_fails_with_wrong_password(): void
    {
        Admin::query()->create(['name' => 'Admin', 'email' => 'admin@padel.local', 'password' => 'secret123']);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@padel.local',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function admin_routes_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/admin/courts')->assertStatus(401);
        $this->getJson('/api/admin/bookings')->assertStatus(401);
    }

    #[Test]
    public function authenticated_admin_can_access_protected_routes(): void
    {
        $admin = Admin::query()->create(['name' => 'Admin', 'email' => 'admin@padel.local', 'password' => 'secret123']);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/courts')
            ->assertStatus(200);
    }
}
