<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\ApiRequestLog;

use App\Models\CutOff;
use App\Models\CutOffDepartment;
use App\Models\CutOffPayment;
use App\Models\CutOffDiscount;

use App\Models\Sb\CutOff as SbCutOff;
use App\Models\Sb\CutOffDepartment as SbCutOffDepartment;
use App\Models\Sb\CutOffPayment as SbCutOffPayment;
use App\Models\Sb\CutOffDiscount as SbCutOffDiscount;

use App\Models\EndOfDay;
use App\Models\EndOfDayDepartment;
use App\Models\EndOfDayDiscount;
use App\Models\EndOfDayPayment;

use App\Models\Sb\EndOfDay as SbEndOfDay;
use App\Models\Sb\EndOfDayDepartment as SbEndOfDayDepartment;
use App\Models\Sb\EndOfDayDiscount as SbEndOfDayDiscount;
use App\Models\Sb\EndOfDayPayment as SbEndOfDayPayment;

class SbController extends BaseController
{

    public function updateReadings(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'percentage' => 'required',
            'machine_id' => 'required',
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            $log = new ApiRequestLog();
            $log->type = 'updateReadings';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        //end of day
        $endOfDay = EndOfDay::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->machine_id
            ])
            ->whereDate('treg', $request->date)
            ->first();

        if (!$endOfDay) {
            return $this->sendError('No End Of Day for that day', $validator->errors(), 422);
        }

        //check if sb end of day already exists
        $SbEndOfDay = SbEndOfDay::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->machine_id
            ])
            ->whereDate('treg', $request->date)
            ->first();

        if ($SbEndOfDay) {
            return $this->sendError('SB End Of Day already exists', $validator->errors(), 422);
        }

        $sbLatestEndOfDay = SbEndOfDay::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->machine_id,
            ])
            ->orderBy('treg', 'desc')
            ->first();

        if ($sbLatestEndOfDay) {
            $nextEndOfDay = EndOfDay::where([
                    'branch_id' => $request->branch_id,
                    'pos_machine_id' => $request->machine_id,
                ])
                ->where('treg', '>', $sbLatestEndOfDay->treg)
                ->orderBy('treg', 'asc')
                ->first();

            if ($nextEndOfDay && $nextEndOfDay->id != $endOfDay->id) {
                $nextEndOfDay->treg = date('Y-m-d', strtotime($nextEndOfDay->treg));

                return $this->sendError("SB date must be $nextEndOfDay->treg", $validator->errors(), 422);
            }
        }

        $endOfDayData = $endOfDay->toArray();
        
        if ($sbLatestEndOfDay) {
            $endOfDayData['beginning_amount'] = $sbLatestEndOfDay->ending_amount;
            $endOfDayData['beginning_counter_amount'] = $sbLatestEndOfDay->ending_counter_amount;
        }

        $deductibleFields = [
            'gross_sales',
            'net_sales',
            'vatable_sales',
            'vat_exempt_sales',
            'vat_amount'
        ];

        foreach ($deductibleFields as $field) {
            $endOfDayData[$field] = $endOfDayData[$field] - ($endOfDayData[$field] * ($request->percentage / 100));
        }

        $endOfDayData['ending_amount'] = $endOfDayData['beginning_amount'] + $endOfDayData['net_sales'];
        $endOfDayData['ending_counter_amount'] = $endOfDayData['beginning_counter_amount'] + $endOfDayData['gross_sales'];

        $sbEndOfDay = SbEndOfDay::updateOrCreate(['id' => $endOfDay->id], $endOfDayData);

        $sbEndOfDayResponse = $sbEndOfDay->toArray();
        $sbEndOfDayResponse['end_of_day_departments'] = [];
        $sbEndOfDayResponse['end_of_day_discounts'] = [];
        $sbEndOfDayResponse['end_of_day_payments'] = [];

        //end_of_day_departments
        $endOfDayDepartmentsDeductibleFields = [
            'amount'
        ];

        $endOfDayDepartments = EndOfDayDepartment::where([
            'end_of_day_id' => $endOfDay->end_of_day_id,
            'branch_id' => $endOfDay->branch_id,
            'pos_machine_id' => $request->machine_id
        ])->get();

        foreach ($endOfDayDepartments as $endOfDayDepartment) {
            $endOfDayDepartmentData = $endOfDayDepartment->toArray();

            foreach ($endOfDayDepartmentsDeductibleFields as $field) {
                $endOfDayDepartmentData[$field] = $endOfDayDepartmentData[$field] - ($endOfDayDepartmentData[$field] * ($request->percentage / 100));
            }

            $sbEndOfDayDepartment =  SbEndOfDayDepartment::updateOrCreate(['id' => $endOfDayDepartment->id], $endOfDayDepartmentData);

            $sbEndOfDayResponse['end_of_day_departments'][] = $sbEndOfDayDepartment;
        }

        //end_of_day_discounts
        $endOfDayDiscountsDeductibleFields = [
            'amount'
        ];

        $endOfDayDiscounts = EndOfDayDiscount::where([
            'end_of_day_id' => $endOfDay->end_of_day_id,
            'branch_id' => $endOfDay->branch_id,
            'pos_machine_id' => $request->machine_id
        ])->get();

        foreach ($endOfDayDiscounts as $endOfDayDiscount) {
            $endOfDayDiscountData = $endOfDayDiscount->toArray();

            foreach ($endOfDayDiscountsDeductibleFields as $field) {
                $endOfDayDiscountData[$field] = $endOfDayDiscountData[$field] - ($endOfDayDiscountData[$field] * ($request->percentage / 100));
            }

            $sbEndOfDayDiscount = SbEndOfDayDiscount::updateOrCreate(['id' => $endOfDayDiscount->id], $endOfDayDiscountData);

            $sbEndOfDayResponse['end_of_day_discounts'][] = $sbEndOfDayDiscount;
        }

        //end_of_day_payments
        $endOfDayPaymentsDeductibleFields = [
            'amount'
        ];

        $endOfDayPayments = EndOfDayPayment::where([
            'end_of_day_id' => $endOfDay->end_of_day_id,
            'branch_id' => $endOfDay->branch_id,
            'pos_machine_id' => $request->machine_id
        ])->get();

        foreach ($endOfDayPayments as $endOfDayPayment) {
            $endOfDayPaymentData = $endOfDayPayment->toArray();

            foreach ($endOfDayPaymentsDeductibleFields as $field) {
                $endOfDayPaymentData[$field] = $endOfDayPaymentData[$field] - ($endOfDayPaymentData[$field] * ($request->percentage / 100));
            }

            $sbEndOfDayPayment = SbEndOfDayPayment::updateOrCreate(['id' => $endOfDayPayment->id], $endOfDayPaymentData);

            $sbEndOfDayResponse['end_of_day_payments'][] = $sbEndOfDayPayment;
        }

        $sbLatestCutoff = SbCutOff::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->machine_id
            ])
            ->whereNot('end_of_day_id', $endOfDay->end_of_day_id)
            ->orderBy('treg', 'desc')
            ->first();

        $cutOffBeginningAmount = $endOfDay->beginning_amount;
        if ($sbLatestCutoff) {
            $cutOffBeginningAmount = $sbLatestCutoff->ending_amount;
        }

        $cutOffs = CutOff::where([
                'branch_id' => $request->branch_id,
                'end_of_day_id' => $endOfDay->end_of_day_id,
                'pos_machine_id' => $request->machine_id
            ])
            ->get();

        foreach ($cutOffs as $cutOff) {
            $cutOffData = $cutOff->toArray();

            $cutOffData['beginning_amount'] = $cutOffBeginningAmount;

            $cutOffDeductibleFields = [
                'gross_sales',
                'net_sales',
                'vatable_sales',
                'vat_exempt_sales',
                'vat_amount'
            ];
    
            foreach ($cutOffDeductibleFields as $field) {
                $cutOffData[$field] = $cutOffData[$field] - ($cutOffData[$field] * ($request->percentage / 100));
            }

            $cutOffData['ending_amount'] = $cutOffBeginningAmount = $cutOffData['beginning_amount'] + $cutOffData['net_sales'];
    
            $sbCutOff = SbCutOff::updateOrCreate(['id' => $cutOff->id], $cutOffData);
    
            //cut_off_departments
            $cutOffDepartmentsDeductibleFields = [
                'amount'
            ];
    
            $cutOffDepartments = CutOffDepartment::where([
                'cut_off_id' => $cutOff->cut_off_id,
                'branch_id' => $cutOff->branch_id,
                'pos_machine_id' => $request->machine_id
            ])->get();
            
            foreach ($cutOffDepartments as $cutOffDepartment) {
                $cutOffDepartmentData = $cutOffDepartment->toArray();
    
                foreach ($cutOffDepartmentsDeductibleFields as $field) {
                    $cutOffDepartmentData[$field] = $cutOffDepartmentData[$field] - ($cutOffDepartmentData[$field] * ($request->percentage / 100));
                }
    
                SbCutOffDepartment::updateOrCreate(['id' => $cutOffDepartment->id], $cutOffDepartmentData);
            }
    
            //cut_off_payments
            $cutOffPaymentsDeductibleFields = [
                'amount'
            ];
    
            $cutOffPayments = CutOffPayment::where([
                'cut_off_id' => $cutOff->cut_off_id,
                'branch_id' => $cutOff->branch_id,
                'pos_machine_id' => $request->machine_id
            ])->get();
    
            foreach ($cutOffPayments as $cutOffPayment) {
                $cutOffPaymentData = $cutOffPayment->toArray();
    
                foreach ($cutOffPaymentsDeductibleFields as $field) {
                    $cutOffPaymentData[$field] = $cutOffPaymentData[$field] - ($cutOffPaymentData[$field] * ($request->percentage / 100));
                }
    
                SbCutOffPayment::updateOrCreate(['id' => $cutOffPayment->id], $cutOffPaymentData);
            }
    
            //cut_off_discount
            $cutOffDiscountsDeductibleFields = [
                'amount'
            ];
    
            $cutOffDiscounts = CutOffDiscount::where([
                'cut_off_id' => $cutOff->cut_off_id,
                'branch_id' => $cutOff->branch_id,
                'pos_machine_id' => $request->machine_id
            ])->get();
    
            foreach ($cutOffDiscounts as $cutOffDiscount) {
                $cutOffDiscountData = $cutOffDiscount->toArray();
    
                foreach ($cutOffDiscountsDeductibleFields as $field) {
                    $cutOffDiscountData[$field] = $cutOffDiscountData[$field] - ($cutOffDiscountData[$field] * ($request->percentage / 100));
                }
    
                SbCutOffDiscount::updateOrCreate(['id' => $cutOffDiscount->id], $cutOffDiscountData);
            }
        }

        return $this->sendResponse($sbEndOfDayResponse, 'SB readings updated successfully.');
    }

    public function getCutoff(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'machine_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
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
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->machine_id
        ])
        ->whereDate('treg', $request->date)
        ->first();

        if (!$cutOff) {
            return $this->sendError('No Cut Off for that day', [], 404);
        }

        $cutOff->departments = $cutOff->departments;
        $cutOff->payments = $cutOff->payments;
        $cutOff->discounts = $cutOff->discounts;

        return $this->sendResponse($cutOff, 'SB Cut Off retrieved successfully.');
    }

    public function getEndOfDay(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'machine_id' => 'required',
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
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->machine_id
        ])
        ->whereDate('treg', $request->date)
        ->first();

        if (!$endOfDay) {
            return $this->sendError('No End Of Day for that day', [], 404);
        }

        $endOfDay->departments = $endOfDay->departments;
        $endOfDay->payments = $endOfDay->payments;
        $endOfDay->discounts = $endOfDay->discounts;

        return $this->sendResponse($endOfDay, 'SB End Of Day retrieved successfully.');
    }
}