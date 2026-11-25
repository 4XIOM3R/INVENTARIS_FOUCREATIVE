<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Inventaris Klien')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800">
@if(session('success'))<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)" class="fixed right-5 top-5 z-50 rounded-lg bg-emerald-600 px-5 py-3 text-white shadow-lg">{{ session('success') }}</div>@endif
@if($errors->any())<div class="fixed right-5 top-5 z-50 max-w-md rounded-lg bg-red-600 px-5 py-3 text-white shadow-lg">{{ $errors->first() }}</div>@endif
@yield('body')
</body>
</html>
