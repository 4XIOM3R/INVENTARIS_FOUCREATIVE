<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientCredential;
use App\Models\ClientKbli;
use App\Models\ClientKnowledgeBase;
use App\Models\ClientOnboardingItem;
use App\Models\ClientTechnicalChecklist;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@agency.com'],
            ['name' => 'FOUcreative', 'password' => Hash::make('FOUcreative321')]
        );

        $client = Client::firstOrCreate(
            ['company_name' => 'PT SPORA CIPTA PARAMEDIA'],
            ['onboarding_token' => (string) Str::uuid()]
        );

        $client->update([
            'brand_name' => 'Spora Comm',
            'established_year' => '2002',
            'company_history' => 'Konsultan komunikasi korporat resmi sejak tahun 2002, dengan pengalaman lebih dari 22 tahun.',
            'positioning_statement' => 'BEYOND PUBLICITY — Bukan Sekadar Publikasi! Kami membantu organisasi membangun narasi, memperkuat kepercayaan, dan menjaga reputasi melalui solusi komunikasi terintegrasi.',
            'target_market' => ['BUMN/BUMD','Kementerian/Lembaga','Perusahaan Swasta Besar','LSM Besar/Asing','Perwakilan Perusahaan Asing'],
            'nib' => '0262010010398',
            'nib_issued_date' => '2020-10-09',
            'nib_updated_date' => '2023-11-17',
            'investment_status' => 'PMDN / Usaha Kecil',
            'npwp' => '021604111014000',
            'office_address' => 'Gedung Epiwalk Lt. 3 Unit A-306, Kawasan Rasuna Epicentrum, Jl. H.R. Rasuna Said, Kel. Karet Kuningan, Kec. Setiabudi, Jakarta Selatan 12940',
            'workshop_address' => 'Jl. Taman Margasatwa No. 22 (Jl. Damai No. 22), Ragunan, Pasar Minggu, Jakarta Selatan 12550',
            'phone' => '021-29912275',
            'wa_business_number' => '0821-7416-2002',
            'wa_active_7_days' => true,
            'system_email' => 'spora.comm@gmail.com',
            'nib_email' => 'giyatno.spora@gmail.com',
            'corporate_emails' => ['halo@spora.co.id','info@spora.co.id','pora.comm@spora.co.id'],
            'website' => 'https://www.spora.co.id',
            'social_media' => ['instagram'=>'@sporacomm','linkedin'=>'@sporacomm','facebook'=>'Spora Comm','facebook_pic'=>'Dea / Muharomah'],
            'status' => 'in_progress',
        ]);

        $kblis = [
            ['73100','Periklanan','Izin Usaha (Efektif)','Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['58110','Penerbitan Buku','Izin Usaha (Efektif)','Gedung Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['58190','Aktivitas Penerbitan Lainnya','Izin Usaha (Efektif)','Gedung Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['70203','Aktivitas Kehumasan','Izin Usaha (Efektif)','Gedung Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['46422','Perdagangan Besar Barang Percetakan dan Penerbitan','Izin Usaha (Efektif)','Gedung Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['59122','Aktivitas Pascaproduksi Film, Video dan Program Televisi Oleh Swasta','NIB Terbit (Risiko Rendah)','Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['70209','Aktivitas Konsultasi Manajemen Lainnya','NIB Terbit (Risiko Rendah)','Gedung Epiwalk Lt. 3 Unit A-306, Jakarta Selatan'],
            ['26420','Industri Peralatan Perekam, Penerima dan Pengganda Audio & Video','NIB Terbit; Sertifikat Standar Belum Terverifikasi (Risiko Menengah Tinggi)','Epiwalk Lt. 3 Unit A-306, Jakarta Selatan (Pemenuhan maks. 90 hari kerja)'],
        ];
        $client->kblis()->delete();
        foreach ($kblis as [$code,$title,$status,$location]) {
            ClientKbli::create(['client_id'=>$client->id,'kbli_code'=>$code,'title'=>$title,'license_status'=>$status,'location'=>$location]);
        }

        $credentials = [
            'system_email' => ['spora.comm@gmail.com','Merdeka@@22','Muharomah & Giyatno','0821-7416-2002',false,'SUDAH LENGKAP','Konfirmasi akhir apakah kampanye memakai Gmail atau email berbayar @spora.co.id.'],
            'facebook_fanpage' => ['Arka Labs','', 'Dea / Muharomah','',false,'SUDAH LENGKAP','Akun FB Arka Labs sudah berteman dengan PIC; belum di-invite sebagai Admin Fanpage Spora Comm.'],
            'instagram' => ['spora.comm','jayakayaraya','Muharomah','',false,'SUDAH LENGKAP','Akses login Instagram @sporacomm diterima.'],
            'domain_panel' => ['www.spora.co.id','', 'Diana','',true,'BELUM DITERIMA','PIC tidak mengetahui pemegang akses panel domain. Klien menyetujui pembuatan domain baru untuk Landing Page khusus.'],
            'apify' => ['','', 'Muharomah','',false,'MASIH PROSES','Aktivasi paket Starter US$19/bulan pada sesi koordinasi melalui link referral tim.'],
            'meta_ads' => ['','', 'Muharomah','',false,'MASIH PROSES','Input kartu kredit Meta fisik internasional dan OTP dijadwalkan.'],
        ];
        foreach ($credentials as $platform=>$data) {
            [$username,$secret,$pic,$contact,$needsDomain,$verification,$notes] = $data;
            $attributes = ['username_or_email'=>$username,'pic_name'=>$pic,'pic_contact'=>$contact,'needs_new_domain'=>$needsDomain,'verification_status'=>$verification,'notes'=>$notes];
            $attributes['secret_value'] = $secret !== '' ? $secret : null;
            ClientCredential::updateOrCreate(['client_id'=>$client->id,'platform'=>$platform],$attributes);
        }

        $items = [
            ['legal','NPWP Perusahaan','SUDAH LENGKAP','Nomor NPWP: 021604111014000. Lampiran foto/PDF dapat diminta jika dibutuhkan verifikasi Meta.',null,null,false],
            ['access','Email Sistem & PIC OTP','SUDAH LENGKAP','Email: spora.comm@gmail.com. PIC HP/OTP: Muharomah & Giyatno. Konfirmasi akhir penggunaan Gmail atau email berbayar.',null,null,false],
            ['access','Status Nomor WA Business','SUDAH LENGKAP','Nomor 0821-7416-2002 aktif di WA Business minimal 7 hari. Perlu konfirmasi Display Name dan PIC scan QR.',null,null,false],
            ['billing','Paket & Jadwal Billing','MASIH PROSES','Sesi koordinasi Senin–Jumat 10.00–17.30 WIB ±15 menit untuk input kartu kredit fisik internasional & OTP. Pilihan paket belum dikonfirmasi.',null,'Senin–Jumat, 10.00–17.30 WIB',false],
            ['asset','Materi Penawaran, Promo & Logo Asli','SUDAH LENGKAP','3 layanan prioritas, promo Free 30-Minute Consultation, CTA WhatsApp dan meeting, serta keputusan Landing Page khusus.',null,null,false],
            ['access','Akses Admin Fanpage Facebook','SUDAH LENGKAP','Akun FB Arka Labs sudah berteman dengan PIC Dea/Muharomah; belum di-invite sebagai Admin Fanpage Spora Comm.',null,null,false],
            ['access','Password / Akses Login Instagram','SUDAH LENGKAP','Akses Instagram spora.comm diterima.',null,null,false],
            ['access','Akses Panel Domain','BELUM DITERIMA','www.spora.co.id: PIC Diana belum mengetahui pemegang akses panel domain. Persetujuan dibuatkan domain baru untuk Landing Page.',null,null,false],
            ['asset','Update Daftar Klien Terbaru','MENYUSUL','Sedang didata ulang oleh Diana untuk melengkapi portofolio Landing Page.',null,null,false],
            ['billing','Konfirmasi Pembayaran Server & WA API','BELUM DITERIMA','Pilihan paket belum dikonfirmasi: 1 bulan Rp210.000 / 6 bulan Rp1.260.000 / 12 bulan Rp2.520.000. Transfer ke BCA 825 506 5111 a.n. Felix Kusuma.',null,null,false],
            ['addon','Konfirmasi Add-On Chatbot AI & CRM','SUDAH LENGKAP','Sudah termasuk; perlu kepastian apakah menggunakan add-on dan wajib melengkapi Product Knowledge/FAQ Excel, 5–10 contoh chat, alur bot, serta kriteria lead Cold/Qualified/Hot.',null,null,true],
        ];
        $client->onboardingItems()->delete();
        foreach ($items as [$category,$name,$status,$notes,$file,$schedule,$addon]) {
            ClientOnboardingItem::updateOrCreate(
                ['client_id'=>$client->id,'item_name'=>$name],
                ['category'=>$category,'status'=>$status,'detail_notes'=>$notes,'file_path'=>$file,'billing_schedule'=>$schedule,'has_chatbot_addon'=>$addon,'package_choice'=>'']
            );
        }

        $client->knowledgeBases()->delete();
        $priorities = [
            ['Reputation Intelligence','Retainer Bulanan/Tahunan','Media Monitoring online/cetak/elektronik, Social Listening, Sentiment Analysis, Share of Voice, Issue & Trend Mapping, Reputation Early Warning, dan Competitor & Stakeholder Intelligence.','Dashboard monitoring, laporan harian/mingguan/bulanan, analisis sentimen, dan rekomendasi komunikasi.'],
            ['Digital Communication Management','Retainer Bulanan, ideal 6–12 bulan','Social Media Management, Digital Communication Strategy, Content Strategy & Production, Digital PR & Thought Leadership, Digital Marketing, Community Engagement, Website Optimization, dan Digital Performance Reporting.','Strategi digital, editorial calendar, konten rutin, kampanye digital, pengelolaan kanal, dan laporan KPI.'],
            ['Reputation & Communication Advisory','Project-based / Strategic Retainer','Audit Persepsi & Reputasi, Strategic Communication Planning, Stakeholder Mapping & Engagement, Crisis Risk Assessment, Crisis Communication Management, Reputation Recovery, Executive Communication Advisory, dan Communication Effectiveness Evaluation.','Laporan audit reputasi, peta stakeholder, strategi komunikasi, crisis communication protocol, roadmap, dan rekomendasi strategis.'],
        ];
        foreach ($priorities as $i=>$data) {
            [$title,$scope,$description,$output] = $data;
            ClientKnowledgeBase::create(['client_id'=>$client->id,'section_type'=>'priority_service','category_label'=>'Prioritas '.($i+1),'title'=>$title,'metadata'=>['scope'=>$scope,'description'=>$description,'output'=>$output]]);
        }
        ClientKnowledgeBase::create(['client_id'=>$client->id,'section_type'=>'promo_cta','title'=>'Penawaran Pembuka / Promo Khusus','metadata'=>['promo'=>'Free 30-Minute Communication & Reputation Consultation','cta'=>'1. Membalas chat WhatsApp untuk diskusi. 2. Menjadwalkan meeting / presentasi.','landing_page'=>'Perlu dibuatkan Landing Page penawaran khusus, tidak langsung diarahkan ke website utama www.spora.co.id.','coverage'=>'BUMN, Kementerian/Lembaga, dan korporasi']]);

        $ecosystem = [
            ['ADVISE (Strategy First)','Strategi Komunikasi','Audit situasi & isu, pemetaan stakeholder & audiens, positioning, message house, roadmap kampanye, dan KPI.'],
            ['ADVISE (Strategy First)','Branding','Corporate Branding, Product Branding, Personal Branding, Nation Branding & Advocacy Communication, riset, audit brand, identitas visual, dan storytelling strategy.'],
            ['ADVISE (Strategy First)','Crisis Handling & Reputation Management','Reputation Audit & Risk Mapping, Crisis Preparedness & Response, crisis command center, holding statement, Q&A, media line, dan Reputation Recovery.'],
            ['LISTEN & CONNECT (Media Intelligence)','Media Monitoring & Social Listening','Pemantauan pemberitaan dan percakapan media sosial, klasifikasi isu, sentimen, peta penyebaran, early warning, dashboard, dan rekomendasi respons.'],
            ['LISTEN & CONNECT (Media Intelligence)','Media Handling & Media Relations','Press release, pitch media, follow-up redaksi, media briefing, konferensi pers, wawancara, media visit, dan laporan coverage.'],
            ['LISTEN & CONNECT (Media Intelligence)','Event Organizer for Media','Media gathering, media visit, press conference, forum, media workshop, dan editorial visit.'],
            ['ENGAGE & GROW (Digital & Campaign)','Social Media Management','Strategi media sosial, content pillar, kalender editorial, caption, desain grafis, video, community management, dan evaluasi IG/FB/TikTok/YouTube/LinkedIn.'],
            ['ENGAGE & GROW (Digital & Campaign)','Digital Marketing','Google Ads, TikTok Ads, Meta Ads, segmentasi audiens, optimasi creative/conversion dan anggaran.'],
            ['ENGAGE & GROW (Digital & Campaign)','Website Optimization','Audit, SEO, GEO/AI Search, discoverability, analytics, dan pembenahan struktur konten.'],
            ['CREATE & REPORT (Editorial & Reporting)','Publication','Majalah cetak/digital, e-magazine/e-book, newsletter, buku profil/sejarah, biografi, dan ghostwriting.'],
            ['CREATE & REPORT (Editorial & Reporting)','Production House','Produksi audiovisual, iklan layanan masyarakat/produk, company profile, dokumenter, web series, live streaming, agency dan media buying TV.'],
            ['CREATE & REPORT (Editorial & Reporting)','Corporate Reporting','Annual Report, Sustainability Report, ESG Report, GRI, wawancara, copywriting bilingual, desain, photosession, cetak, compliance advisory, dan pendampingan kompetisi.'],
        ];
        foreach ($ecosystem as [$category,$title,$scope]) {
            ClientKnowledgeBase::create(['client_id'=>$client->id,'section_type'=>'service_ecosystem','category_label'=>$category,'title'=>$title,'metadata'=>['scope'=>$scope]]);
        }

        $team = [
            ['Burhanudin','Team Leader & Coordinator','Memiliki pengalaman luas dalam koordinasi penyusunan Annual Report dan Sustainability Report untuk berbagai BUMN dan Perusahaan Terbuka.'],
            ['Nana Oktavia Musliana, S.S.','Project Leader / Lead Copywriter (GRI Certified)','Alumni UI, spesialis penulis AR/SR berpengalaman 20+ tahun sejak 2003. Menangani puluhan laporan korporasi terkemuka.'],
            ['Pudji Lestari, S.Pd.','Senior Business Writer / Lead Copywriter','Jurnalis senior 14+ tahun di Bisnis Indonesia, Forbes Indonesia, Policy & Regulatory Report, dan MLex Market Insight.'],
            ['Dea Riska','Corporate Comm Specialist & Translator','Spesialis komunikasi korporat dan penerjemahan resmi bilingual Indonesia–Inggris.'],
        ];
        foreach ($team as [$name,$role,$qualification]) {
            ClientKnowledgeBase::create(['client_id'=>$client->id,'section_type'=>'team_member','title'=>$name,'metadata'=>['role'=>$role,'qualification'=>$qualification]]);
        }

        $portfolios = [
            ['Energi & Utilitas','PT PLN Icon Plus / PT Indonesia Comnets Plus','2022–2024','AR & SR 2022, 2023, dan 2024'],
            ['Energi & Utilitas','PT PLN Mandau Cipta Tenaga Nusantara','2025','AR dan SR 2025'],
            ['Keuangan, Perbankan & Asuransi','PT Asuransi Jasindo','2024','AR dan SR 2024'],
            ['Keuangan, Perbankan & Asuransi','PT Askrida Syariah','2022–2024','AR 2022, 2023, dan 2024'],
            ['Keuangan, Perbankan & Asuransi','PT Mirae Asset Sekuritas Indonesia','2025','Sustainability Report 2025 dan workshop keberlanjutan'],
            ['Kesehatan & Farmasi','PT Kimia Farma Apotek','2025','Annual Report 2025'],
            ['Transportasi & Infrastruktur','PT Pelita Air Service','2025','Annual Report dan Sustainability Report 2025'],
            ['Transportasi & Infrastruktur','PT Pemalang Batang Tol Road','2022–2023','AR dan SR 2022–2023'],
            ['Industri, Manufaktur & Pertambangan','PT Nusa Karya Arindo','2023–2025','AR/SR 2023, AR 2024, AR 2025, dan ESG Report 2025'],
            ['Industri, Manufaktur & Pertambangan','PT Sinergi Mitra Lestari Indonesia','2023–2024','AR/SR 2023 dan Integrated AR 2024'],
            ['Industri, Manufaktur & Pertambangan','PT Sinergi Inti Plastisindo Tbk','2024','Annual Report 2024'],
            ['Industri, Manufaktur & Pertambangan','PT SIER','2023','Annual Report dan Sustainability Report 2023'],
            ['Telekomunikasi, Teknologi & Sektor Lain','PT Aplikanusa Lintasarta','2024–2025','Sustainability Report'],
            ['Telekomunikasi, Teknologi & Sektor Lain','PT IAS Support Indonesia','2025','Annual Report'],
            ['Telekomunikasi, Teknologi & Sektor Lain','BAZNAS','2022','Annual Report'],
        ];
        foreach ($portfolios as [$category,$title,$year,$project]) {
            ClientKnowledgeBase::create(['client_id'=>$client->id,'section_type'=>'portfolio','category_label'=>$category,'title'=>$title,'year'=>$year,'metadata'=>['project'=>$project]]);
        }

        $awards = [
            ['2026','PT Jasa Raharja | Majalah SECURE','Gold Winner — Kategori Owned Media (Subkategori Majalah Internal), PRIA 2026'],
            ['2026','PT Jasa Raharja | Majalah SECURE','Bronze Winner — Kategori Media Internal (Magazine), AHI 2026'],
            ['2026','PT Perkebunan Nusantara I | OneMagz','Bronze Winner — Kategori Media Internal (Magazine), AHI 2026'],
            ['2026','PT Perkebunan Nusantara I | Video Profile','Bronze Winner — Kategori Media Internal (Video Profile), AHI 2026'],
            ['2025','Kementerian Pertanian | Siaran Pers','Terbaik II — Kategori Siaran Pers (Media Online), AMH 2025'],
            ['2025','Kementerian Pertanian | Audiovisual','Terbaik III — Kategori Audio Visual, AMH 2025'],
            ['2024','PLN Icon Plus | Annual Report 2023','Penghargaan Khusus — Perusahaan dengan Peningkatan Skor Pengungkapan ARA Paling Tinggi'],
            ['2024','Kementerian Kelautan dan Perikanan | Majalah SEGARA','Terbaik I — Kategori Penerbitan Media Internal, AMH 2024'],
            ['2022','PT Kereta Commuter Indonesia | Majalah C-News (Jan 2021)','Gold Winner — E-Magazine, InMA 2022'],
            ['2022','PT Kereta Commuter Indonesia | Majalah C-News (Jun 2021)','Silver Winner — E-Magazine, PRIA 2022'],
            ['2022','PT Kereta Commuter Indonesia | Majalah C-News (Jun 2022)','Bronze Winner — Media Internal Majalah Cetak, AHI 2022'],
            ['2018','PT Elnusa Petrofin | Creative Performance','Medali Gold — Upstream Improvement & Innovation Awards 2018'],
        ];
        foreach ($awards as [$year,$title,$award]) {
            ClientKnowledgeBase::create(['client_id'=>$client->id,'section_type'=>'award','title'=>$title,'year'=>$year,'metadata'=>['award'=>$award]]);
        }

        $checklists = [
            'TAHAP_0'=>'Pembuatan Profil Browser Chrome Isolasi Klien',
            'TAHAP_A'=>'Website & Domain',
            'TAHAP_B'=>'Integrasi WhatsApp Business Coexistence',
            'TAHAP_C'=>'Ekstraksi & Input Cookie sessionid Instagram',
            'TAHAP_D'=>'Pengujian Deliverability Email Sistem',
            'TAHAP_E'=>'Aktivasi Paket Starter Apify',
            'TAHAP_FG'=>'Rekap Kredensial & Pembuatan/Approval 3 Template Blast',
        ];
        foreach ($checklists as $code=>$title) {
            ClientTechnicalChecklist::updateOrCreate(
                ['client_id'=>$client->id,'task_code'=>$code],
                ['task_title'=>$title,'is_completed'=>false,'completed_at'=>null]
            );
        }
    }
}
