<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PosSqliteImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class PosSqliteImportController extends Controller
{
    // Rows per request; keeps each request well under the web server timeout.
    private const BATCH_SIZE = 1000;

    public function index()
    {
        return view('admin.pos-sqlite-import.index', [
            'directory' => $this->directory(),
            'files' => $this->availableFiles(),
            'tables' => array_keys(PosSqliteImporter::TABLES),
        ]);
    }

    public function verifyTable(Request $request, PosSqliteImporter $importer)
    {
        $validated = $this->validateRequest($request);

        return $this->runTable($importer, $validated, true);
    }

    public function importTable(Request $request, PosSqliteImporter $importer)
    {
        $validated = $this->validateRequest($request);

        if ($request->input('fingerprint') !== $this->fingerprint($this->path($validated))) {
            return response()->json(['message' => 'The file changed after it was verified. Please verify it again.'], 409);
        }

        return $this->runTable($importer, $validated, false);
    }

    private function runTable(PosSqliteImporter $importer, array $validated, bool $dryRun)
    {
        set_time_limit(0);
        ignore_user_abort(true);

        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        $path = $this->path($validated);

        try {
            $result = $importer->run($path, [$validated['table']], [
                'branch_id' => $validated['branch_id'] ?? null,
                'pos_machine_id' => $validated['pos_machine_id'] ?? null,
            ], $dryRun, (int) ($validated['after_row_id'] ?? 0), self::BATCH_SIZE);
        } catch (\Throwable $e) {
            return response()->json(['message' => ($dryRun ? 'Verification' : 'Import') . ' failed: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'table' => $validated['table'],
            'dry_run' => $dryRun,
            'summary' => $result['tables'][$validated['table']],
            'failures' => $result['failures'],
            'failure_count' => $result['failure_count'],
            'fingerprint' => $this->fingerprint($path),
        ]);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'file' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!in_array($value, array_column($this->availableFiles(), 'name'), true)) {
                    $fail('The selected file is not a SQLite database in the import folder.');
                }
            }],
            'table' => ['required', Rule::in(array_keys(PosSqliteImporter::TABLES))],
            'after_row_id' => 'nullable|integer|min:0',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'pos_machine_id' => 'nullable|integer|exists:pos_machines,id',
        ]);
    }

    private function path(array $validated): string
    {
        return $this->directory() . '/' . $validated['file'];
    }

    private function directory(): string
    {
        $directory = storage_path('app/pos-sqlite-import');
        File::ensureDirectoryExists($directory);

        return $directory;
    }

    private function availableFiles(): array
    {
        $files = [];

        foreach (File::files($this->directory()) as $file) {
            if (PosSqliteImporter::isSqliteFile($file->getPathname())) {
                $files[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        return $files;
    }

    private function fingerprint(string $path): string
    {
        clearstatcache(true, $path);

        return md5($path . '|' . filesize($path) . '|' . filemtime($path));
    }
}
