<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryLog;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner.index');
    }

    public function scan(Request $request)
    {
        $qrData = trim($request->get('qr_data', ''));

        $parts = explode('|', $qrData);

        if (count($parts) < 2 || $parts[0] !== 'GASDELIVERY') {
            return response()->json(['success' => false, 'message' => 'Invalid QR code format.'], 400);
        }

        $customerCode = $parts[1];

        $customer = Customer::where('customer_code', $customerCode)->first();

        if (! $customer) {
            return response()->json(['success' => false, 'message' => "Customer not found: {$customerCode}"], 404);
        }

        $deliveries = Delivery::with(['items', 'driver.user'])
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['assigned', 'in_transit'])
            ->orderByDesc('delivery_date')
            ->get();

        return response()->json([
            'success'    => true,
            'customer'   => $customer,
            'deliveries' => $deliveries->map(function ($d) {
                return [
                    'id'                  => $d->id,
                    'delivery_code'       => $d->delivery_code,
                    'delivery_date'       => $d->delivery_date ? $d->delivery_date->toDateString() : null,
                    'status'              => $d->status,
                    'items'               => $d->items,
                    'quantity_ordered'    => $d->quantity_ordered,
                    'special_instructions'=> $d->special_instructions,
                    'driver'              => $d->driver ? [
                        'id'          => $d->driver->id,
                        'name'        => $d->driver->user->full_name ?? null,
                        'driver_code' => $d->driver->driver_code,
                    ] : null,
                ];
            }),
        ]);
    }
}