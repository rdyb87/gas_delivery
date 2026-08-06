@extends('layouts.app')

@section('title', $customer ? 'Edit Customer' : 'Add Customer')
@section('page_title', $customer ? 'Edit Customer' : 'Add New Customer')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8">
    <form method="POST" action="{{ $customer ? route('customers.update', $customer) : route('customers.store') }}" class="space-y-4">
        @csrf
        @if($customer) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" name="name" required value="{{ old('name', $customer?->name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dealer Type *</label>
                <select name="dealer_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach(['retailer' => 'Retailer', 'industrial' => 'Industrial', 'wholesaler' => 'Wholesaler', 'restaurant' => 'Restaurant'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('dealer_type', $customer?->dealer_type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer?->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
            <input type="text" name="contact_person" value="{{ old('contact_person', $customer?->contact_person) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('address', $customer?->address) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude', $customer?->latitude) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude', $customer?->longitude) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Site Notes</label>
            <textarea name="site_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('site_notes', $customer?->site_notes) }}</textarea>
        </div>

        @if($customer)
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $customer->is_active)) class="rounded">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>
        @endif

        <div class="pt-4 flex gap-3 justify-end">
            <a href="{{ $customer ? route('customers.show', $customer) : route('customers.index') }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <i class="fas fa-save"></i> {{ $customer ? 'Update Customer' : 'Save Customer' }}
            </button>
        </div>
    </form>
</div>
@endsection