<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class TestController extends Controller
{
    public function mapData(Request $request)
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $number = 1;

            foreach ($company->products as $data) {
                $data->update([
                    'code' => $number
                ]);

                $number++;
            }
        }

        dd("here");
    }
}