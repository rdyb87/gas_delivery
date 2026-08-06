@extends('layouts.app')

@section('title', 'Drivers')
@section('page_title', 'Drivers')

@section('header_actions')
    @if(auth()->user()->canManage())
    <button onclick="document.getElementById('driverModal').classList.remove('hidden')" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus"></i> Add Driver
    </button>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('drivers.index') }}" class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code or lorry..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="available" @selected(request('status') === 'available')>Available</option>
                <option value="on_duty" @selected(request('status') === 'on_duty')>On Duty</option>
                <option value="off_duty" @selected(request('status') === 'off_duty')>Off Duty</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($drivers as $driver)
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-truck text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $driver->user?->full_name ?? 'N/A' }}</h3>
                            <p class="text-sm text-gray-600">{{ $driver->driver_code }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $driver->is_available ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                        {{ $driver->is_available ? 'Available' : 'On Delivery' }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-map-pin w-4"></i><span>Lorry: {{ $driver->lorry_plate ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-box w-4"></i><span>{{ $driver->deliveries->count() }} deliveries</span>
                    </div>
                    @if($driver->license_expiry)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-id-card w-4"></i><span>License: {{ $driver->license_expiry->format('d M Y') }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('drivers.show', $driver) }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium text-center">
                        View / Edit
                    </a>
                    @if(auth()->user()->canManage())
                    <a href="{{ route('deliveries.create') }}?driver_id={{ $driver->id }}" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium text-center">
                        Assign Job
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('drivers.destroy', $driver) }}" onsubmit="return confirm('Are you sure you want to delete this driver?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-10 text-gray-500 bg-white rounded-xl shadow">
                No drivers found. Add one above!
            </div>
        @endforelse
    </div>
</div>

@if(auth()->user()->canManage())
<div id="driverModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0">
            <h2 class="text-xl font-bold">Add New Driver</h2>
            <button onclick="document.getElementById('driverModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>

        <form method="POST" action="{{ route('drivers.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" placeholder="Default: driver123" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input type="text" name="full_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">License Number</label>
                    <input type="text" name="license_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">License Expiry</label>
                    <input type="date" name="license_expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lorry Plate</label>
                    <input type="text" name="lorry_plate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lorry Capacity</label>
                    <input type="number" name="lorry_capacity" value="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-4 flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('driverModal').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-save"></i> Save Driver
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection