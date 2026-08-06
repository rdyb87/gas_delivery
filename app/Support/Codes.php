<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;

class Codes
{
    public static function customerCode(): string
    {
        do {
            $code = 'CUST' . strtoupper(bin2hex(random_bytes(4)));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }

    public static function driverCode(): string
    {
        do {
            $code = 'DRV' . strtoupper(bin2hex(random_bytes(3)));
        } while (Driver::where('driver_code', $code)->exists());

        return $code;
    }

    public static function deliveryCode(): string
    {
        do {
            $code = 'DEL' . now()->format('Ymd') . strtoupper(bin2hex(random_bytes(3)));
        } while (Delivery::where('delivery_code', $code)->exists());

        return $code;
    }
}