@extends('layouts.app')

@section('title', 'Delivery: ' . $delivery->delivery_code)
@section('page_title', 'Delivery Details')

@section('header_actions')
    @if(auth()->user()->canManage())
    <a href="{{ route('deliveries.edit', $delivery) }}" class="flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
        <i class="fas fa-edit"></i> Edit
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $delivery->delivery_code }}</h2>
                    <span class="px-2 py-1 text-xs font-medium rounded-full mt-2 inline-block
                        {{ $delivery->status === 'completed' ? 'bg-green-100 text-green-800' : ($delivery->status === 'in_transit' ? 'bg-orange-100 text-orange-800' : ($delivery->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                        {{ str_replace('_', ' ', strtoupper($delivery->status)) }}
                    </span>
                </div>
                @if($delivery->customer?->address)
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($delivery->customer->address) }}" target="_blank" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="View Map">
                    <i class="fas fa-map-pin text-2xl"></i>
                </a>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-600">Customer</p><p class="font-medium">{{ $delivery->customer?->name }}</p></div>
                <div><p class="text-sm text-gray-600">Driver</p><p class="font-medium">{{ $delivery->driver?->user?->full_name ?? 'Unassigned' }}</p></div>
                <div><p class="text-sm text-gray-600">Delivery Date</p><p class="font-medium">{{ $delivery->delivery_date?->format('d M Y') }}</p></div>
                <div><p class="text-sm text-gray-600">Delivery Time</p><p class="font-medium">{{ $delivery->delivery_time?->format('H:i') ?? 'N/A' }}</p></div>
            </div>

            @if($delivery->customer?->site_notes)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Site Notes</p>
                <p class="font-medium">{{ $delivery->customer->site_notes }}</p>
            </div>
            @endif

            @if($delivery->special_instructions)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Special Instructions</p>
                <p class="font-medium">{{ $delivery->special_instructions }}</p>
            </div>
            @endif

            <div class="mt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Items</h3>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cylinder</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($delivery->items as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $item->cylinder_type }}</td>
                                <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-2 font-bold">Total Ordered</td>
                            <td class="px-4 py-2 text-right font-bold">{{ $delivery->quantity_ordered }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Completion Details</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Quantity Delivered</span><span class="font-medium">{{ $delivery->quantity_delivered ?? $delivery->quantity_ordered }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Empty Collected</span><span class="font-medium">{{ $delivery->empty_cylinders_collected }}</span></div>
                    @if($delivery->driver_notes)
                    <div class="mt-2"><span class="text-gray-600">Driver Notes:</span><p class="font-medium mt-1">{{ $delivery->driver_notes }}</p></div>
                    @endif
                    @if($delivery->arrived_at)
                        <div class="flex justify-between"><span class="text-gray-600">Arrived At</span><span class="font-medium">{{ $delivery->arrived_at->format('d M Y H:i') }}</span></div>
                    @endif
                    @if($delivery->completed_at)
                        <div class="flex justify-between"><span class="text-gray-600">Completed At</span><span class="font-medium">{{ $delivery->completed_at->format('d M Y H:i') }}</span></div>
                    @endif
                </div>
            </div>

            @if($delivery->status !== 'completed' && !auth()->user()->isDriver())
                @if(auth()->user()->canManage())
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Mark Completed</h2>
                    <form method="POST" action="{{ route('deliveries.complete', $delivery) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Delivered</label>
                            <input type="number" name="quantity_delivered" value="{{ $delivery->quantity_ordered }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Empty Cylinders Collected</label>
                            <input type="number" name="empty_cylinders_collected" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Complete Delivery
                        </button>
                    </form>
                </div>
                @endif
            @endif
        </div>
    </div>

    @if($delivery->logs->isNotEmpty())
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Activity Log</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($delivery->logs as $log)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ str_replace('_', ' ', $log->action) }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $log->details ? json_encode($log->details) : '' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection