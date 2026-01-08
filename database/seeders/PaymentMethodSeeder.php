<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Bank Vietnam',
                'slug' => 'bank-vietnam',
                'type' => 'bank_transfer',
                'bank_name' => 'MB Bank',
                'account_number' => '8266566666',
                'account_holder' => 'CONG TY TNHH HM FULFILL',
                'qr_code' => 'images/qr-code.jpg',
                'instructions' => 'Please transfer the exact amount and include the transaction code in the transfer description.',
                'min_amount' => 10.00,
                'max_amount' => null,
                'currency' => 'USD',
                'exchange_rate' => 27000.00, // 1 USD = 27,000 VND
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Payoneer',
                'slug' => 'payoneer',
                'type' => 'payoneer',
                'bank_name' => null,
                'account_number' => 'admin@bluprinter.com',
                'account_holder' => 'Bluprinter Admin',
                'qr_code' => null,
                'instructions' => 'Transfer funds via Payoneer to email: admin@bluprinter.com. Please include the transaction code in the notes section.',
                'min_amount' => 10.00,
                'max_amount' => null,
                'currency' => 'USD',
                'exchange_rate' => 1.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'PingPong',
                'slug' => 'pingpong',
                'type' => 'pingpong',
                'bank_name' => null,
                'account_number' => 'admin@tron-studio.com',
                'account_holder' => 'Bluprinter Admin',
                'qr_code' => null,
                'instructions' => 'Transfer funds via PingPong to email: admin@tron-studio.com. Please include the transaction code in the notes section.',
                'min_amount' => 10.00,
                'max_amount' => null,
                'currency' => 'USD',
                'exchange_rate' => 1.00,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'LianLian',
                'slug' => 'lianlian',
                'type' => 'lianpay',
                'bank_name' => null,
                'account_number' => 'admin@bluprinter.com',
                'account_holder' => 'Bluprinter Admin',
                'qr_code' => null,
                'instructions' => 'Transfer funds via LianLian Pay to email: admin@bluprinter.com. Please include the transaction code in the notes section.',
                'min_amount' => 10.00,
                'max_amount' => null,
                'currency' => 'USD',
                'exchange_rate' => 1.00,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Worldfirst',
                'slug' => 'worldfirst',
                'type' => 'worldfirst',
                'bank_name' => null,
                'account_number' => 'WF-281025151906147020003690',
                'account_holder' => 'HM FULFILL COMPANY',
                'qr_code' => null,
                'instructions' => 'Transfer funds via World First with account number: WF-281025151906147020003690. Please include the transaction code in the notes section.',
                'min_amount' => 10.00,
                'max_amount' => null,
                'currency' => 'USD',
                'exchange_rate' => 1.00,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }
    }
}
