<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentType;
use App\Models\PaymentTypeField;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentTypeField::query()->delete();
        PaymentType::query()->delete();

        PaymentType::create([
            'id' => 1,
            'status' => 'active',
            'name' => 'Cash',
            'description' => 'Cash',
        ]);
    }
}
