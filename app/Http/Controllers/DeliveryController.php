<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\DeliveryLog;
use App\Models\Driver;
use App\Support\Codes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $deliveries = Delivery::with(['customer', 'driver.user', 'items']);

        if ($status = $request->get('status')) {
            $deliveries->where('status', $status);
        }

        if ($request->user()->role === 'driver') {
            $driver = Driver::where('user_id', $request->user()->id)->first();
            if ($driver) {
                $deliveries->where('driver_id', $driver->id);
            }
        }

        $deliveries = $deliveries->orderByDesc('delivery_date')->paginate(20)->withQueryString();

        return view('deliveries.index', compact('deliveries'));
    }

    public function create()
    {
        $this->authorizeManage();

        return view('deliveries.form', [
            'delivery'  => null,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'drivers'   => Driver::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'customer_id'           => ['required', 'exists:customers,id'],
            'driver_id'             => ['nullable', 'exists:drivers,id'],
            'delivery_date'         => ['required', 'date'],
            'delivery_time'         => ['nullable', 'date_format:H:i'],
            'special_instructions'  => ['nullable', 'string'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.cylinder_type' => ['required', 'string', 'max:50'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
        ]);

        $delivery = Delivery::create([
            'delivery_code'         => Codes::deliveryCode(),
            'customer_id'           => $data['customer_id'],
            'driver_id'             => $data['driver_id'] ?? null,
            'delivery_date'         => $data['delivery_date'],
            'delivery_time'         => $data['delivery_time'] ?? null,
            'special_instructions'  => $data['special_instructions'] ?? null,
            'status'                => 'assigned',
        ]);

        foreach ($data['items'] as $item) {
            $delivery->items()->create([
                'cylinder_type' => $item['cylinder_type'],
                'quantity'      => $item['quantity'],
            ]);
        }

        return redirect()->route('deliveries.show', $delivery)->with('success', 'Delivery job created successfully.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['customer', 'driver.user', 'items', 'logs']);

        if (auth()->user()?->isDriver()) {
            $driver = Driver::where('user_id', auth()->id())->first();
            abort_if(! $driver || $delivery->driver_id !== $driver->id, 403);
        }

        return view('deliveries.show', compact('delivery'));
    }

    public function edit(Delivery $delivery)
    {
        $this->authorizeManage();

        return view('deliveries.form', [
            'delivery'  => $delivery->load(['items']),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'drivers'   => Driver::with('user')->get(),
        ]);
    }

    public function update(Request $request, Delivery $delivery)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'customer_id'           => ['required', 'exists:customers,id'],
            'driver_id'             => ['nullable', 'exists:drivers,id'],
            'delivery_date'         => ['required', 'date'],
            'delivery_time'         => ['nullable', 'date_format:H:i'],
            'special_instructions'  => ['nullable', 'string'],
            'status'                => ['nullable', 'in:assigned,in_transit,arrived,completed,cancelled'],
            'items'                 => ['nullable', 'array', 'min:1'],
            'items.*.cylinder_type' => ['required', 'string', 'max:50'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
        ]);

        $delivery->update([
            'customer_id'          => $data['customer_id'],
            'driver_id'            => $data['driver_id'] ?? null,
            'delivery_date'        => $data['delivery_date'],
            'delivery_time'        => $data['delivery_time'] ?? null,
            'special_instructions' => $data['special_instructions'] ?? null,
            'status'               => $data['status'] ?? $delivery->status,
        ]);

        if (isset($data['items'])) {
            $delivery->items()->delete();
            foreach ($data['items'] as $item) {
                $delivery->items()->create($item);
            }
        }

        return redirect()->route('deliveries.show', $delivery)->with('success', 'Delivery updated successfully.');
    }

    public function destroy(Delivery $delivery)
    {
        $this->authorizeAdmin();

        $delivery->delete();

        return redirect()->route('deliveries.index')->with('success', 'Delivery deleted successfully.');
    }

    public function complete(Request $request, Delivery $delivery)
    {
        $user = $request->user();

        if ($user?->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            abort_if(! $driver || $delivery->driver_id !== $driver->id, 403);
        }

        $delivery->update([
            'status'                       => 'completed',
            'completed_at'                 => now(),
            'quantity_delivered'           => $request->integer('quantity_delivered') ?: $delivery->quantity_ordered,
            'empty_cylinders_collected'    => $request->integer('empty_cylinders_collected'),
        ]);

        DeliveryLog::create([
            'delivery_id' => $delivery->id,
            'action'      => 'delivery_completed',
            'details'     => ['completed_by' => $user->full_name ?? $user->username],
        ]);

        return back()->with('success', 'Delivery completed successfully.');
    }

    private function authorizeManage(): void
    {
        abort_if(Auth::user()->role === 'driver', 403, 'Unauthorized.');
    }

    private function authorizeAdmin(): void
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Unauthorized.');
    }
}