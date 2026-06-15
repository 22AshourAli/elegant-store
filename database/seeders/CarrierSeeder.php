<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'Bosta',
                'name_ar' => 'بوستة',
                'code' => 'bosta',
                'api_key' => env('BOSTA_API_KEY', ''),
                'base_url' => env('BOSTA_BASE_URL', 'https://api.bosta.co/v2/'),
                'is_active' => true,
            ],
            [
                'name' => 'Aramex',
                'name_ar' => 'أرامكس',
                'code' => 'aramex',
                'api_key' => env('ARAMEX_API_KEY', ''),
                'base_url' => 'https://ws.aramex.net',
                'is_active' => false,
            ],
        ];

        foreach ($carriers as $carrier) {
            Carrier::firstOrCreate(['code' => $carrier['code']], $carrier);
        }
    }
}
