<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'FOUcreative',
            'password' => Hash::make('secure-test-password'),
        ]);

        $response = $this->post('/login', [
            'username' => $user->name,
            'password' => 'secure-test-password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post('/login', [
            'username' => 'FOUcreative',
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors('username');
    }

    public function test_onboarding_token_resolves_existing_client(): void
    {
        $client = Client::create([
            'onboarding_token' => (string) \Illuminate\Support\Str::uuid(),
            'company_name' => 'Test Company',
        ]);

        $this->get('/onboarding/'.$client->onboarding_token)
            ->assertOk();
    }

    public function test_unknown_onboarding_token_returns_not_found(): void
    {
        $this->get('/onboarding/'.\Illuminate\Support\Str::uuid())
            ->assertNotFound();
    }
}
