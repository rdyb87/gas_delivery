@extends('layouts.app')

@section('title', $delivery ? 'Edit Delivery' : 'New Delivery Job')
@section('page_title', $delivery ? 'Edit Delivery' : 'New Delivery Job')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8">
    <form method="POST" action="{{ $delivery ? route('deliveries.update', $delivery) : route('deliveries.store') }}" class="space-y-4" id="deliveryForm">
        @csrf
        @if($delivery) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
            <select name="customer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Select Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id', $delivery?->customer_id) == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
            <select name="driver_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Select Driver</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected((string)old('driver_id', $delivery?->driver_id ?? request('driver_id')) === (string)$d->id)>
                        {{ $d->user?->full_name }} ({{ $d->lorry_plate ?? 'no lorry' }}) - {{ $d->status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date *</label>
                <input type="date" name="delivery_date" required value="{{ old('delivery_date', $delivery?->delivery_date?->toDateString() ?? now()->toDateString()) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Time</label>
                <input type="time" name="delivery_time" value="{{ old('delivery_time', $delivery?->delivery_time?->format('H:i')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Order Items *</label>
            <div id="itemsContainer" class="space-y-3">
                @php
                    $items = old('items', $delivery?->items->map(fn($i) => ['cylinder_type' => $i->cylinder_type, 'quantity' => $i->quantity])->all() ?? [['cylinder_type' => '14kg', 'quantity' => 1]]);
                @endphp
                @foreach($items as $index => $item)
                    <div class="flex gap-2 items-center item-row">
                        <div class="flex-1">
                            <select name="items[{{ $index }}][cylinder_type]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                @foreach(['14kg' => '14kg (Domestic)', '50kg' => '50kg (Commercial)', 'C200' => 'C200 (Bulk)'] as $val => $label)
                                    <option value="{{ $val }}" @selected(($item['cylinder_type'] ?? '') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <input type="number" name="items[{{ $index }}][quantity]" min="1" required value="{{ $item['quantity'] ?? 1 }}" placeholder="Qty" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <button type="button" class="text-red-500 hover:text-red-700 p-2 remove-item" title="Remove item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="addItem" class="mt-3 text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
            <textarea name="special_instructions" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('special_instructions', $delivery?->special_instructions) }}</textarea>
        </div>

        @if($delivery && auth()->user()->canManage())
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                @foreach(['assigned' => 'Assigned', 'in_transit' => 'In Transit', 'arrived' => 'Arrived', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                    <option value="{{ $val }}" @selected($delivery->status === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="pt-4 flex gap-3 justify-end">
            <a href="{{ $delivery ? route('deliveries.show', $delivery) : route('deliveries.index') }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <i class="fas fa-save"></i> {{ $delivery ? 'Update Job' : 'Create Job' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const container = document.getElementById('itemsContainer');
    let index = {{ count($items) }};

    document.getElementById('addItem').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'flex gap-2 items-center item-row';
        row.innerHTML = `
            <div class="flex-1">
                <select name="items[${index}][cylinder_type]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="14kg">14kg (Domestic)</option>
                    <option value="50kg">50kg (Commercial)</option>
                    <option value="C200">C200 (Bulk)</option>
                </select>
            </div>
            <div class="w-24">
                <input type="number" name="items[${index}][quantity]" min="1" required value="1" placeholder="Qty" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="button" class="text-red-500 hover:text-red-700 p-2 remove-item"><i class="fas fa-trash"></i></button>`;
        container.appendChild(row);
        index++;
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.remove-item')) {
            const rows = container.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
            }
        }
    });
</script>
@endpush