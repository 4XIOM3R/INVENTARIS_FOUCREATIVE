<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UploadFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_accepts_varied_file_formats_on_assets_and_autosave_routes(): void
    {
        Storage::fake('public');

        foreach ([false, true] as $autosave) {
            $client = $this->makeClient();
            $uploads = [
                ['logo', 'brand.webp', 'Logo Asli'],
                ['npwp', 'tax.gif', 'NPWP Perusahaan'],
                ['chat_example', 'example.svg', 'Contoh Percakapan Chat'],
                ['logo', 'document.pdf', 'Logo Asli'],
                ['npwp', 'agreement.docx', 'NPWP Perusahaan'],
                ['chat_example', 'archive.zip', 'Contoh Percakapan Chat'],
                ['faq', 'knowledge.xlsx', 'Excel Product Knowledge / FAQ'],
                ['logo', 'brand', 'Logo Asli'],
            ];

            foreach ($uploads as [$field, $filename, $itemName]) {
                $item = $client->onboardingItems()->where('item_name', $itemName)->first();
                $oldPath = $item?->file_path;
                $url = '/onboarding/'.$client->onboarding_token.($autosave ? '/autosave/6' : '/assets');
                $file = UploadedFile::fake()->create($filename, 16, 'application/octet-stream');

                if ($autosave) {
                    $this->postJson($url, [$field => $file])->assertOk();
                } else {
                    $this->post($url, [$field => $file])->assertRedirect();
                }

                $item = $client->onboardingItems()->where('item_name', $itemName)->firstOrFail();
                $this->assertSame('SUDAH LENGKAP', $item->status);
                Storage::disk('public')->assertExists($item->file_path);
                if ($oldPath) {
                    Storage::disk('public')->assertMissing($oldPath);
                }
                if ($filename === 'brand') {
                    $this->assertStringEndsNotWith('.', $item->file_path);
                }
            }
        }
    }

    public function test_onboarding_rejects_executable_extensions_on_both_upload_routes(): void
    {
        Storage::fake('public');
        $client = $this->makeClient();
        $file = fn () => UploadedFile::fake()->create('malicious.PHP', 16, 'application/octet-stream');

        $this->post('/onboarding/'.$client->onboarding_token.'/assets', ['logo' => $file()])
            ->assertSessionHasErrors('logo');

        $this->postJson('/onboarding/'.$client->onboarding_token.'/autosave/6', ['logo' => $file()])
            ->assertJsonValidationErrors('logo');

        $this->assertDatabaseMissing('client_onboarding_items', [
            'client_id' => $client->id,
            'item_name' => 'Logo Asli',
        ]);
    }

    public function test_admin_accepts_general_files_and_rejects_executable_extensions(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $client = $this->makeClient();
        $item = $client->onboardingItems()->create([
            'category' => 'asset',
            'item_name' => 'Lampiran',
            'status' => 'MASIH PROSES',
        ]);
        $oldPath = 'clients/'.$client->id.'/old-file.pdf';
        Storage::disk('public')->put($oldPath, 'old');
        $item->update(['file_path' => $oldPath]);

        $this->actingAs($admin)
            ->put('/admin/clients/'.$client->id.'/items/'.$item->id, [
                'status' => 'SUDAH LENGKAP',
                'file' => UploadedFile::fake()->create('replacement.psd', 16, 'application/octet-stream'),
            ])
            ->assertRedirect();

        $item->refresh();
        Storage::disk('public')->assertExists($item->file_path);
        Storage::disk('public')->assertMissing($oldPath);

        $this->actingAs($admin)
            ->post('/admin/clients/'.$client->id.'/items', [
                'item_name' => 'File baru',
                'category' => 'asset',
                'status' => 'MASIH PROSES',
                'file' => UploadedFile::fake()->create('malicious.php', 16, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($admin)
            ->put('/admin/clients/'.$client->id.'/items/'.$item->id, [
                'status' => 'SUDAH LENGKAP',
                'file' => UploadedFile::fake()->create('malicious.php', 16, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('client_onboarding_items', [
            'client_id' => $client->id,
            'item_name' => 'File baru',
        ]);
    }

    private function makeClient(): Client
    {
        return Client::create([
            'onboarding_token' => (string) Str::uuid(),
            'company_name' => 'Upload Test Company',
        ]);
    }
}
