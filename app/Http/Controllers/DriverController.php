<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Driver;
use App\Models\User;
use App\Support\Codes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::with(['user', 'deliveries']);

        if ($search = $request->get('search')) {
            $drivers->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('full_name', 'like', "%{$search}%");
                })
                    ->orWhere('driver_code', 'like', "%{$search}%")
                    ->orWhere('lorry_plate', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $drivers->where('status', $status);
        }

        $drivers = $drivers->orderByDesc('created_at')->get();

        return view('drivers.index', compact('drivers'));
    }

    public function show(Driver $driver)
    {
        $driver->load(['user', 'deliveries.customer', 'deliveries.items']);

        $statistics = [
            'total_deliveries'     => $driver->deliveries->count(),
            'completed_deliveries' => $driver->deliveries->where('status', 'completed')->count(),
            'today_deliveries'     => $driver->deliveries->where('delivery_date', today())->count(),
        ];

        return view('drivers.show', compact('driver', 'statistics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username'       => ['required', 'string', 'max:64', 'unique:users,username'],
            'email'          => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'full_name'      => ['required', 'string', 'max:128'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'password'       => ['nullable', 'string', 'min:6'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'license_expiry' => ['nullable', 'date'],
            'lorry_plate'    => ['nullable', 'string', 'max:20'],
            'lorry_capacity' => ['nullable', 'integer'],
        ]);

        $user = User::create([
            'username'  => $data['username'],
            'email'     => $data['email'] ?? ($data['username'] . '@gasdelivery.local'),
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
            'password'  => $data['password'] ?? 'driver123',
            'role'      => 'driver',
        ]);

        $driver = Driver::create([
            'user_id'         => $user->id,
            'driver_code'     => Codes::driverCode(),
            'license_number'  => $data['license_number'] ?? null,
            'license_expiry'  => $data['license_expiry'] ?? null,
            'lorry_plate'     => $data['lorry_plate'] ?? null,
            'lorry_capacity'  => $data['lorry_capacity'] ?? 100,
            'status'          => 'available',
        ]);

        return redirect()->route('drivers.show', $driver)->with('success', 'Driver created successfully.');
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate([
            'full_name'      => ['nullable', 'string', 'max:128'],
            'email'          => ['nullable', 'email', 'max:120'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'password'       => ['nullable', 'string', 'min:6'],
            'is_active'      => ['nullable', 'boolean'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'license_expiry' => ['nullable', 'date'],
            'lorry_plate'    => ['nullable', 'string', 'max:20'],
            'lorry_capacity' => ['nullable', 'integer'],
            'status'         => ['nullable', 'in:available,on_duty,off_duty'],
        ]);

        $user = $driver->user;
        $user->update([
            'full_name' => $data['full_name'] ?? $user->full_name,
            'email'     => $data['email'] ?? $user->email,
            'phone'     => $data['phone'] ?? $user->phone,
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        $driver->update([
            'license_number' => $data['license_number'] ?? $driver->license_number,
            'license_expiry' => $data['license_expiry'] ?? $driver->license_expiry,
            'lorry_plate'    => $data['lorry_plate'] ?? $driver->lorry_plate,
            'lorry_capacity' => $data['lorry_capacity'] ?? $driver->lorry_capacity,
            'status'         => $data['status'] ?? $driver->status,
        ]);

        return redirect()->route('drivers.show', $driver)->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $active = Delivery::where('driver_id', $driver->id)->whereIn('status', ['assigned', 'in_transit'])->count();

        if ($active > 0) {
            return back()->withErrors(['delete' => "Cannot delete driver with {$active} active deliveries."]);
        }

        $driver->user->delete(); // cascades to driver

        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
    }

    public function updateAvailability(Request $request, Driver $driver)
    {
        $data = $request->validate([
            'status' => ['required', 'in:available,on_duty,off_duty'],
        ]);

        $driver->update(['status' => $data['status']]);

        return back()->with('success', 'Driver availability updated.');
    }
}