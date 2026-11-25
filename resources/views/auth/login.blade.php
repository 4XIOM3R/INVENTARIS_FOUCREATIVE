@extends('layouts.app')
@section('title','Login Admin')
@section('body')
<main class="flex min-h-screen items-center justify-center bg-slate-950 p-6">
  <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
    <div class="mb-8"><div class="text-sm font-bold uppercase tracking-widest text-indigo-600">Command Center</div><h1 class="mt-2 text-3xl font-bold text-slate-900">Masuk Admin</h1></div>
    <form method="POST" action="{{ route('login') }}" class="space-y-5">@csrf
      <div><label class="mb-2 block text-sm font-semibold">Username</label><input name="username" type="text" value="{{ old('username') }}" autocomplete="username" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500"></div>
      <div><label class="mb-2 block text-sm font-semibold">Password</label><input name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500"></div>
      @error('username')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
      <button class="w-full rounded-xl bg-indigo-600 py-3 font-bold text-white hover:bg-indigo-700">Masuk ke Dashboard</button>
    </form>
  </div>
</main>
@endsection
