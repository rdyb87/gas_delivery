@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-1">Welcome back, {{ auth()->user()->full_name }}! Here's your overview.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card title="Total Deliveries" :value="$stats['total_deliveries']" icon="fa-box" color="from-blue-500 to-blue-600" />
        <x-stat-card title="Today's Deliveries" :value="$stats['today_deliveries']" icon="fa-calendar" color="from-green-500 to-green-600" />
        <x-stat-card title="Completed Today" :value="$stats['completed_today']" icon="fa-check-circle" color="from-purple-500 to-purple-600" />
        <x-stat-card title="Pending" :value="$stats['pending_deliveries']" icon="fa-clock" color="from-orange-500 to-orange-600" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recent Deliveries</h2>
                <a href="{{ route('deliveries.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</a>
            </div>
            <div class="space-y-4">
                @forelse($recentDeliveries as $delivery)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-500 transition-colors">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $delivery->customer?->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-600">{{ $delivery->driver?->user?->full_name ?? 'Unassigned' }} &bull; {{ $delivery->delivery_date?->format('d M Y') }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full
                            {{ $delivery->status === 'completed' ? 'bg-green-100 text-green-800' : ($delivery->status === 'in_transit' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                            {{ str_replace('_', ' ', ucwords($delivery->status)) }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">No recent deliveries found</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                @if(auth()->user()->canManage())
                <a href="{{ route('customers.create') }}" class="flex flex-col items-center justify-center p-6 border-2 border-blue-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 text-blue-600 transition-all">
                    <i class="fas fa-plus text-xl mb-2"></i><span class="text-sm font-medium">New Customer</span>
                </a>
                <a href="{{ route('deliveries.create') }}" class="flex flex-col items-center justify-center p-6 border-2 border-green-200 rounded-xl hover:border-green-500 hover:bg-green-50 text-green-600 transition-all">
                    <i class="fas fa-box text-xl mb-2"></i><span class="text-sm font-medium">New Delivery</span>
                </a>
                @endif
                <a href="{{ route('scanner.index') }}" class="flex flex-col items-center justify-center p-6 border-2 border-purple-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 text-purple-600 transition-all">
                    <i class="fas fa-qrcode text-xl mb-2"></i><span class="text-sm font-medium">Scan QR Code</span>
                </a>
                <a href="{{ route('reports.index') }}" class="flex flex-col items-center justify-center p-6 border-2 border-orange-200 rounded-xl hover:border-orange-500 hover:bg-orange-50 text-orange-600 transition-all">
                    <i class="fas fa-chart-bar text-xl mb-2"></i><span class="text-sm font-medium">View Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection