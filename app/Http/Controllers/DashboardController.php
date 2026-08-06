<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_deliveries' => Delivery::count(),
            'today_deliveries' => Delivery::whereDate('delivery_date', today())->count(),
            'completed_today'  => Delivery::whereDate('delivery_date', today())->where('status', 'completed')->count(),
            'pending_deliveries' => Delivery::whereIn('status', ['assigned', 'in_transit'])->count(),
            'active_drivers'   => Driver::where('status', 'available')->count(),
            'total_customers'  => Customer::where('is_active', true)->count(),
        ];

        $recentDeliveries = Delivery::with(['customer', 'driver.user'])
            ->orderBy('delivery_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('dashboard', compact('stats', 'recentDeliveries'));
    }
}