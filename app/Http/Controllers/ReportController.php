<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo   = $request->get('date_to', now()->toDateString());
        $days     = (int) $request->get('days', 30);

        $stats = $this->dashboardStats();

        $summary = $this->deliverySummary($dateFrom, $dateTo);

        $performance = $this->driverPerformance($dateFrom, $dateTo);

        $analytics = $this->dailyAnalytics($days);

        return view('reports.index', compact('stats', 'summary', 'performance', 'analytics', 'dateFrom', 'dateTo', 'days'));
    }

    private function dashboardStats(): array
    {
        return [
            'total_deliveries'   => Delivery::count(),
            'today_deliveries'   => Delivery::whereDate('delivery_date', today())->count(),
            'completed_today'    => Delivery::whereDate('delivery_date', today())->where('status', 'completed')->count(),
            'pending_deliveries' => Delivery::whereIn('status', ['assigned', 'in_transit'])->count(),
            'active_drivers'     => Driver::where('status', 'available')->count(),
            'total_customers'    => Customer::where('is_active', true)->count(),
        ];
    }

    private function deliverySummary(?string $from, ?string $to): array
    {
        $query = Delivery::query();

        if ($from) {
            $query->whereDate('delivery_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('delivery_date', '<=', $to);
        }

        $deliveries = $query->with('items')->get();

        $total = $deliveries->count();
        $completed = $deliveries->where('status', 'completed')->count();
        $inTransit = $deliveries->where('status', 'in_transit')->count();
        $assigned  = $deliveries->where('status', 'assigned')->count();
        $cancelled = $deliveries->where('status', 'cancelled')->count();

        $totalOrdered = $deliveries->sum(fn ($d) => $d->quantity_ordered);
        $totalDelivered = $deliveries->sum(fn ($d) => $d->status === 'completed' ? ($d->quantity_delivered ?? 0) : 0);

        return [
            'total_deliveries' => $total,
            'completed'        => $completed,
            'in_transit'       => $inTransit,
            'assigned'         => $assigned,
            'cancelled'        => $cancelled,
            'total_ordered'    => $totalOrdered,
            'total_delivered'  => $totalDelivered,
            'completion_rate'  => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    private function driverPerformance(?string $from, ?string $to): array
    {
        $drivers = Driver::with('user')->get();
        $performance = [];

        foreach ($drivers as $driver) {
            $query = $driver->deliveries();
            if ($from) {
                $query->whereDate('delivery_date', '>=', $from);
            }
            if ($to) {
                $query->whereDate('delivery_date', '<=', $to);
            }

            $deliveries = $query->get();
            $total = $deliveries->count();
            $completed = $deliveries->where('status', 'completed')->count();
            $rate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

            $withTimes = $deliveries->filter(fn ($d) => $d->status === 'completed' && $d->created_at && $d->completed_at);
            $avg = null;
            if ($withTimes->isNotEmpty()) {
                $avg = round($withTimes->avg(fn ($d) => $d->created_at->diffInMinutes($d->completed_at)), 2);
            }

            $performance[] = [
                'driver_id'     => $driver->id,
                'driver_name'   => $driver->user->full_name ?? 'Unknown',
                'driver_code'   => $driver->driver_code,
                'total_deliveries' => $total,
                'completed_deliveries' => $completed,
                'completion_rate' => $rate,
                'average_delivery_time_minutes' => $avg,
                'is_available'  => $driver->is_available,
            ];
        }

        usort($performance, fn ($a, $b) => $b['completion_rate'] <=> $a['completion_rate']);

        return $performance;
    }

    private function dailyAnalytics(int $days): array
    {
        $end = today();
        $start = today()->subDays($days - 1);

        $deliveries = Delivery::with('items')
            ->whereDate('delivery_date', '>=', $start)
            ->whereDate('delivery_date', '<=', $end)
            ->get();

        $result = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $dayDeliveries = $deliveries->filter(fn ($d) => $d->delivery_date?->isSameDay($cursor));
            $total = $dayDeliveries->count();
            $completed = $dayDeliveries->where('status', 'completed')->count();

            $result[] = [
                'date'                  => $cursor->toDateString(),
                'total_deliveries'      => $total,
                'completed_deliveries'  => $completed,
                'completion_rate'       => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'total_quantity'        => $dayDeliveries->sum(fn ($d) => $d->quantity_ordered),
            ];

            $cursor->addDay();
        }

        return $result;
    }

    public function exportDeliveries(Request $request)
    {
        $from = $request->get('date_from');
        $to   = $request->get('date_to');

        $query = Delivery::with(['customer', 'driver.user', 'items']);
        if ($from) {
            $query->whereDate('delivery_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('delivery_date', '<=', $to);
        }

        $deliveries = $query->orderBy('delivery_date')->get();

        $filename = 'deliveries_' . now()->toDateString() . '.csv';

        return response()->streamDownload(function () use ($deliveries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Delivery Code', 'Customer', 'Driver', 'Delivery Date', 'Status',
                'Items', 'Total Quantity', 'Delivered Quantity', 'Created At', 'Completed At',
            ]);

            foreach ($deliveries as $d) {
                $itemsStr = $d->items->map(fn ($i) => "{$i->quantity}x {$i->cylinder_type}")->join(', ');

                fputcsv($out, [
                    $d->delivery_code,
                    $d->customer?->name ?? 'N/A',
                    $d->driver?->user?->full_name ?? 'Unassigned',
                    $d->delivery_date?->toDateString() ?? '',
                    $d->status,
                    $itemsStr,
                    $d->quantity_ordered,
                    $d->quantity_delivered ?? 0,
                    $d->created_at?->toDateTimeString() ?? '',
                    $d->completed_at?->toDateTimeString() ?? '',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}