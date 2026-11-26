@extends('layouts.app')
@section('title','Dashboard Admin')
@section('body')
@php
  $statusLabels = ['draft' => 'Draft', 'in_progress' => 'Dalam Proses', 'ready_to_deploy' => 'Siap Deploy', 'completed' => 'Selesai'];
  $statusStyles = [
    'draft' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
    'in_progress' => 'border-blue-200 bg-blue-50 text-blue-700',
    'ready_to_deploy' => 'border-amber-200 bg-amber-50 text-amber-700',
    'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
  ];
  $statDots = ['draft' => 'bg-zinc-400', 'in_progress' => 'bg-blue-500', 'ready_to_deploy' => 'bg-amber-500', 'completed' => 'bg-emerald-500'];
@endphp
<div x-data="{modal:false}" class="dashboard-page relative min-h-screen">
  <nav class="dashboard-nav sticky top-0 z-30 border-b"><div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6"><div><span class="brand-mark text-lg font-semibold">FOUCREATIVE</span><span class="brand-copy ml-2 text-sm">Inventaris Klien</span></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button rounded-lg px-4 py-2 text-sm font-medium">Keluar</button></form></div></nav>
  <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="summary-label text-sm">Ringkasan seluruh klien</p><h1 class="dashboard-title text-3xl font-semibold">Dashboard</h1></div><button @click="modal=true" class="glass-btn-primary add-client-button w-full rounded-lg px-5 py-3 font-semibold sm:w-auto">+ Tambah Klien Baru</button></div>
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
      @foreach(['draft'=>'Draft','in_progress'=>'Dalam Proses','ready_to_deploy'=>'Siap Deploy','completed'=>'Selesai'] as $key=>$label)<div class="stat-card rounded-xl p-5"><p class="stat-label flex items-center gap-2 text-sm"><span class="h-2 w-2 rounded-full {{ $statDots[$key] }}"></span>{{ $label }}</p><p class="stat-value mt-2 text-3xl font-semibold tabular-nums">{{ $stats[$key] }}</p></div>@endforeach
    </div>
    <div class="table-panel hidden overflow-hidden rounded-xl md:block"><table class="w-full text-left text-sm"><thead class="dashboard-table-head text-xs uppercase"><tr><th class="px-6 py-4">Klien</th><th>Status</th><th>Progress</th><th class="px-6 py-4">Aksi</th></tr></thead><tbody>
    @forelse($clients as $client)<tr class="client-row border-t"><td class="px-6 py-5"><p class="client-name font-medium">{{ $client->company_name }}</p><p class="client-brand text-sm">{{ $client->brand_name ?? '-' }}</p></td><td><span class="status-badge inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $statusStyles[$client->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">{{ $statusLabels[$client->status] ?? $client->status }}</span></td><td><div class="progress-track h-1.5 w-32 rounded-full"><div class="progress-fill h-1.5 rounded-full" style="width:{{ $client->completion_percentage }}%"></div></div><span class="progress-value text-xs tabular-nums">{{ $client->completion_percentage }}%</span></td><td class="px-6 py-5" x-data="{toast:false}"><div class="flex gap-3 text-sm"><button @click="navigator.clipboard.writeText('{{ url('/onboarding/'.$client->onboarding_token) }}');toast=true;setTimeout(()=>toast=false,2000)" class="text-zinc-600 hover:text-zinc-950">Copy Link</button><span x-show="toast" class="copy-toast text-xs">Link berhasil disalin</span><a href="{{ route('admin.clients.show',$client) }}" class="font-medium text-zinc-700 hover:text-zinc-950">Detail</a><a href="{{ route('admin.clients.export-pdf',$client) }}" class="text-zinc-500 hover:text-zinc-950">PDF</a></div></td></tr>@empty<tr><td colspan="4" class="empty-state px-6 py-10 text-center">Belum ada klien.</td></tr>@endforelse
    </tbody></table></div>
    <div class="mobile-client-list space-y-3 md:hidden">
      @forelse($clients as $client)
        <article class="mobile-client-card rounded-2xl p-4" x-data="{toast:false}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="client-name break-words font-medium">{{ $client->company_name }}</p>
              <p class="client-brand mt-1 break-words text-sm">{{ $client->brand_name ?? '-' }}</p>
            </div>
            <span class="status-badge shrink-0 rounded-full border px-3 py-1 text-xs font-medium {{ $statusStyles[$client->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">{{ $statusLabels[$client->status] ?? $client->status }}</span>
          </div>
          <div class="mt-4">
            <div class="mb-2 flex items-center justify-between text-xs">
              <span class="mobile-progress-label">Progress</span>
              <span class="progress-value">{{ $client->completion_percentage }}%</span>
            </div>
            <div class="progress-track h-1.5 w-full rounded-full">
              <div class="progress-fill h-1.5 rounded-full" style="width:{{ $client->completion_percentage }}%"></div>
            </div>
          </div>
          <div class="mt-4 grid grid-cols-3 gap-2">
            <button @click="navigator.clipboard.writeText('{{ url('/onboarding/'.$client->onboarding_token) }}');toast=true;setTimeout(()=>toast=false,2000)" class="mobile-action action-copy min-h-11 rounded-xl text-xs font-semibold">Copy Link</button>
            <a href="{{ route('admin.clients.show',$client) }}" class="mobile-action action-detail min-h-11 rounded-xl text-xs font-semibold">Detail</a>
            <a href="{{ route('admin.clients.export-pdf',$client) }}" class="mobile-action action-pdf min-h-11 rounded-xl text-xs font-semibold">PDF</a>
          </div>
          <p x-show="toast" x-cloak class="copy-toast mt-2 text-center text-xs">Link berhasil disalin</p>
        </article>
      @empty
        <div class="mobile-client-card empty-state rounded-2xl px-6 py-10 text-center">Belum ada klien.</div>
      @endforelse
    </div>
  </main>
  <div x-show="modal" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-950/30 p-4"><div @click.outside="modal=false" class="glass-panel dashboard-modal w-full max-w-lg rounded-xl p-6"><h2 class="mb-5 text-xl font-semibold">Tambah Klien Baru</h2><form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-4">@csrf<input name="company_name" required placeholder="Nama Perusahaan" class="glass-input w-full px-4 py-3"><input name="brand_name" placeholder="Nama Brand" class="glass-input w-full px-4 py-3"><input name="phone" placeholder="Kontak PIC" class="glass-input w-full px-4 py-3"><div class="flex justify-end gap-3"><button type="button" @click="modal=false" class="glass-btn-secondary modal-cancel min-h-11 rounded-lg px-4 py-2">Batal</button><button class="glass-btn-primary min-h-11 rounded-lg px-5 py-2 font-medium">Simpan</button></div></form></div></div>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
