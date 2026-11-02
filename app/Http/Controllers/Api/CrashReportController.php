<?php

namespace App\Http\Controllers\Api;

use App\Models\CrashReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrashReportController extends BaseController
{
    public function store(Request $request)
    {
        $data = $this->normalize($request->all());

        $rules = [
            'device_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'machine_number' => 'required',
            'serial_number' => 'required',
            'version_code' => 'required',
            'version_name' => 'required',
            'device_model' => 'required',
            'android_version' => 'required',
            'occurred_at' => 'required|date',
            'exception' => 'required',
            'stack_trace' => 'required',
        ];

        $failedRequests = [];
        $toInsert = [];

        foreach ($data as $idx => $report) {
            $validator = validator($report, $rules);
            if ($validator->fails()) {
                $failedRequests[$idx] = $report;
                continue;
            }

            $toInsert[] = [
                'device_id' => $report['device_id'],
                'pos_machine_id' => $report['pos_machine_id'],
                'branch_id' => $report['branch_id'],
                'company_id' => $report['company_id'],
                'machine_number' => $report['machine_number'],
                'serial_number' => $report['serial_number'],
                'version_code' => $report['version_code'],
                'version_name' => $report['version_name'],
                'device_model' => $report['device_model'],
                'android_version' => $report['android_version'],
                'occurred_at' => $report['occurred_at'],
                'exception' => $report['exception'],
                'stack_trace' => $report['stack_trace'],
            ];
        }

        try {
            DB::connection('transactional_db')->transaction(function () use ($toInsert) {
                if (empty($toInsert)) {
                    return;
                }

                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }

                CrashReport::insert($toInsert);
            });
        } catch (\Exception $e) {
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'saved' => count($toInsert),
            'failed_requests' => array_values($failedRequests),
        ], 'Crash reports processed successfully.');
    }

    private function normalize(array $requestData): array
    {
        if (isset($requestData['data'])) {
            if (is_array($requestData['data']) && !$this->isAssoc($requestData['data'])) {
                return $requestData['data'];
            }

            return [$requestData['data']];
        }

        if ($this->isAssoc($requestData)) {
            return [$requestData];
        }

        return $requestData;
    }

    private function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
