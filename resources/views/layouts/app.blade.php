<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gas Delivery System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen bg-gray-50">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white shadow-2xl transition-transform duration-300 lg:translate-x-0 -translate-x-full">
            <div class="h-full flex flex-col">
                <div class="p-6 border-b border-blue-500">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-lg backdrop-blur">
                            <i class="fas fa-truck text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-lg">Gas Delivery</h2>
                            <p class="text-xs text-blue-200">Factory System</p>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 p-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-white text-blue-600 shadow-lg' : 'text-white hover:bg-white/10' }}">
                        <i class="fas fa-home w-5"></i><span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('customers.*') ? 'bg-white text-blue-600 shadow-lg' : 'text-white hover:bg-white/10' }}">
                        <i class="fas fa-users w-5"></i><span class="font-medium">Customers</span>
                    </a>
                    <a href="{{ route('drivers.index') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('drivers.*') ? 'bg-white text-blue-600 shadow-lg' : 'text-white hover:bg-white/10' }}">
                        <i class="fas fa-truck w-5"></i><span class="font-medium">Drivers</span>
                    </a>
                    <a href="{{ route('deliveries.index') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('deliveries.*') ? 'bg-white text-blue-600 shadow-lg' : 'text-white hover:bg-white/10' }}">
                        <i class="fas fa-box w-5"></i><span class="font-medium">Deliveries</span>
                    </a>
                    @if(auth()->user()?->isDriver())
                    <a href="{{ route('scanner.index') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('scanner.*') ? 'bg-white text-blue-600 shadow-lg' : 'text-white hover:bg-white/10' }}">
                        <i class="fas fa-qrcode w-5"></i><span class="font-medium">QR Scanner</span>
                    </a>
                    @endif
                    <a href="{{ route('reports.index') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('reports.*') ? 'bg-white text-blue-600 shadow-lg' : 'text-white hover:bg-white/10' }}">
                        <i class="fas fa-chart-bar w-5"></i><span class="font-medium">Reports</span>
                    </a>
                </nav>

                <div class="p-4 border-t border-blue-500">
                    <div class="flex items-center gap-3 mb-4 p-3 bg-white/10 rounded-lg">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="font-bold text-lg">{{ strtoupper(substr(auth()->user()?->full_name ?? auth()->user()?->username, 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <p class="text-sm font-medium truncate">{{ auth()->user()?->full_name ?? auth()->user()?->username }}</p>
                            <p class="text-xs text-blue-200 truncate">{{ strtoupper(auth()->user()?->role) }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i><span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="lg:ml-64">
            <header class="bg-white shadow-sm sticky top-0 z-40">
                <div class="px-6 py-4 flex items-center justify-between">
                    <button id="sidebarToggle" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-bars w-6 text-gray-600"></i>
                    </button>
                    <h1 class="text-xl font-bold text-gray-800 lg:block hidden">@yield('page_title', 'Gas Delivery System')</h1>
                    <div class="flex items-center gap-4 ml-auto">
                        @yield('header_actions')
                    </div>
                </div>
            </header>

            <main class="p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul class="list-disc ml-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>