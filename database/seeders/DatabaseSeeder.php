<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\User;
use App\Services\QrCodeService;
use App\Support\Codes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (User::exists()) {
            $this->command->info('Database already seeded. Skipping...');
            return;
        }

        $this->command->info('Seeding database...');

        $qrService = app(QrCodeService::class);

        $admin = User::create([
            'username'  => 'admin',
            'email'     => 'admin@gasdelivery.com',
            'full_name' => 'System Administrator',
            'phone'     => '+60123456789',
            'password'  => 'admin123',
            'role'      => 'admin',
        ]);

        $staff1 = User::create([
            'username' => 'staff1', 'email' => 'staff1@gasdelivery.com', 'full_name' => 'John Staff',
            'phone' => '+60123456780', 'password' => 'staff123', 'role' => 'staff',
        ]);
        $staff2 = User::create([
            'username' => 'staff2', 'email' => 'staff2@gasdelivery.com', 'full_name' => 'Mary Staff',
            'phone' => '+60123456781', 'password' => 'staff123', 'role' => 'staff',
        ]);
        $driver1User = User::create([
            'username' => 'driver1', 'email' => 'driver1@gasdelivery.com', 'full_name' => 'Ahmad Driver',
            'phone' => '+60123456782', 'password' => 'driver123', 'role' => 'driver',
        ]);
        $driver2User = User::create([
            'username' => 'driver2', 'email' => 'driver2@gasdelivery.com', 'full_name' => 'Kumar Driver',
            'phone' => '+60123456783', 'password' => 'driver123', 'role' => 'driver',
        ]);
        $driver3User = User::create([
            'username' => 'driver3', 'email' => 'driver3@gasdelivery.com', 'full_name' => 'Lee Driver',
            'phone' => '+60123456784', 'password' => 'driver123', 'role' => 'driver',
        ]);

        $driver1 = Driver::create([
            'user_id' => $driver1User->id, 'driver_code' => 'DRV001', 'license_number' => 'D12345678',
            'license_expiry' => '2026-12-31', 'lorry_plate' => 'ABC1234', 'lorry_capacity' => 100,
        ]);
        $driver2 = Driver::create([
            'user_id' => $driver2User->id, 'driver_code' => 'DRV002', 'license_number' => 'D87654321',
            'license_expiry' => '2027-06-30', 'lorry_plate' => 'XYZ5678', 'lorry_capacity' => 120,
        ]);
        $driver3 = Driver::create([
            'user_id' => $driver3User->id, 'driver_code' => 'DRV003', 'license_number' => 'D11223344',
            'license_expiry' => '2026-09-15', 'lorry_plate' => 'DEF9012', 'lorry_capacity' => 80,
        ]);

        $customersData = [
            ['name' => 'Restoran Nasi Kandar Ali', 'dealer_type' => 'retailer', 'contact_person' => 'Ali bin Ahmad', 'phone' => '+60123001001', 'email' => 'ali@restaurant.com', 'address' => 'No 123, Jalan Makan, Kuala Lumpur', 'latitude' => 3.1390, 'longitude' => 101.6869, 'site_notes' => 'Enter through back door. Kitchen on 1st floor.'],
            ['name' => 'Hotel Grand Plaza', 'dealer_type' => 'industrial', 'contact_person' => 'Robert Tan', 'phone' => '+60123002002', 'email' => 'procurement@grandplaza.com', 'address' => 'Jalan Sultan Ismail, Kuala Lumpur', 'latitude' => 3.1478, 'longitude' => 101.7010, 'site_notes' => 'Contact loading bay manager. Delivery between 8am-10am only.'],
            ['name' => 'Kedai Gas Borong Rahman', 'dealer_type' => 'wholesaler', 'contact_person' => 'Rahman Abdullah', 'phone' => '+60123003003', 'email' => 'rahman@gasborong.com', 'address' => 'Lot 45, Jalan Industri, Selangor', 'latitude' => 3.0738, 'longitude' => 101.5183, 'site_notes' => 'Large warehouse. Park at loading zone 2.'],
            ['name' => 'Mamak Stall Sulaiman', 'dealer_type' => 'retailer', 'contact_person' => 'Sulaiman', 'phone' => '+60123004004', 'email' => 'sulaiman@mamak.com', 'address' => 'Jalan Ampang, Kuala Lumpur', 'latitude' => 3.1570, 'longitude' => 101.7158, 'site_notes' => 'Small shop. Unload at side entrance.'],
            ['name' => 'ABC Manufacturing Sdn Bhd', 'dealer_type' => 'industrial', 'contact_person' => 'John Lim', 'phone' => '+60123005005', 'email' => 'john@abcmanufacturing.com', 'address' => 'Shah Alam Industrial Park', 'latitude' => 3.0733, 'longitude' => 101.5185, 'site_notes' => 'Report to security. Need safety gear.'],
        ];

        $customers = [];
        foreach ($customersData as $data) {
            $customer = Customer::create(array_merge($data, ['customer_code' => Codes::customerCode(), 'is_active' => true]));
            $qrFile = $qrService->generateForCustomer($customer->customer_code, $customer->name);
            $customer->update(['qr_code_path' => $qrFile]);
            $customers[] = $customer;
        }

        $today = now();

        $deliveriesData = [
            ['customer' => $customers[0], 'driver' => $driver1, 'date' => $today->copy(), 'time' => '09:00', 'item' => ['cylinder_type' => '14kg', 'quantity' => 10], 'status' => 'assigned', 'notes' => 'Call before arrival'],
            ['customer' => $customers[1], 'driver' => $driver1, 'date' => $today->copy(), 'time' => '11:00', 'item' => ['cylinder_type' => '50kg', 'quantity' => 20], 'status' => 'assigned', 'notes' => 'Deliver to loading bay'],
            ['customer' => $customers[2], 'driver' => $driver2, 'date' => $today->copy(), 'time' => '10:00', 'item' => ['cylinder_type' => '14kg', 'quantity' => 50], 'status' => 'in_transit', 'notes' => null],
            ['customer' => $customers[3], 'driver' => $driver3, 'date' => $today->copy()->subDay(), 'time' => '14:00', 'item' => ['cylinder_type' => '14kg', 'quantity' => 5], 'status' => 'completed', 'notes' => null, 'delivered' => 5, 'empty' => 5, 'completed_at' => now()->subDay()],
            ['customer' => $customers[4], 'driver' => $driver2, 'date' => $today->copy()->addDay(), 'time' => '08:00', 'item' => ['cylinder_type' => '50kg', 'quantity' => 30], 'status' => 'assigned', 'notes' => 'Safety briefing required'],
        ];

        foreach ($deliveriesData as $d) {
            $delivery = Delivery::create([
                'delivery_code'        => Codes::deliveryCode(),
                'customer_id'          => $d['customer']->id,
                'driver_id'            => $d['driver']->id,
                'delivery_date'        => $d['date']->toDateString(),
                'delivery_time'        => $d['time'],
                'special_instructions' => $d['notes'],
                'status'               => $d['status'],
                'quantity_delivered'   => $d['delivered'] ?? null,
                'empty_cylinders_collected' => $d['empty'] ?? 0,
                'completed_at'         => $d['completed_at'] ?? null,
            ]);

            $delivery->items()->create(['cylinder_type' => $d['item']['cylinder_type'], 'quantity' => $d['item']['quantity']]);

            if ($d['status'] === 'completed' && $d['completed_at'] ?? null) {
                \App\Models\DeliveryLog::create([
                    'delivery_id' => $delivery->id,
                    'action'      => 'delivery_completed',
                    'details'     => ['quantity_delivered' => $d['delivered'], 'completed_by' => $d['driver']->user->full_name],
                ]);
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin - username: admin, password: admin123');
        $this->command->info('Staff - username: staff1, password: staff123');
        $this->command->info('Driver - username: driver1, password: driver123');
    }
}