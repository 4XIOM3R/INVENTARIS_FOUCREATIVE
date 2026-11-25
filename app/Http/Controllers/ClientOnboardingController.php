<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use App\Models\Client;
use App\Models\ClientCredential;
use App\Models\ClientKbli;
use App\Models\ClientKnowledgeBase;
use App\Models\ClientOnboardingItem;

class ClientOnboardingController extends Controller
{
    private function client(Request $request): Client { return $request->attributes->get('onboarding_client'); }
    public function show(Request $request)
    {
        $client = $this->client($request);
        $reviews = $client->onboardingItems()
            ->whereIn('item_name', array_map(fn ($module) => $this->reviewItemName($module), range(1, 9)))
            ->get()->keyBy(fn ($item) => (int) str_replace('Portal Modul ', '', $item->item_name));
        return view('onboarding.show', [
            'client'=>$client,
            'moduleProgress'=>$this->moduleProgress($client),
            'moduleSaved'=>$this->moduleSaved($client),
            'progressPercent'=>(int) round(count(array_filter($this->moduleProgress($client)))/9*100),
            'reviewNotes'=>$reviews,
        ]);
    }

    public function autosave(Request $request, string $token, string $module)
    {
        $module = (int) $module;
        $methods = [
            1=>'profile', 2=>'legal', 3=>'contact', 4=>'credentials', 5=>'billing',
            6=>'assets', 7=>'campaign', 8=>'ecosystem', 9=>'portfolio',
        ];
        abort_unless(isset($methods[$module]),404);
        $request->headers->set('Accept','application/json');
        return $this->{$methods[$module]}($request);
    }
    public function profile(Request $request)
    {
        $client=$this->client($request);
        $data=$request->validate(['company_name'=>'required|string|max:255','brand_name'=>'nullable|string|max:255','established_year'=>'nullable|string|max:20','company_history'=>'nullable|string','positioning_statement'=>'nullable|string','target_market'=>'nullable|array']);
        $data['target_market']=$data['target_market'] ?? [];
        $client->update($data);
        $this->mark($client,'Profil Perusahaan','SUDAH LENGKAP');
        return $this->saved($request,'Modul identitas tersimpan.',$client,1);
    }
    public function legal(Request $request)
    {
        $client=$this->client($request);
        $client->update($request->validate(['nib'=>'nullable|string|max:100','nib_issued_date'=>'nullable|date','nib_updated_date'=>'nullable|date','investment_status'=>'nullable|string|max:100','npwp'=>'nullable|string|max:100','office_address'=>'nullable|string','workshop_address'=>'nullable|string']));
        $client->kblis()->delete();
        foreach ($request->input('kblis',[]) as $row) {
            if (!empty($row['kbli_code'])) $client->kblis()->create($row);
        }
        $this->mark($client,'NIB / Nomor Izin Berusaha','SUDAH LENGKAP');
        return $this->saved($request,'Modul legalitas tersimpan.',$client,2);
    }
    public function contact(Request $request)
    {
        $client=$this->client($request);
        $data=$request->validate(['phone'=>'nullable|string|max:50','wa_business_number'=>'nullable|string|max:50','wa_active_7_days'=>'nullable|boolean','system_email'=>'nullable|email','nib_email'=>'nullable|email','corporate_emails'=>'nullable|array','website'=>'nullable|string|max:255','social_media'=>'nullable|array']);
        $data['wa_active_7_days']=$request->boolean('wa_active_7_days');
        $client->update($data);
        $this->mark($client,'Kontak & Aset Digital','SUDAH LENGKAP');
        return $this->saved($request,'Modul kontak tersimpan.',$client,3);
    }
    public function credentials(Request $request)
    {
        $client=$this->client($request);
        foreach ($request->input('credentials',[]) as $platform=>$row) {
            if (!in_array($platform,['system_email','facebook_fanpage','instagram','domain_panel','apify','meta_ads'])) continue;
            $data=$row;
            $data['needs_new_domain']=!empty($data['needs_new_domain']);
            if (empty($data['secret_value'])) unset($data['secret_value']);
            $client->credentials()->updateOrCreate(['platform'=>$platform],$data);
        }
        $this->mark($client,'Kredensial Sistem','SUDAH LENGKAP');
        return $this->saved($request,'Brankas kredensial tersimpan.',$client,4);
    }
    public function billing(Request $request)
    {
        $client=$this->client($request);
        $data=$request->validate(['package_choice'=>'nullable|string','detail_notes'=>'nullable|string','billing_schedule'=>'nullable|string','billing_pic'=>'nullable|string','has_chatbot_addon'=>'nullable|boolean']);
        $item=$client->onboardingItems()->firstOrCreate(['item_name'=>'Paket & Jadwal Billing'],['category'=>'billing']);
        $item->update(['package_choice'=>$data['package_choice']??null,'detail_notes'=>$data['detail_notes']??null,'billing_schedule'=>$data['billing_schedule']??null,'has_chatbot_addon'=>$request->boolean('has_chatbot_addon'),'status'=>'MASIH PROSES']);
        $item=$client->onboardingItems()->firstOrCreate(['item_name'=>'Chatbot AI & CRM'],['category'=>'addon']);
        $item->update(['has_chatbot_addon'=>$request->boolean('has_chatbot_addon'),'detail_notes'=>$data['billing_pic']??null]);
        return $this->saved($request,'Paket dan billing tersimpan.',$client,5);
    }
    public function assets(Request $request)
    {
        $client=$this->client($request);
        $request->validate([
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,ai,zip,pdf|max:10240',
            'npwp' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:10240',
            'faq' => 'nullable|file|mimes:xlsx,xls,csv|max:10240',
            'chat_example' => 'nullable|file|max:10240',
        ]);
        $allowed=['logo'=>'logo_original','npwp'=>'npwp','faq'=>'faq_knowledge','chat_example'=>'chat_example'];
        foreach ($allowed as $field=>$name) {
            if (!$request->hasFile($field)) continue;
            $file=$request->file($field);
            $path=$file->storeAs('clients/'.$client->id, $name.'_'.time().'.'.$file->getClientOriginalExtension(),'public');
            $item=$client->onboardingItems()->firstOrCreate(['item_name'=>match($name){'logo_original'=>'Logo Asli','npwp'=>'NPWP Perusahaan','faq'=>'Excel Product Knowledge / FAQ','chat_example'=>'Contoh Percakapan Chat'}],['category'=>$name==='logo_original'||$name==='npwp'?'asset':'addon']);
            $item->update(['file_path'=>$path,'status'=>'SUDAH LENGKAP']);
        }
        return $this->saved($request,'Aset dan dokumen tersimpan.',$client,6);
    }
    public function campaign(Request $request)
    {
        $client=$this->client($request);
        foreach ($request->input('priorities',[]) as $i=>$row) {
            if (empty($row['title'])) continue;
            $client->knowledgeBases()->updateOrCreate(['section_type'=>'priority_service','title'=>$row['title']],['category_label'=>'Prioritas '.$i,'metadata'=>$row]);
        }
        if ($request->filled('promo')) $client->knowledgeBases()->updateOrCreate(['section_type'=>'promo_cta','title'=>'Penawaran & CTA'],['metadata'=>$request->input('promo')]);
        return $this->saved($request,'Materi kampanye tersimpan.',$client,7);
    }
    public function ecosystem(Request $request)
    {
        $client=$this->client($request);
        $client->knowledgeBases()->whereIn('section_type',['service_ecosystem','team_member'])->delete();
        foreach ($request->input('services',[]) as $row) if(!empty($row['title'])) $client->knowledgeBases()->create(['section_type'=>'service_ecosystem','title'=>$row['title'],'metadata'=>$row]);
        foreach ($request->input('team',[]) as $row) if(!empty($row['title'])) $client->knowledgeBases()->create(['section_type'=>'team_member','title'=>$row['title'],'metadata'=>$row]);
        return $this->saved($request,'Ekosistem layanan dan tim tersimpan.',$client,8);
    }
    public function portfolio(Request $request)
    {
        $client=$this->client($request);
        $client->knowledgeBases()->whereIn('section_type',['portfolio','award'])->delete();
        foreach ($request->input('portfolio',[]) as $row) if(!empty($row['title'])) $client->knowledgeBases()->create(['section_type'=>'portfolio','title'=>$row['title'],'year'=>$row['year']??null,'metadata'=>$row]);
        foreach ($request->input('awards',[]) as $row) if(!empty($row['title'])) $client->knowledgeBases()->create(['section_type'=>'award','title'=>$row['title'],'year'=>$row['year']??null,'metadata'=>$row]);
        return $this->saved($request,'Portofolio dan penghargaan tersimpan.',$client,9);
    }
    private function mark(Client $client,string $name,string $status): void
    {
        $item=$client->onboardingItems()->firstOrCreate(['item_name'=>$name],['category'=>'asset']);
        $item->update(['status'=>$status]);
    }

    public function submit(Request $request)
    {
        $client = $this->client($request);
        $missing = [];
        if (!filled($client->brand_name)) $missing[] = 'Nama brand';
        if (!filled($client->nib)) $missing[] = 'NIB';
        if (!filled($client->npwp)) $missing[] = 'NPWP';
        if (!filled($client->office_address)) $missing[] = 'Alamat kantor sesuai NIB';
        if (!filled($client->wa_business_number)) $missing[] = 'Nomor WhatsApp Business';
        if (!filled($client->system_email)) $missing[] = 'Email sistem';
        if ($missing) {
            return back()->withErrors(['submission'=>'Lengkapi dulu: '.implode(', ', $missing).'.']);
        }
        $client->update(['status' => 'ready_to_deploy']);
        return redirect()->route('onboarding.show', $client->onboarding_token)
            ->with('success', 'Terima kasih! Semua data berhasil dikirim dan akan diverifikasi admin.');
    }

    private function saved(Request $request, string $message, Client $client, int $module)
    {
        $review = $client->onboardingItems()->where('item_name',$this->reviewItemName($module))->first();
        if ($review && $review->status === 'BELUM DITERIMA') {
            $review->update([
                'status'=>'MASIH PROSES',
                'detail_notes'=>'Perubahan disimpan klien pada '.now()->format('d M Y H:i').'. Menunggu verifikasi admin.',
            ]);
        }
        $progress = $this->moduleProgress($client);
        $saved = $this->moduleSaved($client);
        if ($request->expectsJson()) {
            return response()->json([
                'message'=>$message,
                'module'=>$module,
                'moduleProgress'=>$progress,
                'moduleSaved'=>$saved,
                'progress'=>(int) round(count(array_filter($progress))/9*100),
            ]);
        }
        return back()->with('success',$message);
    }

    private function reviewItemName(int $module): string
    {
        return 'Portal Modul '.$module.' Review';
    }

    private function moduleProgress(Client $client): array
    {
        $items = $client->onboardingItems()->get();
        $files = $items->whereNotNull('file_path')->pluck('item_name');
        $knowledge = $client->knowledgeBases()->get();
        $credentials = $client->credentials()->whereIn('platform',['system_email','facebook_fanpage','instagram','domain_panel'])->count();
        $billing = $items->firstWhere('item_name','Paket & Jadwal Billing');

        return [
            1=>filled($client->brand_name) && filled($client->established_year) && filled($client->company_history),
            2=>filled($client->nib) && filled($client->npwp) && filled($client->office_address) && $client->kblis()->exists(),
            3=>filled($client->phone) && filled($client->wa_business_number) && filled($client->system_email),
            4=>$credentials >= 4,
            5=>filled($billing?->package_choice) && filled($billing?->billing_schedule),
            6=>$files->contains('Logo Asli') && $files->contains('NPWP Perusahaan'),
            7=>$knowledge->where('section_type','priority_service')->count() >= 3 && $knowledge->contains('section_type','promo_cta'),
            8=>$knowledge->contains('section_type','service_ecosystem') && $knowledge->contains('section_type','team_member'),
            9=>$knowledge->contains('section_type','portfolio') && $knowledge->contains('section_type','award'),
        ];
    }

    private function moduleSaved(Client $client): array
    {
        $items = $client->onboardingItems()->get();
        $knowledge = $client->knowledgeBases()->get();
        return [
            1=>filled($client->brand_name) || filled($client->company_history),
            2=>filled($client->nib) || filled($client->npwp) || $client->kblis()->exists(),
            3=>filled($client->wa_business_number) || filled($client->system_email),
            4=>$client->credentials()->exists(),
            5=>$items->contains('item_name','Paket & Jadwal Billing'),
            6=>$items->contains(fn ($item) => filled($item->file_path)),
            7=>$knowledge->whereIn('section_type',['priority_service','promo_cta'])->isNotEmpty(),
            8=>$knowledge->whereIn('section_type',['service_ecosystem','team_member'])->isNotEmpty(),
            9=>$knowledge->whereIn('section_type',['portfolio','award'])->isNotEmpty(),
        ];
    }
}
