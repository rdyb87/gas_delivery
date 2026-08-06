<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryLog;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    private function resolveDriver(User $user): Driver
    {
        if ($user->role !== 'driver') {
            abort(403, 'Unauthorized');
        }

        $driver = Driver::where('user_id', $user->id)->first();
        abort_if(! $driver, 404, 'Driver profile not found');

        return $driver;
    }

    public function jobs(Request $request)
    {
        $driver = $this->resolveDriver($request->user());

        $query = Delivery::with('customer', 'items')->where('driver_id', $driver->id);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($dateFilter = $request->get('date')) {
            $query->whereDate('delivery_date', $dateFilter);
        } else {
            $query->whereDate('delivery_date', '>=', today());
        }

        $jobs = $query->orderBy('delivery_date')->orderBy('delivery_time')->get();

        $jobs = $jobs->map(function ($d) {
            return [
                'id'                  => $d->id,
                'delivery_code'       => $d->delivery_code,
                'customer'            => $d->customer ? [
                    'id'            => $d->customer->id,
                    'name'          => $d->customer->name,
                    'customer_code' => $d->customer->customer_code,
                    'address'       => $d->customer->address,
                    'phone'         => $d->customer->phone,
                    'latitude'      => $d->customer->latitude,
                    'longitude'     => $d->customer->longitude,
                ] : null,
                'delivery_date'       => $d->delivery_date?->toDateString(),
                'delivery_time'       => $d->delivery_time?->format('H:i'),
                'items'               => $d->items,
                'quantity_ordered'    => $d->quantity_ordered,
                'status'              => $d->status,
                'special_instructions'=> $d->special_instructions,
                'assigned_at'         => $d->created_at?->toIso8601String(),
            ];
        });

        return response()->json(['success' => true, 'jobs' => $jobs, 'total' => $jobs->count()]);
    }

    public function deliveryDetails(Request $request, Delivery $delivery)
    {
        $this->resolveDriver($request->user());

        abort_if($delivery->driver_id !== $request->user()->driverProfile?->id, 403, 'Unauthorized');

        $delivery->load(['customer', 'items']);

        return response()->json([
            'success'   => true,
            'delivery'  => [
                'id'                     => $delivery->id,
                'delivery_code'          => $delivery->delivery_code,
                'customer'               => [
                    'name'       => $delivery->customer?->name,
                    'address'    => $delivery->customer?->address,
                    'phone'      => $delivery->customer?->phone,
                    'site_notes' => $delivery->customer?->site_notes,
                ],
                'delivery_date'          => $delivery->delivery_date?->toDateString(),
                'items'                  => $delivery->items,
                'quantity_ordered'       => $delivery->quantity_ordered,
                'quantity_delivered'     => $delivery->quantity_delivered,
                'empty_cylinders_collected' => $delivery->empty_cylinders_collected,
                'status'                 => $delivery->status,
                'special_instructions'   => $delivery->special_instructions,
                'driver_notes'           => $delivery->driver_notes,
            ],
        ]);
    }

    public function scanQr(Request $request)
    {
        $driver = $this->resolveDriver($request->user());

        $data = $request->validate([
            'qr_data'   => ['required', 'string'],
            'latitude'  => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $parts = explode('|', $data['qr_data']);

        if (count($parts) < 2 || $parts[0] !== 'GASDELIVERY') {
            return response()->json(['success' => false, 'message' => 'Invalid QR code'], 400);
        }

        $customer = Customer::where('customer_code', $parts[1])->first();

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $delivery = Delivery::where('customer_id', $customer->id)
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['assigned', 'in_transit'])
            ->orderBy('delivery_date')
            ->first();

        if (! $delivery) {
            return response()->json(['success' => false, 'message' => 'No active delivery found for this customer'], 404);
        }

        $delivery->update([
            'status'             => 'arrived',
            'arrived_at'         => now(),
            'arrival_latitude'   => $data['latitude'] ?? null,
            'arrival_longitude'  => $data['longitude'] ?? null,
        ]);

        DeliveryLog::create([
            'delivery_id' => $delivery->id,
            'action'      => 'qr_scanned',
            'details'     => ['customer_code' => $customer->customer_code, 'scanned_by' => $request->user()->full_name],
            'latitude'    => $data['latitude'] ?? null,
            'longitude'   => $data['longitude'] ?? null,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Arrival confirmed',
            'delivery' => [
                'id'               => $delivery->id,
                'delivery_code'    => $delivery->delivery_code,
                'customer_name'    => $customer->name,
                'items'            => $delivery->items,
                'quantity_ordered' => $delivery->quantity_ordered,
                'status'           => $delivery->status,
            ],
        ]);
    }

    public function complete(Request $request, Delivery $delivery)
    {
        $driver = $this->resolveDriver($request->user());

        abort_if($delivery->driver_id !== $driver->id, 403, 'Unauthorized');

        $data = $request->validate([
            'quantity_delivered'        => ['nullable', 'integer'],
            'empty_cylinders_collected' => ['nullable', 'integer'],
            'driver_notes'              => ['nullable', 'string'],
        ]);

        $photos = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store('deliveries', 'uploads');
            }
        }

        $delivery->update([
            'quantity_delivered'        => $data['quantity_delivered'] ?? $delivery->quantity_ordered,
            'empty_cylinders_collected' => $data['empty_cylinders_collected'] ?? 0,
            'driver_notes'              => $data['driver_notes'] ?? null,
            'delivery_photos'           => array_merge($delivery->delivery_photos ?? [], $photos),
            'status'                    => 'completed',
            'completed_at'              => now(),
        ]);

        DeliveryLog::create([
            'delivery_id' => $delivery->id,
            'action'      => 'delivery_completed',
            'details'     => [
                'quantity_delivered' => $delivery->quantity_delivered,
                'empty_collected'    => $delivery->empty_cylinders_collected,
                'completed_by'       => $request->user()->full_name,
            ],
        ]);

        return response()->json(['success' => true, 'message' => 'Delivery completed successfully']);
    }
}