<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AmbatuGrow ERP')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#1f5c3d',
                            dark: '#163f2b',
                            light: '#e9f5ee',
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-60 shrink-0 bg-brand text-white flex flex-col">
        <div class="px-5 py-6 border-b border-white/10">
            <div class="w-9 h-9 bg-white rounded mb-2"></div>
            <div class="font-bold leading-tight">AMBATUGROW</div>
            <div class="text-xs text-white/60 tracking-wide">ERP SYSTEM</div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('requisitions.create') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-white/10 {{ request()->routeIs('requisitions.create') ? 'bg-white/15 font-semibold' : '' }}">
                <span>➕</span> Create PO
            </a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-white/10">
                <span>📶</span> Tracking
            </a>
            <a href="{{ route('requisitions.create') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-white/10 {{ request()->routeIs('requisitions.*') ? 'bg-white/15 font-semibold' : '' }}">
                <span>🗂️</span> Order Management
            </a>
            <a href="{{ route('approvals.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-white/10 {{ request()->routeIs('approvals.*') ? 'bg-white/15 font-semibold' : '' }}">
                <span>✅</span> Approvals
            </a>
        </nav>

        <div class="px-3 py-4 border-t border-white/10 text-sm space-y-1">
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-white/10">⚙️ Settings</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-white/10">❓ Support</a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b px-6 py-3 flex items-center gap-4">
            <div class="flex-1 max-w-xl">
                <input type="text" placeholder="🔎 Search"
                       class="w-full border rounded-md px-3 py-2 text-sm bg-gray-50">
            </div>
            <div class="text-sm text-gray-500">{{ now()->format('F d, Y') }}</div>
            <div class="text-lg">🔔</div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                <div class="text-sm">
                    <div class="font-semibold leading-none">JJ M.</div>
                    <div class="text-xs text-gray-500">Manager</div>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-2">
                {{ session('success') }}
            </div>
        @endif

        <main class="p-6 flex-1">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
