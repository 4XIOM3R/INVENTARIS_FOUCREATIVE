<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Inventaris Klien')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="glass-page">
@if(session('success'))<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)" class="fixed right-5 top-5 z-50 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm">{{ session('success') }}</div>@endif
@if($errors->any())<div class="fixed right-5 top-5 z-50 max-w-md rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm">{{ $errors->first() }}</div>@endif
@yield('body')
</body>
</html>
