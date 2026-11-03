<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppDetail;
use Illuminate\Http\Request;

class AppDetailController extends Controller
{
    public function edit()
    {
        return view('admin.app-details.edit', [
            'appDetail' => AppDetail::firstOrNew(['app' => 'pos']),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'version_code' => 'required|integer|min:1',
            'version_name' => 'required|string|max:50',
            'apk_url' => 'required|url|max:255',
            'sha256' => ['required', 'string', 'regex:/^[A-Fa-f0-9]{64}$/'],
        ], [
            'sha256.regex' => 'The SHA-256 must be a 64-character hexadecimal string.',
        ]);

        $validated['sha256'] = strtoupper($validated['sha256']);
        $validated['mandatory'] = $request->boolean('mandatory');

        AppDetail::updateOrCreate(['app' => 'pos'], $validated);

        return redirect()->route('admin.app-details.edit')->with('success', 'Data has been updated successfully!');
    }
}
