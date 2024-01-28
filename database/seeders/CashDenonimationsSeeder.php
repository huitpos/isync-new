<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashDenonimationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => '5 Cents',
                'amount' => '0.0500',
            ],
            [
                'name' => '25 Cents',
                'amount' => '0.2500',
            ],
            [
                'name' => '1',
                'amount' => '1.0000',
                // add more columns and data for additional rows
            ],
            [
                'name' => '5',
                'amount' => '5.0000',
            ],
            [
                'name' => '10',
                'amount' => '10.0000',
            ],
            [
                'name' => '20',
                'amount' => '20.0000',
            ],
            [
                'name' => '50',
                'amount' => '50.0000',
            ],
            [
                'name' => '100',
                'amount' => '100.0000',
            ],
            [
                'name' => '200',
                'amount' => '200.0000',
            ],
            [
                'name' => '500',
                'amount' => '500.0000',
            ],
            [
                'name' => '1000',
                'amount' => '1000.0000',
            ]
        ];

        // Insert multiple rows using the DB facade
        DB::table('cash_denominations')->insert($data);
    }
}
