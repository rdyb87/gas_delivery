@extends('layouts.app')

@section('title', 'Driver: ' . ($driver->user->full_name ?? 'Driver'))
@section('page_title', 'Driver Details')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $driver->user?->full_name }}</h2>
                    <p class="text-blue-600 font-medium">{{ $driver->driver_code }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $driver->is_available ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                    {{ ucwords(str_replace('_', ' ', $driver->status)) }}
                </span>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-600">Phone</p><p class="font-medium">{{ $driver->user?->phone ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-600">Email</p><p class="font-medium">{{ $driver->user?->email ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-600">License Number</p><p class="font-medium">{{ $driver->license_number ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-600">License Expiry</p><p class="font-medium">{{ $driver->license_expiry?->format('d M Y') ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-600">Lorry Plate</p><p class="font-medium">{{ $driver->lorry_plate ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-600">Lorry Capacity</p><p class="font-medium">{{ $driver->lorry_capacity ?? 'N/A' }}</p></div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-blue-600">{{ $statistics['total_deliveries'] }}</p>
                    <p class="text-sm text-gray-600">Total Deliveries</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-green-600">{{ $statistics['completed_deliveries'] }}</p>
                    <p class="text-sm text-gray-600">Completed</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-purple-600">{{ $statistics['today_deliveries'] }}</p>
                    <p class="text-sm text-gray-600">Today</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 space-y-4">
            <h2 class="text-xl font-bold text-gray-900">Update Driver</h2>
            <form method="POST" action="{{ route('drivers.update', $driver) }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $driver->user?->full_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $driver->user?->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $driver->user?->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-xs text-gray-400">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">License Number</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">License Expiry</label>
                    <input type="date" name="license_expiry" value="{{ old('license_expiry', $driver->license_expiry?->toDateString()) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lorry Plate</label>
                    <input type="text" name="lorry_plate" value="{{ old('lorry_plate', $driver->lorry_plate) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lorry Capacity</label>
                    <input type="number" name="lorry_capacity" value="{{ old('lorry_capacity', $driver->lorry_capacity) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked($driver->user->is_active) class="rounded">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Update Driver
                </button>
            </form>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Availability</h3>
                <form method="POST" action="{{ route('drivers.availability', $driver) }}" class="flex items-center gap-2">
                    @csrf @method('PUT')
                    <select name="status" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="available" @selected($driver->status === 'available')>Available</option>
                        <option value="on_duty" @selected($driver->status === 'on_duty')>On Duty</option>
                        <option value="off_duty" @selected($driver->status === 'off_duty')>Off Duty</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Delivery History</h2>
            @if(auth()->user()->canManage())
            <a href="{{ route('deliveries.create') }}?driver_id={{ $driver->id }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Assign New Job</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($driver->deliveries->sortByDesc('delivery_date') as $delivery)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap"><a href="{{ route('deliveries.show', $delivery) }}" class="font-medium text-blue-600 hover:underline">{{ $delivery->delivery_code }}</a></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $delivery->customer?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $delivery->delivery_date?->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $delivery->status === 'completed' ? 'bg-green-100 text-green-800' : ($delivery->status === 'in_transit' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ str_replace('_', ' ', strtoupper($delivery->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No deliveries for this driver yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection