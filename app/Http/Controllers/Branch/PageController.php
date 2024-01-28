<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        return view('branch.dashboard', compact('company', 'branch'));
    }
}
