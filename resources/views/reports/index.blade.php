@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page_title', 'Reports & Analytics')

@section('header_actions')
    <a href="{{ route('reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-download"></i> Export CSV
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('reports.index') }}" class="bg-white rounded-xl shadow-lg p-4">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">From:</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">To:</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Days:</label>
                <select name="days" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="7" @selected($days == 7)>Last 7 days</option>
                    <option value="30" @selected($days == 30)>Last 30 days</option>
                    <option value="90" @selected($days == 90)>Last 90 days</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply Filters</button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card title="Total Deliveries" :value="$stats['total_deliveries']" icon="fa-box" color="from-blue-500 to-blue-600" />
        <x-stat-card title="Today's Deliveries" :value="$stats['today_deliveries']" icon="fa-calendar" color="from-green-500 to-green-600" />
        <x-stat-card title="Completed Today" :value="$stats['completed_today']" icon="fa-trend-up" color="from-purple-500 to-purple-600" />
        <x-stat-card title="Pending Deliveries" :value="$stats['pending_deliveries']" icon="fa-clock" color="from-orange-500 to-orange-600" />
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Delivery Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div><p class="text-sm text-gray-500">Total Deliveries</p><p class="text-2xl font-bold">{{ $summary['total_deliveries'] }}</p></div>
            <div><p class="text-sm text-gray-500">Completed</p><p class="text-2xl font-bold text-green-600">{{ $summary['completed'] }}</p></div>
            <div><p class="text-sm text-gray-500">In Transit</p><p class="text-2xl font-bold text-blue-600">{{ $summary['in_transit'] }}</p></div>
            <div><p class="text-sm text-gray-500">Completion Rate</p><p class="text-2xl font-bold">{{ $summary['completion_rate'] }}%</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Performance Overview</h2>
            <div class="h-64"><canvas id="lineChart"></canvas></div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Delivery Trends</h2>
            <div class="h-64"><canvas id="barChart"></canvas></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Driver Performance</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Driver</th>
                        <th class="text-right py-3 px-4">Total Deliveries</th>
                        <th class="text-right py-3 px-4">Completed</th>
                        <th class="text-right py-3 px-4">Completion Rate</th>
                        <th class="text-right py-3 px-4">Avg. Time (min)</th>
                        <th class="text-center py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($performance as $driver)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="font-medium">{{ $driver['driver_name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $driver['driver_code'] }}</div>
                            </td>
                            <td class="text-right py-3 px-4">{{ $driver['total_deliveries'] }}</td>
                            <td class="text-right py-3 px-4">{{ $driver['completed_deliveries'] }}</td>
                            <td class="text-right py-3 px-4">
                                <span class="font-medium {{ $driver['completion_rate'] >= 90 ? 'text-green-600' : ($driver['completion_rate'] >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $driver['completion_rate'] }}%
                                </span>
                            </td>
                            <td class="text-right py-3 px-4">{{ $driver['average_delivery_time_minutes'] ?? 'N/A' }}</td>
                            <td class="text-center py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $driver['is_available'] ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $driver['is_available'] ? 'Available' : 'On Delivery' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-500">No driver data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Daily Analytics (last {{ $days }} days)</h2>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white">
                    <tr class="border-b">
                        <th class="text-left py-2">Date</th>
                        <th class="text-right py-2">Total</th>
                        <th class="text-right py-2">Completed</th>
                        <th class="text-right py-2">Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_reverse(array_slice($analytics, -10)) as $day)
                        <tr class="border-b">
                            <td class="py-2">{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                            <td class="text-right py-2">{{ $day['total_deliveries'] }}</td>
                            <td class="text-right py-2">{{ $day['completed_deliveries'] }}</td>
                            <td class="text-right py-2">{{ $day['completion_rate'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const analytics = @json($analytics);

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: analytics.map(a => a.date),
            datasets: [{
                label: 'Completion Rate (%)',
                data: analytics.map(a => a.completion_rate),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                fill: true,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 100 } },
        },
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: analytics.map(a => a.date),
            datasets: [
                { label: 'Total Deliveries', data: analytics.map(a => a.total_deliveries), backgroundColor: '#3b82f6' },
                { label: 'Completed', data: analytics.map(a => a.completed_deliveries), backgroundColor: '#10b981' },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
</script>
@endpush