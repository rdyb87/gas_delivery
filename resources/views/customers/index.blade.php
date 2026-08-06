@extends('layouts.app')

@section('title', 'Customers')
@section('page_title', 'Customers')

@section('header_actions')
    @if(auth()->user()->canManage())
    <a href="{{ route('customers.create') }}" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus"></i> Add Customer
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('customers.index') }}" class="bg-white rounded-xl shadow-lg p-6 border-b border-gray-200">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code or phone..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter"></i> Search
            </button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm font-medium text-gray-900">{{ $customer->customer_code }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm text-gray-900">{{ $customer->name }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ $customer->dealer_type ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $customer->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $customer->address }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('customers.qrcode', $customer) }}" class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Download QR">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                    <a href="{{ route('customers.show', $customer) }}" class="p-1 text-green-600 hover:bg-green-50 rounded" title="View/Edit">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="p-1 text-orange-600 hover:bg-orange-50 rounded" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 text-red-600 hover:bg-red-50 rounded" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No customers found. Add one above!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-gray-200">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection