@extends('layouts.app')

@section('title', 'Deliveries')
@section('page_title', 'Deliveries')

@section('header_actions')
    @if(auth()->user()->canManage())
    <a href="{{ route('deliveries.create') }}" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus"></i> New Delivery
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('deliveries.index') }}" class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-wrap items-center gap-4">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                @foreach(['assigned' => 'Assigned', 'in_transit' => 'In Transit', 'arrived' => 'Arrived', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($deliveries as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $delivery->delivery_code }}</span>
                            <div class="text-xs text-gray-500">{{ $delivery->delivery_date?->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $delivery->customer?->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $delivery->customer?->customer_code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $delivery->driver?->user?->full_name ?? 'Unassigned' }}</div>
                            <div class="text-xs text-gray-500">{{ $delivery->driver?->driver_code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $delivery->status === 'completed' ? 'bg-green-100 text-green-800' : ($delivery->status === 'in_transit' ? 'bg-orange-100 text-orange-800' : ($delivery->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ str_replace('_', ' ', strtoupper($delivery->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @foreach($delivery->items as $item)<div>{{ $item->quantity }}x {{ $item->cylinder_type }}</div>@endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('deliveries.show', $delivery) }}" class="text-blue-600 hover:text-blue-900">View / Edit</a>
                                @if($delivery->customer?->address)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($delivery->customer->address) }}" target="_blank" class="text-green-600 hover:text-green-900" title="View Map">
                                    <i class="fas fa-map-pin"></i>
                                </a>
                                @endif
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('deliveries.destroy', $delivery) }}" onsubmit="return confirm('Are you sure you want to delete this delivery?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No deliveries found. Create one above!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($deliveries->hasPages())
        <div>{{ $deliveries->links() }}</div>
    @endif
</div>
@endsection