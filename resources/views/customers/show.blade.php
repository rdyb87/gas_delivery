@extends('layouts.app')

@section('title', 'Customer: ' . $customer->name)
@section('page_title', 'Customer Details')

@section('header_actions')
    @if(auth()->user()->canManage())
    <a href="{{ route('customers.edit', $customer) }}" class="flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
        <i class="fas fa-edit"></i> Edit
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $customer->name }}</h2>
            <p class="text-blue-600 font-medium">{{ $customer->customer_code }}</p>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Dealer Type</p>
                    <p class="font-medium">{{ $customer->dealer_type }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Contact Person</p>
                    <p class="font-medium">{{ $customer->contact_person ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="font-medium">{{ $customer->phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-medium">{{ $customer->email ?? 'N/A' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600">Address</p>
                    <p class="font-medium">{{ $customer->address }}</p>
                </div>
                @if($customer->site_notes)
                <div class="col-span-2">
                    <p class="text-sm text-gray-600">Site Notes</p>
                    <p class="font-medium">{{ $customer->site_notes }}</p>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center">
            <h2 class="text-xl font-bold text-gray-900 mb-4">QR Code</h2>
            @if($customer->qr_code_path)
                <div class="w-40 h-40 border border-gray-200 rounded-lg p-2 mb-4">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(Storage::disk('qrcodes')->get($customer->qr_code_path)) }}" alt="QR Code" class="w-full h-full">
                </div>
            @else
                <div class="w-40 h-40 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mb-4">
                    <i class="fas fa-qrcode text-4xl"></i>
                </div>
            @endif
            <a href="{{ route('customers.qrcode', $customer) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <i class="fas fa-download"></i> Download QR
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Delivery History</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($deliveries as $delivery)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('deliveries.show', $delivery) }}" class="font-medium text-blue-600 hover:underline">{{ $delivery->delivery_code }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $delivery->delivery_date?->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $delivery->driver?->user?->full_name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @foreach($delivery->items as $item)<div>{{ $item->quantity }}x {{ $item->cylinder_type }}</div>@endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $delivery->status === 'completed' ? 'bg-green-100 text-green-800' : ($delivery->status === 'in_transit' ? 'bg-orange-100 text-orange-800' : ($delivery->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ str_replace('_', ' ', strtoupper($delivery->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No deliveries yet for this customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection