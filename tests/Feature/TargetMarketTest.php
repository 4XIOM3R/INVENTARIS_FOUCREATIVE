<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TargetMarketTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_autosave_persists_custom_targets_and_restores_them_on_reload(): void
    {
        $client = Client::create([
            'onboarding_token' => (string) Str::uuid(),
            'company_name' => 'Target Market Company',
        ]);

        $this->postJson('/onboarding/'.$client->onboarding_token.'/autosave/1', [
            'company_name' => $client->company_name,
            'target_market' => ['BUMN/BUMD', 'UMKM'],
            'target_market_custom' => [' Startup teknologi ', '', 'Startup teknologi', 'Komunitas kreator'],
        ])->assertOk();

        $this->assertSame(
            ['BUMN/BUMD', 'UMKM', 'Startup teknologi', 'Komunitas kreator'],
            $client->fresh()->target_market,
        );

        $this->get('/onboarding/'.$client->onboarding_token)
            ->assertOk()
            ->assertSee('Startup teknologi')
            ->assertSee('Komunitas kreator');
    }

    public function test_admin_profile_update_preserves_and_normalizes_custom_targets(): void
    {
        $admin = User::factory()->create();
        $client = Client::create([
            'onboarding_token' => (string) Str::uuid(),
            'company_name' => 'Target Market Company',
            'target_market' => ['BUMN/BUMD', 'Target klien lama'],
        ]);

        $this->actingAs($admin)
            ->put('/admin/clients/'.$client->id, [
                'company_name' => $client->company_name,
                'target_market' => ['BUMN/BUMD', 'UMKM'],
                'target_market_custom' => [' Target klien lama ', 'Target klien baru', 'Target klien baru', ''],
            ])
            ->assertRedirect();

        $this->assertSame(
            ['BUMN/BUMD', 'UMKM', 'Target klien lama', 'Target klien baru'],
            $client->fresh()->target_market,
        );
    }
}
