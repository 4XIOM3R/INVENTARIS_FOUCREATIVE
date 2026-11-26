<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientCredential;
use App\Models\ClientKbli;
use App\Models\ClientKnowledgeBase;
use App\Models\ClientOnboardingItem;
use App\Models\ClientTechnicalChecklist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Rules\RejectExecutableExtensions;

class AdminClientController extends Controller
{
    public function show(Client $client)
    {
        $client->load(['kblis','credentials','onboardingItems','knowledgeBases','technicalChecklists']);
        return view('admin.clients.show', compact('client'));
    }
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'company_name'=>'required|string|max:255','brand_name'=>'nullable|string|max:255',
            'established_year'=>'nullable|string|max:20','company_history'=>'nullable|string',
            'positioning_statement'=>'nullable|string','nib'=>'nullable|string|max:100',
            'nib_issued_date'=>'nullable|date','nib_updated_date'=>'nullable|date',
            'investment_status'=>'nullable|string|max:100','npwp'=>'nullable|string|max:100',
            'office_address'=>'nullable|string','workshop_address'=>'nullable|string',
            'phone'=>'nullable|string|max:50','wa_business_number'=>'nullable|string|max:50',
            'wa_active_7_days'=>'nullable|boolean','system_email'=>'nullable|email|max:255',
            'nib_email'=>'nullable|email|max:255','website'=>'nullable|string|max:255',
            'target_market'=>'nullable|array','target_market.*'=>'nullable|string|max:100',
            'target_market_custom'=>'nullable|array','target_market_custom.*'=>'nullable|string|max:100',
            'corporate_emails'=>'nullable|array',
            'social_media'=>'nullable|array',
        ]);
        $data['wa_active_7_days'] = $request->boolean('wa_active_7_days');
        $targetMarkets = array_merge($data['target_market'] ?? [], $data['target_market_custom'] ?? []);
        $targetMarkets = array_map(fn ($value) => is_string($value) ? trim($value) : '', $targetMarkets);
        $data['target_market'] = array_values(array_unique(array_filter($targetMarkets, fn ($value) => filled($value))));
        unset($data['target_market_custom']);
        if (array_key_exists('corporate_emails', $data)) {
            $data['corporate_emails'] = array_values(array_filter($data['corporate_emails'], fn ($value) => filled($value)));
        }
        $client->update($data);
        return back()->with('success','Profil klien berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        DB::transaction(function () use ($client) {
            foreach ($client->onboardingItems()->whereNotNull('file_path')->get() as $item) {
                Storage::disk('public')->delete($item->file_path);
            }
            $client->kblis()->delete();
            $client->credentials()->delete();
            $client->onboardingItems()->delete();
            $client->knowledgeBases()->delete();
            $client->technicalChecklists()->delete();
            $client->delete();
        });
        return redirect()->route('admin.dashboard')->with('success','Klien dan seluruh data terkait berhasil dihapus.');
    }
    public function updateStatus(Request $request, Client $client)
    {
        $data = $request->validate(['status'=>'required|in:draft,in_progress,ready_to_deploy,completed']);
        $client->update($data);
        return back()->with('success','Status proyek diperbarui.');
    }
    public function reviewModule(Request $request, Client $client, int $module)
    {
        abort_unless($module >= 1 && $module <= 9,404);
        $data = $request->validate([
            'status'=>'required|in:SUDAH LENGKAP,MASIH PROSES,BELUM DITERIMA,MENYUSUL',
            'detail_notes'=>'nullable|string|max:5000',
        ]);
        if ($data['status'] === 'BELUM DITERIMA' && blank($data['detail_notes'] ?? null)) {
            return back()->withErrors(['detail_notes'=>'Tambahkan catatan agar klien tahu apa yang perlu diperbaiki.']);
        }
        $client->onboardingItems()->updateOrCreate(
            ['item_name'=>'Portal Modul '.$module.' Review'],
            ['category'=>'asset','status'=>$data['status'],'detail_notes'=>$data['detail_notes'] ?? null]
        );
        return back()->with('success','Status review modul diperbarui.');
    }
    public function updateItem(Request $request, Client $client, ClientOnboardingItem $item)
    {
        abort_unless($item->client_id === $client->id, 404);
        $data = $request->validate([
            'status'=>'required|in:SUDAH LENGKAP,MASIH PROSES,BELUM DITERIMA,MENYUSUL',
            'detail_notes'=>'nullable|string',
            'item_name'=>'sometimes|required|string|max:255',
            'category'=>'sometimes|required|in:legal,access,billing,asset,addon',
            'package_choice'=>'nullable|string|max:255','billing_schedule'=>'nullable|string|max:255',
            'has_chatbot_addon'=>'nullable|boolean',
            'file'=>['nullable','file','max:10240',new RejectExecutableExtensions],
        ]);
        if ($request->has('has_chatbot_addon')) $data['has_chatbot_addon'] = $request->boolean('has_chatbot_addon');
        $oldFilePath = $item->file_path;
        $newFilePath = null;
        if ($request->hasFile('file')) {
            $newFilePath = $request->file('file')->store('clients/'.$client->id, 'public');
            if ($newFilePath === false) {
                throw new \RuntimeException('File gagal disimpan ke penyimpanan publik.');
            }
            $data['file_path'] = $newFilePath;
        }
        $item->update($data);
        if ($oldFilePath && $newFilePath && $oldFilePath !== $newFilePath) {
            Storage::disk('public')->delete($oldFilePath);
        }
        return back()->with('success','Status kelengkapan diperbarui.');
    }
    public function storeItem(Request $request, Client $client)
    {
        $data = $request->validate([
            'item_name'=>'required|string|max:255','category'=>'required|in:legal,access,billing,asset,addon',
            'status'=>'required|in:SUDAH LENGKAP,MASIH PROSES,BELUM DITERIMA,MENYUSUL',
            'detail_notes'=>'nullable|string','package_choice'=>'nullable|string|max:255',
            'billing_schedule'=>'nullable|string|max:255','has_chatbot_addon'=>'nullable|boolean',
            'file'=>['nullable','file','max:10240',new RejectExecutableExtensions],
        ]);
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('clients/'.$client->id,'public');
            if ($data['file_path'] === false) {
                throw new \RuntimeException('File gagal disimpan ke penyimpanan publik.');
            }
        }
        $data['has_chatbot_addon'] = $request->boolean('has_chatbot_addon');
        $client->onboardingItems()->create($data);
        return back()->with('success','Item onboarding berhasil ditambahkan.');
    }
    public function destroyItem(Client $client, ClientOnboardingItem $item)
    {
        abort_unless($item->client_id === $client->id,404);
        if ($item->file_path) Storage::disk('public')->delete($item->file_path);
        $item->delete();
        return back()->with('success','Item onboarding dan file terkait berhasil dihapus.');
    }
    public function destroyItemFile(Client $client, ClientOnboardingItem $item)
    {
        abort_unless($item->client_id === $client->id,404);
        if ($item->file_path) Storage::disk('public')->delete($item->file_path);
        $item->update(['file_path'=>null]);
        return back()->with('success','Lampiran dihapus; item onboarding tetap tersimpan.');
    }

    public function storeKbli(Request $request, Client $client)
    {
        $client->kblis()->create($request->validate([
            'kbli_code'=>'required|string|max:20','title'=>'required|string|max:255',
            'license_status'=>'nullable|string|max:150','location'=>'nullable|string',
        ]));
        return back()->with('success','Bidang usaha berhasil ditambahkan.');
    }
    public function updateKbli(Request $request, Client $client, ClientKbli $kbli)
    {
        abort_unless($kbli->client_id === $client->id,404);
        $kbli->update($request->validate([
            'kbli_code'=>'required|string|max:20','title'=>'required|string|max:255',
            'license_status'=>'nullable|string|max:150','location'=>'nullable|string',
        ]));
        return back()->with('success','Bidang usaha berhasil diperbarui.');
    }
    public function destroyKbli(Client $client, ClientKbli $kbli)
    {
        abort_unless($kbli->client_id === $client->id,404);
        $kbli->delete();
        return back()->with('success','Bidang usaha berhasil dihapus.');
    }
    public function storeCredential(Request $request, Client $client)
    {
        $data = $request->validate([
            'platform'=>'required|in:system_email,facebook_fanpage,instagram,domain_panel,apify,meta_ads',
            'username_or_email'=>'nullable|string|max:255','secret_value'=>'nullable|string',
            'pic_name'=>'nullable|string|max:255','pic_contact'=>'nullable|string|max:100',
            'needs_new_domain'=>'nullable|boolean','verification_status'=>'required|in:SUDAH LENGKAP,MASIH PROSES,BELUM DITERIMA',
            'notes'=>'nullable|string',
        ]);
        $data['needs_new_domain'] = $request->boolean('needs_new_domain');
        $client->credentials()->updateOrCreate(['platform'=>$data['platform']], $data);
        return back()->with('success','Kredensial disimpan.');
    }
    public function updateCredential(Request $request, Client $client, ClientCredential $credential)
    {
        abort_unless($credential->client_id === $client->id, 404);
        $data = $request->validate([
            'username_or_email'=>'nullable|string|max:255','secret_value'=>'nullable|string',
            'pic_name'=>'nullable|string|max:255','pic_contact'=>'nullable|string|max:100',
            'needs_new_domain'=>'nullable|boolean','verification_status'=>'required|in:SUDAH LENGKAP,MASIH PROSES,BELUM DITERIMA','notes'=>'nullable|string',
        ]);
        if (empty($data['secret_value'])) unset($data['secret_value']);
        $data['needs_new_domain'] = $request->boolean('needs_new_domain');
        $credential->update($data);
        return back()->with('success','Kredensial diperbarui.');
    }
    public function destroyCredential(Client $client, ClientCredential $credential)
    {
        abort_unless($credential->client_id === $client->id,404);
        $credential->delete();
        return back()->with('success','Kredensial berhasil dihapus.');
    }

    public function storeKnowledgeBase(Request $request, Client $client)
    {
        $data = $this->validatedKnowledgeBase($request);
        $client->knowledgeBases()->create($data);
        return back()->with('success','Data materi berhasil ditambahkan.');
    }
    public function updateKnowledgeBase(Request $request, Client $client, ClientKnowledgeBase $knowledgeBase)
    {
        abort_unless($knowledgeBase->client_id === $client->id,404);
        $knowledgeBase->update($this->validatedKnowledgeBase($request));
        return back()->with('success','Data materi berhasil diperbarui.');
    }
    public function destroyKnowledgeBase(Client $client, ClientKnowledgeBase $knowledgeBase)
    {
        abort_unless($knowledgeBase->client_id === $client->id,404);
        $knowledgeBase->delete();
        return back()->with('success','Data materi berhasil dihapus.');
    }
    private function validatedKnowledgeBase(Request $request): array
    {
        $data = $request->validate([
            'section_type'=>'required|in:priority_service,promo_cta,service_ecosystem,team_member,portfolio,award',
            'category_label'=>'nullable|string|max:255','title'=>'required|string|max:255',
            'year'=>'nullable|string|max:20','metadata_json'=>'nullable|json',
            'metadata'=>'nullable|array','metadata.*'=>'nullable|string|max:5000',
        ]);
        $data['metadata'] = $data['metadata'] ?? (!empty($data['metadata_json']) ? json_decode($data['metadata_json'],true,512,JSON_THROW_ON_ERROR) : null);
        unset($data['metadata_json']);
        return $data;
    }

    public function storeChecklist(Request $request, Client $client)
    {
        $client->technicalChecklists()->create($request->validate([
            'task_code'=>'required|string|max:20','task_title'=>'required|string|max:255',
            'technical_notes'=>'nullable|string',
        ]));
        return back()->with('success','Checklist teknis berhasil ditambahkan.');
    }
    public function updateChecklist(Request $request, Client $client, ClientTechnicalChecklist $checklist)
    {
        abort_unless($checklist->client_id === $client->id, 404);
        $data = $request->validate([
            'task_code'=>'sometimes|required|string|max:20',
            'task_title'=>'sometimes|required|string|max:255',
            'technical_notes'=>'nullable|string',
        ]);
        $completed = $request->boolean('is_completed');
        $checklist->update($data + [
            'is_completed'=>$completed,
            'completed_at'=>$completed ? now() : null,
        ]);
        return back()->with('success','Checklist teknis diperbarui.');
    }
    public function destroyChecklist(Client $client, ClientTechnicalChecklist $checklist)
    {
        abort_unless($checklist->client_id === $client->id,404);
        $checklist->delete();
        return back()->with('success','Checklist teknis berhasil dihapus.');
    }
}
