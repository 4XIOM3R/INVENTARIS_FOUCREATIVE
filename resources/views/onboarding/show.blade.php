@extends('layouts.app')
@section('title','Portal Onboarding')
@section('body')
@php
$priorityRows = $client->knowledgeBases->where('section_type','priority_service')->values();
$promo = $client->knowledgeBases->firstWhere('section_type','promo_cta');
$services = $client->knowledgeBases->where('section_type','service_ecosystem')->values();
$team = $client->knowledgeBases->where('section_type','team_member')->values();
$portfolios = $client->knowledgeBases->where('section_type','portfolio')->values();
$awards = $client->knowledgeBases->where('section_type','award')->values();
$targetMarketPresets = ['BUMN/BUMD','Kementerian/Lembaga','Perusahaan Swasta Besar','LSM Besar/Asing','Perwakilan Perusahaan Asing','UMKM'];
$customTargetMarkets = collect($client->target_market ?? [])
    ->reject(fn ($target) => in_array($target, $targetMarketPresets, true))
    ->values();
$steps = [
 ['Profil Perusahaan','Ceritakan nama dan identitas usaha'],
 ['Legalitas & Bidang Usaha','Lengkapi dokumen dan KBLI'],
 ['Kontak & Media Sosial','Isi kontak yang mudah dihubungi'],
 ['Akun & Password','Isi akun yang akan digunakan tim'],
 ['Pilihan Paket & Jadwal','Pilih paket dan jadwal koordinasi'],
 ['Dokumen & Gambar','Unggah logo dan dokumen pendukung'],
 ['Materi Kampanye','Isi layanan, promo, dan CTA'],
 ['Tim & Layanan','Lengkapi profil tim dan layanan'],
 ['Portofolio & Penghargaan','Tambahkan bukti pengalaman'],
];
@endphp
<div x-data="onboardingWizard()" x-init="init()" class="onboarding-page relative min-h-screen">
  <header class="glass-panel sticky top-0 z-30 border-b border-zinc-200 bg-white/80 backdrop-blur-md">
    <div class="mx-auto max-w-6xl px-4 py-4">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Isi data dengan mudah</p>
          <h1 class="break-words text-lg font-semibold text-zinc-950 sm:text-xl">{{ $client->brand_name ?: $client->company_name }}</h1>
        </div>
        <a href="https://wa.me/6282182851217?text={{ urlencode('Halo Admin, saya membutuhkan bantuan mengisi portal onboarding '.$client->company_name) }}" target="_blank" rel="noopener" class="glass-btn-secondary min-h-11 shrink-0 rounded-full px-4 py-2 text-sm font-bold">Bantuan WhatsApp</a>
      </div>
      <div class="mt-4">
        <div class="mb-2 flex items-center justify-between gap-3 text-xs font-medium text-zinc-600"><span x-text="'Langkah '+module+' dari 9'"></span><span class="flex items-center gap-2"><span class="tabular-nums" x-text="progress+'% lengkap'"></span><span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-zinc-700"><span class="h-1.5 w-1.5 rounded-full" :class="saveState==='Tersimpan'?'bg-emerald-500':'bg-amber-500'"></span><span x-text="saveState"></span></span></span></div>
        <div class="h-1.5 overflow-hidden rounded-full bg-zinc-200"><div class="h-1.5 rounded-full bg-zinc-900 transition-all" :style="'width:'+progress+'%'"></div></div>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-6 sm:py-8">
    @if(in_array($client->status,['ready_to_deploy','completed']))
      <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span>
        <div><p class="font-black text-emerald-900">Data sudah dikirim</p><p class="mt-1 text-sm text-emerald-800">Tim admin sedang memeriksa data Anda. Jika perlu revisi, modul terkait akan ditandai dan tim akan menghubungi Anda.</p></div>
      </div>
    @endif
    <nav class="step-scroll glass-panel mb-4 flex gap-2 overflow-x-auto rounded-2xl p-2 lg:hidden" aria-label="Langkah onboarding">
      @foreach($steps as $i=>$step)
        <button type="button" @click="go({{ $i+1 }})" :aria-current="module==={{ $i+1 }}?'step':null" :class="module==={{ $i+1 }}?'border-zinc-900 bg-zinc-100':'border-zinc-200 bg-white'" class="flex min-w-[152px] shrink-0 items-center gap-2 rounded-xl border p-3 text-left">
          <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-semibold text-zinc-700">{{ $i+1 }}</span>
          <span class="text-xs font-medium text-zinc-800">{{ $step[0] }}</span>
        </button>
      @endforeach
    </nav>
    <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
      <aside class="hidden lg:block">
        <nav class="sticky top-28 space-y-2 rounded-2xl border border-slate-200 bg-white p-3">
          @foreach($steps as $i=>$step)
          <button type="button" @click="go({{ $i+1 }})" :class="module==={{ $i+1 }}?'border-zinc-900 bg-zinc-50':'border-transparent'" class="flex w-full items-center gap-3 rounded-xl border p-3 text-left">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold" :class="completeModules.includes({{ $i+1 }})?'bg-emerald-600 text-white':(module==={{ $i+1 }}?'bg-zinc-900 text-white':'bg-zinc-100 text-zinc-500')" x-text="completeModules.includes({{ $i+1 }})?'✓':'{{ $i+1 }}'"></span>
            <span><b class="block text-sm">{{ $step[0] }}</b><small class="text-slate-500" x-text="reviewStatus[{{ $i+1 }}] || (completeModules.includes({{ $i+1 }})?'Lengkap':(savedModules.includes({{ $i+1 }})?'Tersimpan':'Belum diisi'))"></small></span>
          </button>
          @endforeach
        </nav>
      </aside>

      <div class="glass-panel min-w-0 rounded-2xl border border-zinc-200 bg-white">
        <div class="border-b px-5 py-4 sm:px-8">
          <p class="text-sm font-bold text-indigo-600" x-text="'Langkah '+module+' dari 9'"></p>
          <h2 class="mt-1 text-2xl font-semibold text-slate-900" x-text="titles[module-1]"></h2>
          <p class="mt-2 text-sm text-slate-500" x-text="help[module-1]"></p>
        </div>

        <div class="p-5 sm:p-8">
          @foreach($reviewNotes as $reviewModule=>$review)
            @if($review->status==='BELUM DITERIMA')
              <button type="button" @click="go({{ $reviewModule }})" class="mb-4 block w-full rounded-xl border border-amber-300 bg-amber-50 p-4 text-left">
                <b class="text-amber-900">Modul {{ $reviewModule }} perlu diperbaiki</b>
                <span class="mt-1 block text-sm text-amber-800">{{ $review->detail_notes }}</span>
                <span class="mt-2 block text-xs font-bold text-amber-900">Tekan di sini untuk membuka modul</span>
              </button>
            @elseif($review->status==='MASIH PROSES' && str_starts_with($review->item_name,'Portal Modul'))
              <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">Modul {{ $reviewModule }} sudah diperbarui dan menunggu pemeriksaan admin.</div>
            @endif
          @endforeach
          <section x-show="module===1">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,1]) }}" action="{{ route('onboarding.profile',$client->onboarding_token) }}" class="space-y-5">@csrf
              <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="field-label">Nama Badan Usaha</label><input name="company_name" value="{{ $client->company_name }}" required class="field" placeholder="Contoh: PT SPORA CIPTA PARAMEDIA"><p class="field-hint">Isi nama resmi sesuai dokumen perusahaan.</p></div>
                <div><label class="field-label">Nama Brand</label><input name="brand_name" value="{{ $client->brand_name }}" class="field" placeholder="Contoh: Spora Comm"><p class="field-hint">Nama yang dikenali publik atau dipakai di kampanye.</p></div>
                <div><label class="field-label">Mulai Berdiri</label><input name="established_year" value="{{ $client->established_year }}" class="field" placeholder="Contoh: 2002"><p class="field-hint">Masukkan tahun, misalnya 2002.</p></div>
              </div>
              <div><label class="field-label">Sejarah Singkat Perusahaan</label><textarea name="company_history" class="field min-h-28" placeholder="Contoh: Berdiri sejak 2002 sebagai konsultan komunikasi korporat untuk BUMN dan perusahaan swasta.">{{ $client->company_history }}</textarea><p class="field-hint">Uraikan berdiri sejak kapan dan kegiatan utama perusahaan.</p></div>
              <div><label class="field-label">Kalimat Utama / Positioning</label><textarea name="positioning_statement" class="field min-h-24" placeholder="Contoh: Kami membantu organisasi membangun reputasi melalui solusi komunikasi terintegrasi.">{{ $client->positioning_statement }}</textarea><p class="field-hint">Tuliskan satu kalimat yang menjelaskan keunggulan atau janji utama perusahaan.</p></div>
              <fieldset x-data="{customTargets:@js($customTargetMarkets),addTarget(){this.customTargets.push('');this.$nextTick(()=>this.$root.closest('form').dispatchEvent(new Event('input',{bubbles:true})))},removeTarget(index){this.customTargets.splice(index,1);this.$nextTick(()=>this.$root.closest('form').dispatchEvent(new Event('input',{bubbles:true})))}}" class="space-y-4">
                <legend class="field-label">Siapa target pelanggan utama?</legend>
                <div class="grid gap-2 sm:grid-cols-2">@foreach($targetMarketPresets as $target)<label class="flex items-center gap-3 rounded-xl border border-zinc-200 p-3"><input type="checkbox" name="target_market[]" value="{{ $target }}" @checked(in_array($target,$client->target_market ?? [])) class="h-4 w-4"><span>{{ $target }}</span></label>@endforeach</div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                  <div class="mb-3 flex flex-wrap items-center justify-between gap-2"><div><p class="text-sm font-medium text-zinc-900">Lainnya / Tambah Target Sendiri</p><p class="field-hint">Tambahkan target pelanggan di luar pilihan di atas.</p></div><button type="button" @click="addTarget()" class="glass-btn-secondary rounded-lg px-3 py-2 text-sm font-medium">+ Tambah</button></div>
                  <input type="hidden" name="target_market_custom[]" value="">
                  <div class="space-y-2"><template x-for="(target,index) in customTargets" :key="index"><div class="flex gap-2"><input x-model="customTargets[index]" name="target_market_custom[]" maxlength="100" class="field" placeholder="Contoh: Startup teknologi"><button type="button" @click="removeTarget(index)" class="glass-btn-secondary shrink-0 rounded-lg px-3 text-sm text-red-600" aria-label="Hapus target">Hapus</button></div></template></div>
                </div>
              </fieldset>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===2" x-data="{kblis:@js($client->kblis->map(fn($k)=>['kbli_code'=>$k->kbli_code,'title'=>$k->title,'license_status'=>$k->license_status,'location'=>$k->location])->values())}">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,2]) }}" action="{{ route('onboarding.legal',$client->onboarding_token) }}" class="space-y-5">@csrf
              <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-900"><b>Kenapa alamat kantor penting?</b> Alamat ini harus sama dengan dokumen NIB agar akun bisnis Meta dapat diverifikasi.</div>
              <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="field-label">Nomor NIB</label><input name="nib" value="{{ $client->nib }}" class="field" placeholder="Contoh: 0262010010398" required><p class="field-hint">Salin tepat dari dokumen NIB tanpa spasi.</p></div>
                <div><label class="field-label">Nomor NPWP</label><input name="npwp" value="{{ $client->npwp }}" class="field" placeholder="Contoh: 021604111014000" required><p class="field-hint">Masukkan 15 angka nomor NPWP perusahaan.</p></div>
                <div><label class="field-label">Tanggal Terbit NIB</label><input type="date" name="nib_issued_date" value="{{ optional($client->nib_issued_date)->format('Y-m-d') }}" class="field"><p class="field-hint">Contoh: 09 Oktober 2020.</p></div>
                <div><label class="field-label">Tanggal Pembaruan NIB</label><input type="date" name="nib_updated_date" value="{{ optional($client->nib_updated_date)->format('Y-m-d') }}" class="field"><p class="field-hint">Contoh: 17 November 2023.</p></div>
                <div class="sm:col-span-2"><label class="field-label">Status Usaha</label><input name="investment_status" value="{{ $client->investment_status }}" class="field" placeholder="Contoh: PMDN / Usaha Kecil"><p class="field-hint">Lihat status pada dokumen NIB.</p></div>
                <div class="sm:col-span-2"><label class="field-label">Alamat Kantor Sesuai NIB</label><textarea name="office_address" class="field min-h-24" required placeholder="Contoh: Gedung Epiwalk Lt. 3 Unit A-306, Jl. H.R. Rasuna Said, Jakarta Selatan 12940">{{ $client->office_address }}</textarea><p class="field-hint">Tulis lengkap: gedung, lantai, jalan, kelurahan, kecamatan, kota, dan kode pos.</p></div>
                <div class="sm:col-span-2"><label class="field-label">Alamat Workshop</label><textarea name="workshop_address" class="field min-h-20" placeholder="Contoh: Jl. Taman Margasatwa No. 22, Ragunan, Jakarta Selatan 12550">{{ $client->workshop_address }}</textarea><p class="field-hint">Kosongkan jika alamat workshop sama dengan kantor.</p></div>
              </div>
              <div><div class="mb-3 flex items-center justify-between"><label class="field-label mb-0">Daftar Bidang Usaha (KBLI)</label><button type="button" @click="kblis.push({kbli_code:'',title:'',license_status:'',location:''})" class="rounded-lg bg-indigo-50 px-3 py-2 text-sm font-bold text-indigo-700">+ Tambah baris</button></div>
                <template x-for="(row,i) in kblis" :key="i"><div class="mb-2 grid gap-2 rounded-xl border p-3 sm:grid-cols-2 lg:grid-cols-[100px_1fr_1fr_auto]"><input x-model="row.kbli_code" :name="'kblis['+i+'][kbli_code]'" placeholder="Contoh: 70203" class="field"><input x-model="row.title" :name="'kblis['+i+'][title]'" placeholder="Contoh: Aktivitas Kehumasan" class="field"><input x-model="row.license_status" :name="'kblis['+i+'][license_status]'" placeholder="Contoh: Izin Usaha Efektif" class="field"><button type="button" @click="kblis.splice(i,1)" class="text-sm font-bold text-red-600">Hapus</button><input x-model="row.location" :name="'kblis['+i+'][location]'" placeholder="Contoh: Gedung Epiwalk Lt. 3 Unit A-306, Jakarta Selatan" class="field sm:col-span-2 lg:col-span-4"></div></template>
              </div>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===3">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,3]) }}" action="{{ route('onboarding.contact',$client->onboarding_token) }}" class="space-y-5">@csrf
              <div class="grid gap-4 sm:grid-cols-2"><div><label class="field-label">Nomor Telepon Kantor</label><input name="phone" value="{{ $client->phone }}" class="field" placeholder="Contoh: 021-29912275"><p class="field-hint">Gunakan nomor kantor yang dapat dihubungi.</p></div><div><label class="field-label">Nomor WhatsApp Bisnis</label><input name="wa_business_number" value="{{ $client->wa_business_number }}" class="field" placeholder="Contoh: 0821-7416-2002" required pattern="0[0-9]{9,14}" title="Gunakan nomor dengan awalan 08"><p class="field-hint">Gunakan format 08 tanpa spasi atau tanda hubung.</p></div><label class="sm:col-span-2 flex items-center gap-3 rounded-xl border p-4"><input type="checkbox" name="wa_active_7_days" value="1" @checked($client->wa_active_7_days) class="h-5 w-5"><span>Nomor WhatsApp sudah aktif selama minimal 7 hari</span></label><div><label class="field-label">Email Utama Sistem</label><input type="email" name="system_email" value="{{ $client->system_email }}" class="field" placeholder="Contoh: spora.comm@gmail.com" required><p class="field-hint">Email yang digunakan untuk login dan menerima OTP.</p></div><div><label class="field-label">Email Terdaftar NIB</label><input type="email" name="nib_email" value="{{ $client->nib_email }}" class="field" placeholder="Contoh: giyatno.spora@gmail.com"><p class="field-hint">Email resmi yang tercantum pada data NIB.</p></div><div class="sm:col-span-2"><label class="field-label">Alamat Website</label><input name="website" value="{{ $client->website }}" class="field" placeholder="Contoh: https://www.spora.co.id"><p class="field-hint">Tulis lengkap dengan https:// jika tersedia.</p></div><div><label class="field-label">Instagram</label><input name="social_media[instagram]" value="{{ $client->social_media['instagram'] ?? '' }}" class="field" placeholder="Contoh: @sporacomm"></div><div><label class="field-label">LinkedIn</label><input name="social_media[linkedin]" value="{{ $client->social_media['linkedin'] ?? '' }}" class="field" placeholder="Contoh: Spora Comm atau alamat LinkedIn"></div><div class="sm:col-span-2"><label class="field-label">Facebook Fanpage</label><input name="social_media[facebook]" value="{{ $client->social_media['facebook'] ?? '' }}" class="field" placeholder="Contoh: Spora Comm"></div></div>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===4">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,4]) }}" action="{{ route('onboarding.credentials',$client->onboarding_token) }}" class="space-y-5">@csrf
              @php($platforms=['system_email'=>'Email Sistem','facebook_fanpage'=>'Facebook / Fanpage','instagram'=>'Instagram','domain_panel'=>'Akses Website'])
              @foreach($platforms as $platform=>$label)
              @php($credential=$client->credentials->firstWhere('platform',$platform))
              <div class="glass-panel rounded-xl border border-slate-200 p-4"><h3 class="mb-3 font-black">{{ $label }}</h3><div class="grid gap-3 sm:grid-cols-2"><div><label class="field-label">Nama pengguna / email</label><input name="credentials[{{ $platform }}][username_or_email]" value="{{ $credential?->username_or_email }}" class="field" placeholder="{{ $platform==='system_email'?'Contoh: spora.comm@gmail.com':($platform==='instagram'?'Contoh: spora.comm':($platform==='domain_panel'?'Contoh: admin@spora.co.id':'Contoh: nama akun Facebook')) }}"><p class="field-hint">Masukkan username atau alamat email login.</p></div><div x-data="{show:false}"><label class="field-label">Password / kode akses</label><div class="flex gap-2"><input :type="show?'text':'password'" name="credentials[{{ $platform }}][secret_value]" class="field min-w-0" placeholder="Contoh format: Tulis password akun Anda"><button type="button" @click="show=!show" class="glass-btn-secondary min-h-11 shrink-0 rounded-lg px-3 text-sm" x-text="show?'Sembunyi':'Lihat'"></button></div><p class="field-hint">Kosongkan jika belum ada atau belum siap membagikan.</p></div><div><label class="field-label">PIC yang memegang OTP/HP</label><input name="credentials[{{ $platform }}][pic_name]" value="{{ $credential?->pic_name }}" class="field" placeholder="Contoh: Muharomah"><p class="field-hint">Nama orang yang dapat menerima kode OTP.</p></div><div><label class="field-label">Nomor WhatsApp PIC</label><input name="credentials[{{ $platform }}][pic_contact]" value="{{ $credential?->pic_contact }}" class="field" placeholder="Contoh: 0821-7416-2002"><p class="field-hint">Nomor aktif untuk koordinasi dan OTP.</p></div></div>@if($platform==='domain_panel')<label class="mt-4 flex items-center gap-3 rounded-lg bg-amber-50 p-3 text-sm"><input type="checkbox" name="credentials[domain_panel][needs_new_domain]" value="1" @checked($credential?->needs_new_domain)>Jika akses lama tidak ditemukan, setuju dibuatkan domain baru untuk Landing Page.</label>@endif</div>
              @endforeach
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===5">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,5]) }}" action="{{ route('onboarding.billing',$client->onboarding_token) }}" class="space-y-5">@csrf
              @php($billing=$client->onboardingItems->firstWhere('item_name','Paket & Jadwal Billing'))
              <fieldset><legend class="field-label">Pilih paket</legend><div class="grid gap-3 sm:grid-cols-2">@foreach(['1 Bulan Rp210.000','6 Bulan Rp1.260.000','12 Bulan Rp2.520.000','Custom'] as $package)<label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4"><input type="radio" name="package_choice" value="{{ $package }}" @checked($billing?->package_choice===($package))> {{ $package }}</label>@endforeach</div></fieldset>
              <div class="grid gap-4 sm:grid-cols-2"><div><label class="field-label">Rencana tanggal transfer</label><input name="detail_notes" value="{{ $billing?->detail_notes }}" class="field" placeholder="Contoh: Senin, 30 November 2026"><p class="field-hint">Isi perkiraan tanggal pembayaran paket.</p></div><div><label class="field-label">Jadwal sesi Zoom/GMeet</label><input name="billing_schedule" value="{{ $billing?->billing_schedule }}" class="field" placeholder="Contoh: Senin-Jumat, 10.00-17.30 WIB"><p class="field-hint">Tuliskan hari dan jam yang dapat dihubungi.</p></div><div class="sm:col-span-2"><label class="field-label">Nama PIC Pembayaran</label><input name="billing_pic" class="field" placeholder="Contoh: Muharomah"><p class="field-hint">Nama orang yang memegang kartu kredit atau melakukan pembayaran.</p></div></div>
              <label class="flex items-center gap-3 rounded-xl border p-4"><input type="checkbox" name="has_chatbot_addon" value="1" @checked($billing?->has_chatbot_addon)> Saya menggunakan Add-On Chatbot AI & CRM</label>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===6">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,6]) }}" action="{{ route('onboarding.assets',$client->onboarding_token) }}" enctype="multipart/form-data" class="space-y-5">@csrf
              @foreach(['logo'=>'Logo Asli','npwp'=>'NPWP Perusahaan','faq'=>'Excel Pengetahuan Produk / FAQ','chat_example'=>'Contoh Percakapan Chat'] as $field=>$label)
              <label class="glass-panel upload-card block cursor-pointer rounded-2xl border-dashed border-zinc-300 bg-zinc-50/50 p-4 sm:p-5"><span class="field-label">{{ $label }}</span><input type="file" name="{{ $field }}" onchange="validateUpload(this)" class="glass-input mt-2 block min-h-12 w-full text-sm"><span class="upload-status mt-2 block text-xs text-slate-500">Anda bisa memilih format gambar atau file apa pun dari HP atau komputer. Maksimal 10 MB.</span></label>
              @endforeach
              @if($client->onboardingItems->whereNotNull('file_path')->count())<div class="rounded-xl bg-emerald-50 p-4 text-sm">@foreach($client->onboardingItems->whereNotNull('file_path') as $item)<a target="_blank" href="{{ asset('storage/'.$item->file_path) }}" class="mr-4 block py-1 font-bold text-emerald-700">Lihat file {{ $item->item_name }}</a>@endforeach</div>@endif
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===7" x-data="{priorities:@js($priorityRows->map(fn($r)=>['title'=>$r->title,'scope'=>data_get($r->metadata,'scope'),'output'=>data_get($r->metadata,'output')])->all()), promo:@js($promo?->metadata ?? ['promo'=>'','cta'=>'','landing_page'=>''])}">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,7]) }}" action="{{ route('onboarding.campaign',$client->onboarding_token) }}" class="space-y-5">@csrf
              <template x-for="(row,i) in (priorities.length ? priorities : [{title:'',scope:'',output:''},{title:'',scope:'',output:''},{title:'',scope:'',output:''}])" :key="i"><div class="rounded-xl border p-4"><h3 class="mb-3 font-black" x-text="'Prioritas layanan '+(i+1)"></h3><div class="grid gap-3 sm:grid-cols-3"><div><input x-model="row.title" :name="'priorities['+i+'][title]'" placeholder="Contoh: Reputation Intelligence" class="field"><p class="field-hint">Nama layanan utama.</p></div><div><textarea x-model="row.scope" :name="'priorities['+i+'][scope]'" placeholder="Contoh: Monitoring berita, analisis sentimen, dan laporan tren isu." class="field min-h-24"></textarea><p class="field-hint">Jelaskan pekerjaan yang dilakukan.</p></div><div><textarea x-model="row.output" :name="'priorities['+i+'][output]'" placeholder="Contoh: Dashboard, laporan mingguan, dan rekomendasi." class="field min-h-24"></textarea><p class="field-hint">Jelaskan hasil atau dokumen yang diterima.</p></div></div></div></template>
              <div class="grid gap-4"><div><label class="field-label">Promo / tawaran pembuka</label><textarea x-model="promo.promo" name="promo[promo]" class="field min-h-20" placeholder="Contoh: Gratis konsultasi komunikasi dan reputasi 30 menit."></textarea><p class="field-hint">Tulis penawaran khusus yang ingin ditampilkan.</p></div><div><label class="field-label">Ajakan bertindak (CTA)</label><input x-model="promo.cta" name="promo[cta]" class="field" placeholder="Contoh: Balas chat WhatsApp dan jadwalkan meeting"><p class="field-hint">Instruksi singkat agar calon klien segera menghubungi.</p></div><div><label class="field-label">Catatan Landing Page khusus</label><textarea x-model="promo.landing_page" name="promo[landing_page]" class="field min-h-20" placeholder="Contoh: Buat halaman khusus untuk tawaran ini dan jangan langsung mengarah ke website utama."></textarea><p class="field-hint">Tulis tata letak atau kebutuhan khusus yang diinginkan.</p></div></div>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===8" x-data="{services:@js($services->map(fn($r)=>['category'=>$r->category_label,'title'=>$r->title,'scope'=>data_get($r->metadata,'scope')])->all()),team:@js($team->map(fn($r)=>['title'=>$r->title,'role'=>data_get($r->metadata,'role'),'qualification'=>data_get($r->metadata,'qualification')])->all())}">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,8]) }}" action="{{ route('onboarding.ecosystem',$client->onboarding_token) }}" class="space-y-6">@csrf
              <div><div class="mb-3 flex justify-between"><h3 class="font-black">Layanan yang ditawarkan</h3><button type="button" @click="services.push({category:'',title:'',scope:''})" class="text-sm font-bold text-indigo-600">+ Tambah layanan</button></div><template x-for="(row,i) in services" :key="i"><div class="mb-2 grid gap-2 rounded-xl border p-3 sm:grid-cols-3"><input x-model="row.category" :name="'services['+i+'][category]'" placeholder="Contoh: ADVISE — Strategy First" class="field"><input x-model="row.title" :name="'services['+i+'][title]'" placeholder="Contoh: Strategi Komunikasi" class="field"><input x-model="row.scope" :name="'services['+i+'][scope]'" placeholder="Contoh: Audit isu, pemetaan stakeholder, dan roadmap" class="field"></div></template></div>
              <div><div class="mb-3 flex justify-between"><h3 class="font-black">Tim profesional</h3><button type="button" @click="team.push({title:'',role:'',certification:'',qualification:''})" class="text-sm font-bold text-indigo-600">+ Tambah anggota</button></div><template x-for="(row,i) in team" :key="i"><div class="mb-2 grid gap-2 rounded-xl border p-3 sm:grid-cols-2"><input x-model="row.title" :name="'team['+i+'][title]'" placeholder="Contoh: Nana Oktavia Musliana, S.S." class="field"><input x-model="row.role" :name="'team['+i+'][role]'" placeholder="Contoh: Project Leader / Lead Copywriter" class="field"><input x-model="row.certification" :name="'team['+i+'][certification]'" placeholder="Contoh: GRI Certified" class="field"><input x-model="row.qualification" :name="'team['+i+'][qualification]'" placeholder="Contoh: 20+ tahun pengalaman menulis laporan korporasi" class="field"></div></template></div>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
          </section>

          <section x-show="module===9" x-data="{portfolio:@js($portfolios->map(fn($r)=>['category'=>$r->category_label,'title'=>$r->title,'year'=>$r->year,'project'=>data_get($r->metadata,'project')])->all()),awards:@js($awards->map(fn($r)=>['year'=>$r->year,'title'=>$r->title,'award'=>data_get($r->metadata,'award')])->all())}">
            <form method="POST" data-autosave-url="{{ route('onboarding.autosave',[$client->onboarding_token,9]) }}" action="{{ route('onboarding.portfolio',$client->onboarding_token) }}" class="space-y-6">@csrf
              <div><div class="mb-3 flex justify-between"><h3 class="font-black">Portofolio / proyek</h3><button type="button" @click="portfolio.push({category:'',title:'',year:'',project:''})" class="text-sm font-bold text-indigo-600">+ Tambah portofolio</button></div><template x-for="(row,i) in portfolio" :key="i"><div class="mb-2 grid gap-2 rounded-xl border p-3 sm:grid-cols-4"><input x-model="row.category" :name="'portfolio['+i+'][category]'" placeholder="Contoh: Energi & Utilitas" class="field"><input x-model="row.title" :name="'portfolio['+i+'][title]'" placeholder="Contoh: PT PLN Icon Plus" class="field"><input x-model="row.year" :name="'portfolio['+i+'][year]'" placeholder="Contoh: 2024" class="field"><input x-model="row.project" :name="'portfolio['+i+'][project]'" placeholder="Contoh: Annual Report 2023" class="field"></div></template></div>
              <div><div class="mb-3 flex justify-between"><h3 class="font-black">Penghargaan</h3><button type="button" @click="awards.push({year:'',title:'',award:''})" class="text-sm font-bold text-indigo-600">+ Tambah penghargaan</button></div><template x-for="(row,i) in awards" :key="i"><div class="mb-2 grid gap-2 rounded-xl border p-3 sm:grid-cols-3"><input x-model="row.year" :name="'awards['+i+'][year]'" placeholder="Contoh: 2026" class="field"><input x-model="row.title" :name="'awards['+i+'][title]'" placeholder="Contoh: Jasa Raharja | Majalah SECURE" class="field"><input x-model="row.award" :name="'awards['+i+'][award]'" placeholder="Contoh: Gold Winner PRIA 2026" class="field"></div></template></div>
              <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Simpan langkah ini</button>
            </form>
            <div class="mt-5 rounded-xl bg-indigo-50 p-5">
              <h3 class="font-black text-indigo-900">Siap mengirim data?</h3>
              <p class="mt-1 text-sm text-indigo-800">Periksa ringkasan singkat sebelum mengirim kepada tim admin.</p>
              <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" @click="preview=true" class="rounded-xl border border-indigo-300 bg-white px-5 py-3 font-bold text-indigo-700">Lihat Ringkasan</button>
                <form id="final-onboarding-submit" method="POST" action="{{ route('onboarding.submit',$client->onboarding_token) }}">@csrf<button onclick="submitOnboarding(event)" class="rounded-xl bg-indigo-600 px-5 py-3 font-black text-white">Kirim Semua Data</button></form>
              </div>
            </div>
          </section>
        </div>

        <div class="hidden items-center justify-between border-t px-5 py-4 sm:flex sm:px-8">
          <button type="button" x-show="module>1" @click="go(module-1)" class="rounded-xl border px-4 py-3 font-bold text-slate-600">← Sebelumnya</button>
          <button type="button" x-show="module<9" @click="go(module+1)" class="ml-auto rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 font-bold text-indigo-700">Lanjut →</button>
        </div>
      </div>
    </div>
  </main>
  <div x-show="preview" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/60 p-0 sm:items-center sm:p-4">
    <div @click.outside="preview=false" class="glass-panel max-h-[85vh] w-full max-w-2xl overflow-y-auto rounded-t-3xl bg-white p-5 sm:rounded-2xl sm:p-7">
      <div class="flex items-start justify-between">
        <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Ringkasan</p><h2 class="mt-1 text-2xl font-black">Periksa data utama</h2></div>
        <button type="button" @click="preview=false" class="rounded-full border px-3 py-2" aria-label="Tutup">✕</button>
      </div>
      <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border p-4"><p class="text-xs text-slate-500">Perusahaan</p><p class="mt-1 font-bold">{{ $client->company_name }}</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-slate-500">Nomor WhatsApp</p><p class="mt-1 font-bold">{{ $client->wa_business_number ?: 'Belum diisi' }}</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-slate-500">NIB</p><p class="mt-1 font-bold">{{ $client->nib ?: 'Belum diisi' }}</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-slate-500">NPWP</p><p class="mt-1 font-bold">{{ $client->npwp ?: 'Belum diisi' }}</p></div>
        <div class="rounded-xl border p-4 sm:col-span-2"><p class="text-xs text-slate-500">Alamat kantor sesuai NIB</p><p class="mt-1 font-bold">{{ $client->office_address ?: 'Belum diisi' }}</p></div>
        <div class="rounded-xl border p-4 sm:col-span-2"><p class="text-xs text-slate-500">Email sistem</p><p class="mt-1 font-bold">{{ $client->system_email ?: 'Belum diisi' }}</p></div>
      </div>
      <button type="button" @click="preview=false" class="mt-5 w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Saya sudah memeriksa</button>
    </div>
  </div>
  <nav class="glass-panel fixed inset-x-3 bottom-3 z-40 flex items-center justify-between rounded-2xl border bg-white p-2 sm:hidden">
    <button type="button" x-show="module>1" @click="go(module-1)" class="rounded-xl px-4 py-3 font-bold text-slate-600">← Kembali</button>
    <span x-show="module===1" class="px-2 text-xs font-bold text-slate-400">Langkah 1 dari 9</span>
    <button type="button" x-show="module<9" @click="go(module+1)" class="ml-auto rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white">Lanjut →</button>
  </nav>
</div>

<template id="wizard-buttons-template"></template>
<style>[x-cloak]{display:none!important}</style>
<script>
function validateUpload(input){
  const status=input.parentElement.querySelector('.upload-status');
  if(!input.files.length)return;
  const file=input.files[0];
  if(file.size>10*1024*1024){
    input.value='';
    status.textContent='Ukuran file terlalu besar. Pilih file maksimal 10 MB.';
    status.className='upload-status mt-2 text-xs font-bold text-red-600';
    return;
  }
  status.textContent='File siap diunggah: '+file.name+' ('+Math.ceil(file.size/1024)+' KB)';
  status.className='upload-status mt-2 text-xs font-bold text-emerald-700';
}
function onboardingWizard(){
  return {
    module:1,
    progress:@js($progressPercent),
    completeModules:@js(collect($moduleProgress)->filter()->keys()->values()),
    savedModules:@js(collect($moduleSaved)->filter()->keys()->values()),
    reviewStatus:@js($reviewNotes->mapWithKeys(fn($review,$module)=>[$module=>$review->status==='BELUM DITERIMA'?'Perlu revisi':($review->status==='MASIH PROSES'?'Menunggu pemeriksaan':null)])),
    saveState:'Tersimpan',
    preview:false,
    titles:@js(array_column($steps,0)),
    help:@js(array_column($steps,1)),
    init(){
      const last=parseInt(localStorage.getItem('spora_onboarding_step')||'1');
      this.module=Math.min(Math.max(last,1),9);
      window.portalWizard=this;
    },
    async go(step){
      const active=document.querySelector('[data-autosave-url$="/autosave/'+this.module+'"]');
      if(active && active.dataset.dirty==='1'){
        const ok=await window.autosaveForm(active);
        if(!ok)return;
      }
      this.module=step;
      localStorage.setItem('spora_onboarding_step',step);
      window.scrollTo({top:0,behavior:'smooth'});
    },
    onSaved(data){
      this.progress=data.progress;
      this.completeModules=Object.entries(data.moduleProgress).filter(([,done])=>done).map(([key])=>Number(key));
      this.savedModules=Object.entries(data.moduleSaved).filter(([,saved])=>saved).map(([key])=>Number(key));
      this.reviewStatus[data.module]=this.reviewStatus[data.module]==='Perlu revisi'?'Menunggu pemeriksaan':null;
      this.saveState='Tersimpan';
    }
  }
}
async function autosaveForm(form){
  if(form.dataset.dirty!=='1')return true;
  if(form.autosavePromise){
    const saved=await form.autosavePromise;
    if(!saved)return false;
    return form.dataset.dirty==='1'?autosaveForm(form):true;
  }
  const state=window.portalWizard;
  const version=form.dataset.autosaveVersion||'0';
  if(state)state.saveState='Menyimpan...';
  form.autosavePromise=(async()=>{
    try{
      const response=await fetch(form.dataset.autosaveUrl,{
        method:'POST',
        body:new FormData(form),
        credentials:'same-origin',
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
      });
      const result=await response.json();
      if(!response.ok)throw new Error(Object.values(result.errors||{}).flat()[0]||'Periksa kembali isian formulir.');
      if(form.dataset.autosaveVersion===version)form.dataset.dirty='0';
      if(state)state.onSaved(result);
      return true;
    }catch(error){
      if(state)state.saveState='Belum tersimpan: '+error.message;
      return false;
    }
  })();
  const saved=await form.autosavePromise;
  form.autosavePromise=null;
  if(saved && form.dataset.dirty==='1')return autosaveForm(form);
  return saved;
}
async function submitOnboarding(event){
  event.preventDefault();
  const state=window.portalWizard;
  const form=document.querySelector('[data-autosave-url$="/autosave/9"]');
  if(form && form.dataset.dirty==='1' && !(await window.autosaveForm(form)))return;
  if(state)state.saveState='Mengirim data...';
  document.getElementById('final-onboarding-submit').submit();
}
function installAutosave(){
  if(window.portalAutosaveInstalled)return;
  window.portalAutosaveInstalled=true;
  document.querySelectorAll('form[data-autosave-url]').forEach(form=>{
    let timer;
    const schedule=()=>{
      form.dataset.dirty='1';
      form.dataset.autosaveVersion=String(Number(form.dataset.autosaveVersion||0)+1);
      clearTimeout(timer);
      timer=setTimeout(()=>window.autosaveForm(form),900);
    };
    form.addEventListener('input',schedule);
    form.addEventListener('change',schedule);
    form.addEventListener('focusout',()=>{if(form.dataset.dirty==='1'){clearTimeout(timer);timer=setTimeout(()=>window.autosaveForm(form),150)}});
    form.addEventListener('submit',()=>clearTimeout(timer));
  });
}
window.autosaveForm=autosaveForm;
window.addEventListener('load',installAutosave,{once:true});
</script>
@endsection
