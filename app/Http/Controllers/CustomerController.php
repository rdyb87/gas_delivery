<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\QrCodeService;
use App\Support\Codes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query();

        if ($search = $request->get('search')) {
            $customers->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $customers->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => null]);
    }

    public function store(Request $request, QrCodeService $qrService)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:128'],
            'dealer_type'    => ['required', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:128'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:120'],
            'address'        => ['nullable', 'string', 'max:256'],
            'latitude'       => ['nullable', 'numeric'],
            'longitude'      => ['nullable', 'numeric'],
            'site_notes'     => ['nullable', 'string'],
        ]);

        $data['customer_code'] = Codes::customerCode();

        $customer = Customer::create($data);

        $qrFile = $qrService->generateForCustomer($customer->customer_code, $customer->name);
        $customer->update(['qr_code_path' => $qrFile]);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $deliveries = $customer->deliveries()->with(['driver.user', 'items'])->orderByDesc('delivery_date')->get();

        return view('customers.show', compact('customer', 'deliveries'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:128'],
            'dealer_type'    => ['required', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:128'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:120'],
            'address'        => ['nullable', 'string', 'max:256'],
            'latitude'       => ['nullable', 'numeric'],
            'longitude'      => ['nullable', 'numeric'],
            'site_notes'     => ['nullable', 'string'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['latitude']  = $data['latitude'] !== null ? (float) $data['latitude'] : null;
        $data['longitude'] = $data['longitude'] !== null ? (float) $data['longitude'] : null;

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->qr_code_path) {
            Storage::disk('qrcodes')->delete($customer->qr_code_path);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function qrcode(Customer $customer)
    {
        if (! $customer->qr_code_path || ! Storage::disk('qrcodes')->exists($customer->qr_code_path)) {
            $qrService = app(QrCodeService::class);
            $file = $qrService->generateForCustomer($customer->customer_code, $customer->name);
            $customer->update(['qr_code_path' => $file]);
        }

        return Storage::disk('qrcodes')->download(
            $customer->qr_code_path,
            "{$customer->customer_code}_QR.svg",
            ['Content-Type' => 'image/svg+xml']
        );
    }
}