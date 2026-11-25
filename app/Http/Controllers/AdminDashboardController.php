<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\ClientOnboardingItem;
use App\Models\ClientTechnicalChecklist;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $clients = Client::withCount([])->latest()->get();
        $stats = collect(['draft','in_progress','ready_to_deploy','completed'])->mapWithKeys(
            fn ($status) => [$status => Client::where('status',$status)->count()]
        );
        return view('admin.dashboard', compact('clients','stats'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'=>'required|string|max:255',
            'brand_name'=>'nullable|string|max:255',
            'phone'=>'nullable|string|max:50',
        ]);
        $client = Client::create([...$data,'onboarding_token'=>(string) Str::uuid(),'status'=>'draft']);
        foreach ([
            ['legal','NIB / Nomor Izin Berusaha'],['legal','NPWP Perusahaan'],['asset','Logo Asli'],
            ['asset','NPWP Perusahaan'],['billing','Paket & Jadwal Billing'],
            ['access','Kredensial Sistem'],['addon','Chatbot AI & CRM'],
        ] as [$category,$name]) {
            ClientOnboardingItem::create(['client_id'=>$client->id,'category'=>$category,'item_name'=>$name]);
        }
        foreach ([
            'TAHAP_0'=>'Pembuatan Profil Browser Chrome Isolasi Klien',
            'TAHAP_A'=>'Website & Domain',
            'TAHAP_B'=>'Integrasi WhatsApp Business Coexistence',
            'TAHAP_C'=>'Ekstraksi & Input Cookie sessionid Instagram',
            'TAHAP_D'=>'Pengujian Deliverability Email Sistem',
            'TAHAP_E'=>'Aktivasi Paket Starter Apify',
            'TAHAP_FG'=>'Rekap Kredensial & Pembuatan 3 Template Blast',
        ] as $code=>$title) {
            ClientTechnicalChecklist::create(['client_id'=>$client->id,'task_code'=>$code,'task_title'=>$title]);
        }
        return back()->with('success','Klien baru berhasil ditambahkan.');
    }
}
