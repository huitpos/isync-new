<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard(Request $request)
    {
        $company = $request->attributes->get('company');
        return view('company.dashboard', compact('company'));
    }
}
