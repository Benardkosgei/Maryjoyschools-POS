<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentMethods = [
            ['id' => 1, 'name' => 'Cash'],
            ['id' => 2, 'name' => 'M-Pesa'],
            ['id' => 3, 'name' => 'Airtel Money'],
            ['id' => 4, 'name' => 'Bank Transfer'],
            ['id' => 5, 'name' => 'Cheque'],
            ['id' => 6, 'name' => 'Credit Card'],
            ['id' => 7, 'name' => 'Debit Card'],
            ['id' => 8, 'name' => 'School Account'],
        ];

        foreach ($paymentMethods as $method) {
            DB::table('payment_methods')->updateOrInsert(['id' => $method['id']], $method);
        }
    }
}
