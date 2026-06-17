<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
    }

    public function test_admin_login_page_loads()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_admin_can_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'ashouraligpt@gmail.com',
            'password' => env('ADMIN_PASSWORD', 'ChangeMe123!'),
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    public function test_manager_can_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'manager@store.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    public function test_customer_cannot_login_as_admin()
    {
        $response = $this->post('/admin/login', [
            'email' => 'customer@store.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_invalid_credentials_fail()
    {
        $response = $this->post('/admin/login', [
            'email' => 'ashouraligpt@gmail.com',
            'password' => 'not-' . env('ADMIN_PASSWORD', 'ChangeMe123!'),
        ]);

        $response->assertSessionHasErrors('email');
    }
}
