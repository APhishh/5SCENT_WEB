<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new address columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('address_line', 255)->nullable()->after('shipping_address');
            $table->string('district', 255)->nullable()->after('address_line');
            $table->string('city', 255)->nullable()->after('district');
            $table->string('province', 255)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('province');
        });

        // Migrate data from shipping_address to the new columns
        $orders = DB::table('orders')->whereNotNull('shipping_address')->get();

        foreach ($orders as $order) {
            $shippingAddress = $order->shipping_address;
            $addressData = $this->parseShippingAddress($shippingAddress);

            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'address_line' => $addressData['address_line'],
                    'district' => $addressData['district'],
                    'city' => $addressData['city'],
                    'province' => $addressData['province'],
                    'postal_code' => $addressData['postal_code'],
                ]);
        }

        // Drop the old shipping_address column
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_address');
        });
    }

    public function down(): void
    {
        // Recreate shipping_address column
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_address', 255)->nullable()->after('status');
        });

        // Rebuild shipping_address from the 5 columns
        $orders = DB::table('orders')->get();

        foreach ($orders as $order) {
            $shippingAddress = $this->buildShippingAddress(
                $order->address_line,
                $order->district,
                $order->city,
                $order->province,
                $order->postal_code
            );

            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update(['shipping_address' => $shippingAddress]);
        }

        // Drop the new address columns
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['address_line', 'district', 'city', 'province', 'postal_code']);
        });
    }

    /**
     * Parse shipping address string into components
     * Format: "address_line, district, city, province postal_code"
     * Example: "Jl. Anta, Antapanoy, Bandung, Jawa Barat 12345"
     */
    private function parseShippingAddress(string $address): array
    {
        $result = [
            'address_line' => null,
            'district' => null,
            'city' => null,
            'province' => null,
            'postal_code' => null,
        ];

        if (empty(trim($address))) {
            return $result;
        }

        // Split by comma
        $parts = array_map('trim', explode(',', $address));

        // Assign first 3 parts (address_line, district, city)
        if (isset($parts[0])) {
            $result['address_line'] = $parts[0];
        }
        if (isset($parts[1])) {
            $result['district'] = $parts[1];
        }
        if (isset($parts[2])) {
            $result['city'] = $parts[2];
        }

        // Last part contains "province postal_code", split by spaces
        if (isset($parts[3])) {
            $lastPart = trim($parts[3]);
            $lastPartTokens = explode(' ', $lastPart);

            if (count($lastPartTokens) >= 1) {
                // Last token is postal code if it's all digits
                $potentialPostalCode = end($lastPartTokens);
                
                if (preg_match('/^\d+$/', $potentialPostalCode)) {
                    $result['postal_code'] = $potentialPostalCode;
                    // Remove postal code from tokens
                    array_pop($lastPartTokens);
                    $result['province'] = implode(' ', $lastPartTokens);
                } else {
                    // No valid postal code found
                    $result['province'] = $lastPart;
                }
            }
        }

        return $result;
    }

    /**
     * Build shipping address string from components
     */
    private function buildShippingAddress(
        ?string $addressLine,
        ?string $district,
        ?string $city,
        ?string $province,
        ?string $postalCode
    ): ?string {
        $parts = [];

        if (!empty($addressLine)) {
            $parts[] = $addressLine;
        }
        if (!empty($district)) {
            $parts[] = $district;
        }
        if (!empty($city)) {
            $parts[] = $city;
        }

        // Combine province and postal code
        $provincePart = '';
        if (!empty($province)) {
            $provincePart = $province;
        }
        if (!empty($postalCode)) {
            $provincePart .= (!empty($provincePart) ? ' ' : '') . $postalCode;
        }

        if (!empty($provincePart)) {
            $parts[] = $provincePart;
        }

        return !empty($parts) ? implode(', ', $parts) : null;
    }
};
