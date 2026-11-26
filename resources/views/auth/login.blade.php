@extends('layouts.app')
@section('title','Login Admin')
@section('body')
<main class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
  <div class="relative w-full max-w-sm rounded-2xl border border-zinc-200 bg-white p-8">
    <div class="mb-8"><div class="text-xs font-semibold tracking-[0.16em] text-zinc-500">FOUCREATIVE</div><p class="mt-2 text-sm font-medium text-zinc-500">Command Center</p><h1 class="mt-1 text-3xl font-semibold text-zinc-950">Masuk Admin</h1></div>
    <form method="POST" action="{{ route('login') }}" class="space-y-5">@csrf
      <div><label class="mb-2 block text-sm font-semibold">Username</label><input name="username" type="text" value="{{ old('username') }}" autocomplete="username" required class="glass-input w-full px-4 py-3"></div>
      <div><label class="mb-2 block text-sm font-semibold">Password</label><input name="password" type="password" required class="glass-input w-full px-4 py-3"></div>
      @error('username')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
      <button class="glass-btn-primary w-full rounded-xl py-3 font-semibold">Masuk ke Dashboard</button>
    </form>
  </div>
</main>
@endsection
