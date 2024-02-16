<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;

class TestController extends Controller
{
    public function mapMachineNumber(Request $request)
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $machineNumber = 1;

            foreach ($branch->machines as $machine) {
                $machine->update([
                    'machine_number' => $machineNumber
                ]);

                $machineNumber++;
            }
        }

        dd("here");
    }
}