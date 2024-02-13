<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class TestController extends Controller
{
    public function mapCompanyUsers(Request $request)
    {
        $companyUsers = User::where('client_id', '!=', null)->get();
        $branchUsers = User::where('branch_id', '!=', null)->get();

        foreach ($companyUsers as $user) {
            $user->company_id = $user->client->companies()->first()->id;
            $user->save();

            $user->assignRole('company_admin');
        }

        foreach ($branchUsers as $user) {
            $user->company_id = $user->branch->company->id;
            $user->save();

            $user->assignRole('branch_user');
        }
    }
}