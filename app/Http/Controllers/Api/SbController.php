<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\ApiRequestLog;

use App\Models\CutOff;
use App\Models\Sb\CutOff as SbCutOff;
use App\Models\EndOfDay;
use App\Models\Sb\EndOfDay as SbEndOfDay;

class SbController extends BaseController
{
    public function updateCutoff(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'percentage' => 'required',
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            $log = new ApiRequestLog();
            $log->type = 'updateCutoff';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $cutOff = CutOff::where([
                'branch_id' => $request->branch_id
            ])
            ->whereDate('treg', $request->date)
            ->first();

        if (!$cutOff) {
            return $this->sendError('No Cut Off for that day', $validator->errors(), 422);
        }

        $sbLatestCutoff = SbCutOff::where('branch_id', $request->branch_id)
            ->orderBy('treg', 'desc')
            ->first();

        if ($sbLatestCutoff && $sbLatestCutoff->id != $cutOff->id) {
            return $this->sendError('SB Cut Off is not the latest', $validator->errors(), 422);
        }

        $cutOffData = $cutOff->toArray();
        
        if ($sbLatestCutoff) {
            $cutOffData['beginning_amount'] = $sbLatestCutoff->ending_amount;
        }

        $deductibleFields = [
            'ending_amount',
            'gross_sales',
            'net_sales',
            'vatable_sales',
            'vat_exempt_sales',
            'vat_amount'
        ];

        foreach ($deductibleFields as $field) {
            $cutOffData[$field] = $cutOffData[$field] - ($cutOffData[$field] * ($request->percentage / 100));
        }

        //get sbcutoff $cutOff->id. create new if not existing
        $sbCutOff = SbCutOff::where('id', $cutOff->id)->first();
        if (!$sbCutOff) {
            $sbCutOff = new SbCutOff();
            $sbCutOff->id = $cutOff->id;
        }

        $sbCutOff->fill($cutOffData);

        $sbCutOff->save();

        return $this->sendResponse($sbCutOff, 'SB Cut Off updated successfully.');
    }

    public function updateEndOfDay(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'percentage' => 'required',
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            $log = new ApiRequestLog();
            $log->type = 'updateEndOfDay';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $endOfDay = EndOfDay::where([
                'branch_id' => $request->branch_id
            ])
            ->whereDate('treg', $request->date)
            ->first();

        if (!$endOfDay) {
            return $this->sendError('No End Of Day for that day', $validator->errors(), 422);
        }

        $sbLatestEndOfDay = SbEndOfDay::where('branch_id', $request->branch_id)
            ->orderBy('treg', 'desc')
            ->first();

        if ($sbLatestEndOfDay && $sbLatestEndOfDay->id != $endOfDay->id) {
            return $this->sendError('SB Cut Off is not the latest', $validator->errors(), 422);
        }

        $endOfDayData = $endOfDay->toArray();
        
        if ($sbLatestEndOfDay) {
            $endOfDayData['beginning_amount'] = $sbLatestEndOfDay->ending_amount;
        }

        $deductibleFields = [
            'ending_amount',
            'gross_sales',
            'net_sales',
            'vatable_sales',
            'vat_exempt_sales',
            'vat_amount'
        ];

        foreach ($deductibleFields as $field) {
            $endOfDayData[$field] = $endOfDayData[$field] - ($endOfDayData[$field] * ($request->percentage / 100));
        }

        $sbEndOfDay = SbEndOfDay::where('id', $endOfDay->id)->first();
        if (!$sbEndOfDay) {
            $sbEndOfDay = new SbEndOfDay();
            $sbEndOfDay->id = $endOfDay->id;
        }

        $sbEndOfDay->fill($endOfDayData);

        $sbEndOfDay->save();

        return $this->sendResponse($sbEndOfDay, 'SB End Of Day updated successfully.');
    }

    public function getCutoff(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            $log = new ApiRequestLog();
            $log->type = 'getCutoff';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $cutOff = SbCutOff::where([
            'branch_id' => $request->branch_id
        ])
        ->whereDate('treg', $request->date)
        ->first();

        if (!$cutOff) {
            return $this->sendError('No Cut Off for that day', [], 404);
        }

        return $this->sendResponse($cutOff, 'SB Cut Off retrieved successfully.');
    }

    public function getEndOfDay(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            $log = new ApiRequestLog();
            $log->type = 'getEndOfDay';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $endOfDay = SbEndOfDay::where([
            'branch_id' => $request->branch_id
        ])
        ->whereDate('treg', $request->date)
        ->first();

        if (!$endOfDay) {
            return $this->sendError('No End Of Day for that day', [], 404);
        }

        return $this->sendResponse($endOfDay, 'SB End Of Day retrieved successfully.');
    }
}