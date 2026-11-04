<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppDetailController extends Controller
{
    public function edit()
    {
        return view('admin.app-details.edit', [
            'appDetail' => AppDetail::firstOrNew(['app' => 'pos']),
            'apkEnvironment' => $this->apkEnvironment(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'version_code' => 'required|integer|min:1',
            'version_name' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
            'apk_url' => 'required|url|max:255',
            'sha256' => ['required', 'string', 'regex:/^[A-Fa-f0-9]{64}$/'],
        ], [
            'version_name.regex' => 'The version name may only contain letters, numbers, dots, underscores, and hyphens.',
            'sha256.regex' => 'The SHA-256 must be a 64-character hexadecimal string.',
        ]);

        $validated['sha256'] = strtoupper($validated['sha256']);
        $validated['mandatory'] = $request->boolean('mandatory');

        AppDetail::updateOrCreate(['app' => 'pos'], $validated);

        return redirect()->route('admin.app-details.edit')->with('success', 'Data has been updated successfully!');
    }

    public function createUploadUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'version_name' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
        ], [
            'version_name.regex' => 'The version name may only contain letters, numbers, dots, underscores, and hyphens.',
        ]);

        $filename = sprintf(
            '%s-isyncpos-%s.apk',
            $this->apkEnvironment(),
            $validated['version_name']
        );
        $path = 'pos/'.$filename;
        $disk = Storage::disk('s3');
        $upload = $disk->temporaryUploadUrl($path, now()->addHour(), [
            'ACL' => 'public-read',
            'ContentType' => 'application/vnd.android.package-archive',
        ]);

        return response()->json([
            'upload_url' => $upload['url'],
            'headers' => $upload['headers'],
            'apk_url' => $disk->url($path),
            'filename' => $filename,
        ]);
    }

    private function apkEnvironment(): string
    {
        return app()->environment('production', 'prod') ? 'prod' : 'uat';
    }
}
