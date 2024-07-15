<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\CashDenomination;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Safekeeping;
use App\Models\SafekeepingDenomination;
use App\Models\EndOfDay;
use App\Models\CutOff;
use App\Models\PaymentType;
use App\Models\Discount;
use App\Models\DiscountDetail;
use App\Models\ApiRequestLog;
use App\Models\DiscountType;
use App\Models\Product;
use App\Models\PaymentOtherInformation;
use App\Models\DiscountOtherInformation;
use App\Models\CutOffDepartment;
use App\Models\CutOffDiscount;
use App\Models\CutOffPayment;
use App\Models\EndOfDayDiscount;
use App\Models\EndOfDayPayment;
use App\Models\EndOfDayDepartment;

use App\Models\TakeOrderTransaction;
use App\Models\TakeOrderOrder;

use App\Repositories\Interfaces\ProductRepositoryInterface;

use Carbon\Carbon;


class MiscController extends BaseController
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function cashDenominations()
    {
        $cashDenominations = CashDenomination::all();

        return $this->sendResponse($cashDenominations, 'Cash Denominations retrieved successfully.');
    }

    public function departments($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $departments = $branch->company->departments;

        return $this->sendResponse($departments, 'Departments retrieved successfully.');
    }

    public function categories($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $categories = $branch->company->categories;

        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    public function subCategories($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $subCategories = $branch->company->subCategories;

        return $this->sendResponse($subCategories, 'Sub Categories retrieved successfully.');
    }

    public function branchUsers($branchId)
    {
        $branch = Branch::with(['users.roles.permissions'])->find($branchId);

        $users = $branch->users;

        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    public function paymentTypes($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $paymentTypes = PaymentType::where('company_id', $branch->company->id)
            ->orWhereNull('company_id')
            ->with('fields')
            ->get();

        return $this->sendResponse($paymentTypes, 'Payment Types retrieved successfully.');
    }

    public function discountTypes($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $discountTypes = DiscountType::with('departments')->where('company_id', $branch->company->id)
            ->orWhereNull('company_id')
            ->with('fields')
            ->get();

        return $this->sendResponse($discountTypes, 'Discount Types retrieved successfully.');
    }

    public function chargeAccounts($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $chargeAccounts = $branch->company->chargeAccounts;

        return $this->sendResponse($chargeAccounts, 'Charge Accounts retrieved successfully.');
    }

    public function products(Request $request, $branchId)
    {
        $branch = Branch::with([
            'company',
            'company.products' => function ($query) {
                $query->whereHas('itemType', function ($subQuery) {
                    $subQuery->where('show_in_cashier', true);
                })->with('bundledItems', 'rawItems');
            },
        ])->find($branchId);

        if ($request->from_date) {
            $products = $branch->company->products()
                ->where(function ($query) use ($request) {
                    $query->where('updated_at', '>=', $request->from_date)
                          ->orWhere('created_at', '>=', $request->from_date);
                })
                ->where('uom_id', '>', 0)
                ->get();
        } else {
            $products = $branch->company->products()
                ->where('uom_id', '>', 0)
                ->get();
        }

        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    public function saveTakeOrderTransactions(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'transaction_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'gross_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'tender_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'change' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'service_charge' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cashier_id' => 'required',
            'total_unit_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_void_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'is_account_receivable' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_complete' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'branch_id' => 'required',
            'total_quantity' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_void_qty' => 'required|numeric',
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_return' => 'required|boolean',
            'total_cash_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_return_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'void_counter' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            //log request
            $log = new ApiRequestLog();
            $log->type = 'saveTransactions';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'transaction_id' => $request->transaction_id,
            'pos_machine_id' => $request->pos_machine_id,
            'control_number' => $request->control_number,
            'receipt_number' => $request->receipt_number,
            'gross_sales' => $request->gross_sales,
            'net_sales' => $request->net_sales,
            'vatable_sales' => $request->vatable_sales,
            'vat_exempt_sales' => $request->vat_exempt_sales,
            'vat_amount' => $request->vat_amount,
            'discount_amount' => $request->discount_amount,
            'tender_amount' => $request->tender_amount,
            'change' => $request->change,
            'service_charge' => $request->service_charge,
            'type' => $request->type,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'take_order_id' => $request->take_order_id,
            'take_order_name' => $request->take_order_name,
            'total_unit_cost' => $request->total_unit_cost,
            'total_void_amount' => $request->total_void_amount,
            'shift_number' => $request->shift_number,
            'is_void' => $request->is_void,
            'void_by_id' => $request->void_by_id,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
            'is_back_out' => $request->is_back_out,
            'is_back_out_id' => $request->is_back_out_id,
            'back_out_by' => $request->back_out_by,
            'charge_account_id' => $request->charge_account_id,
            'charge_account_name' => $request->charge_account_name,
            'is_account_receivable' => $request->is_account_receivable,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_complete' => $request->is_complete,
            'completed_at' => $request->completed_at,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
            'branch_id' => $request->branch_id,
            'guest_name' => $request->guest_name,
            'is_resume_printed' => $request->is_resume_printed ?? false,
            'treg' => $request->treg,
            'backout_at' => $request->backout_at,
            'total_quantity' => $request->total_quantity,
            'total_void_qty' => $request->total_void_qty,
            'vat_expense' => $request->vat_expense,
            'is_return' => $request->is_return,
            'total_cash_amount' => $request->total_cash_amount,
            'total_return_amount' => $request->total_return_amount,
            'void_counter' => $request->void_counter,
            'void_remarks' => $request->void_remarks,
            'customer_name' => $request->customer_name,
        ];

        //check if existing. update if yes
        $transaction = TakeOrderTransaction::where([
            'transaction_id' => $request->transaction_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Transaction created successfully.';
        if ($transaction) {
            $message = 'Transaction updated successfully.';
            $transaction->update($postData);

            return $this->sendResponse($transaction, $message);
        }


        return $this->sendResponse(TakeOrderTransaction::create($postData), $message);
    }

    public function saveTransactions(Request $request)
    {
        $requestData = $request->all();
        $validator = validator($request->all(), [
            'transaction_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'gross_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'tender_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'change' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'service_charge' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cashier_id' => 'required',
            'total_unit_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_void_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'is_account_receivable' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_complete' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'branch_id' => 'required',
            'total_quantity' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_void_qty' => 'required|numeric',
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_return' => 'required|boolean',
            'total_cash_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_return_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'void_counter' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            //log request
            $log = new ApiRequestLog();
            $log->type = 'saveTransactions';
            $log->method = $request->method();
            $log->request = json_encode($requestData);
            $log->response = json_encode($validator->errors());
            $log->save();

            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'transaction_id' => $request->transaction_id,
            'pos_machine_id' => $request->pos_machine_id,
            'control_number' => $request->control_number,
            'receipt_number' => $request->receipt_number,
            'gross_sales' => $request->gross_sales,
            'net_sales' => $request->net_sales,
            'vatable_sales' => $request->vatable_sales,
            'vat_exempt_sales' => $request->vat_exempt_sales,
            'vat_amount' => $request->vat_amount,
            'discount_amount' => $request->discount_amount,
            'tender_amount' => $request->tender_amount,
            'change' => $request->change,
            'service_charge' => $request->service_charge,
            'type' => $request->type,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'take_order_id' => $request->take_order_id,
            'take_order_name' => $request->take_order_name,
            'total_unit_cost' => $request->total_unit_cost,
            'total_void_amount' => $request->total_void_amount,
            'shift_number' => $request->shift_number,
            'is_void' => $request->is_void,
            'void_by_id' => $request->void_by_id,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
            'is_back_out' => $request->is_back_out,
            'is_back_out_id' => $request->is_back_out_id,
            'back_out_by' => $request->back_out_by,
            'charge_account_id' => $request->charge_account_id,
            'charge_account_name' => $request->charge_account_name,
            'is_account_receivable' => $request->is_account_receivable,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_complete' => $request->is_complete,
            'completed_at' => $request->completed_at,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
            'branch_id' => $request->branch_id,
            'guest_name' => $request->guest_name,
            'is_resume_printed' => $request->is_resume_printed ?? false,
            'treg' => $request->treg,
            'backout_at' => $request->backout_at,
            'total_quantity' => $request->total_quantity,
            'total_void_qty' => $request->total_void_qty,
            'vat_expense' => $request->vat_expense,
            'is_return' => $request->is_return,
            'total_cash_amount' => $request->total_cash_amount,
            'total_return_amount' => $request->total_return_amount,
            'void_counter' => $request->void_counter,
            'void_remarks' => $request->void_remarks,
            'customer_name' => $request->customer_name,
        ];

        //check if existing. update if yes
        $transaction = Transaction::where([
            'transaction_id' => $request->transaction_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Transaction created successfully.';
        if ($transaction) {
            $message = 'Transaction updated successfully.';
            $transaction->update($postData);

            return $this->sendResponse($transaction, $message);
        }


        return $this->sendResponse(Transaction::create($postData), $message);
    }

    public function getTransactions(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $transactions = Transaction::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($transactions->count() == 0) {
            $transactions = Transaction::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('transaction_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($transactions, 'Transactions retrieved successfully.');
    }

    public function getTakeOrderTransactions(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $transactions = TakeOrderTransaction::where([
            'branch_id' => $request->branch_id,
            'is_complete' => false,
        ])->get();

        return $this->sendResponse($transactions, 'Transactions retrieved successfully.');
    }

    public function saveOrders(Request $request)
    {
        $validator = validator($request->all(), [
            'order_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'transaction_id' => 'required',
            'product_id' => 'required',
            'cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'qty' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'original_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'gross' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_vatable' => 'required|boolean',
            'vat_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'unit_id' => 'required|numeric',
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'min_amount_sold' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_paid' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_completed' => 'required|boolean',
            'branch_id' => 'required',
            'is_cut_off' => 'required|boolean',
            'is_discount_exempt' => 'required|boolean',
            'is_open_price' => 'required|boolean',
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'with_serial' => 'required|boolean',
            'is_return' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'order_id' => $request->order_id,
            'pos_machine_id' => $request->pos_machine_id,
            'transaction_id' => $request->transaction_id,
            'product_id' => $request->product_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'abbreviation' => $request->abbreviation,
            'cost' => $request->cost,
            'qty' => $request->qty,
            'amount' => $request->amount,
            'original_amount' => $request->original_amount,
            'gross' => $request->gross,
            'total' => $request->total,
            'total_cost' => $request->total_cost,
            'is_vatable' => $request->is_vatable,
            'vat_amount' => $request->vat_amount,
            'vatable_sales' => $request->vatable_sales,
            'vat_exempt_sales' => $request->vat_exempt_sales,
            'discount_amount' => $request->discount_amount,
            'department_id' => $request->department_id,
            'department_name' => $request->department_name,
            'category_id' => $request->category_id,
            'category_name' => $request->category_name,
            'subcategory_id' => $request->subcategory_id,
            'subcategory_name' => $request->subcategory_name,
            'unit_id' => $request->unit_id,
            'unit_name' => $request->unit_name,
            'is_void' => $request->is_void,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
            'is_back_out' => $request->is_back_out,
            'is_back_out_id' => $request->is_back_out_id,
            'back_out_by' => $request->back_out_by,
            'min_amount_sold' => $request->min_amount_sold,
            'is_paid' => $request->is_paid,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
            'branch_id' => $request->branch_id,
            'shift_number' => $request->shift_number,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
            'discount_details_id' => $request->discount_details_id,
            'treg' => $request->treg,
            'is_discount_exempt' => $request->is_discount_exempt,
            'is_open_price' => $request->is_open_price,
            'remarks' => $request->remarks,
            'vat_expense' => $request->vat_expense,
            'with_serial' => $request->with_serial,
            'is_return' => $request->is_return,
            'serial_number' => $request->serial_number,
        ];

        $order = Order::where([
            'order_id' => $request->order_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Order created successfully.';
        if ($order) {
            $message = 'Order updated successfully.';
            $order->update($postData);
            return $this->sendResponse($order, $message);
        }

        return $this->sendResponse(Order::create($postData), $message);
    }

    public function saveTakeOrderOrders(Request $request)
    {
        $validator = validator($request->all(), [
            'order_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'transaction_id' => 'required',
            'product_id' => 'required',
            'cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'qty' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'original_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'gross' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_vatable' => 'required|boolean',
            'vat_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'unit_id' => 'required|numeric',
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'min_amount_sold' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_paid' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_completed' => 'required|boolean',
            'branch_id' => 'required',
            'is_cut_off' => 'required|boolean',
            'is_discount_exempt' => 'required|boolean',
            'is_open_price' => 'required|boolean',
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'with_serial' => 'required|boolean',
            'is_return' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'order_id' => $request->order_id,
            'pos_machine_id' => $request->pos_machine_id,
            'transaction_id' => $request->transaction_id,
            'product_id' => $request->product_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'abbreviation' => $request->abbreviation,
            'cost' => $request->cost,
            'qty' => $request->qty,
            'amount' => $request->amount,
            'original_amount' => $request->original_amount,
            'gross' => $request->gross,
            'total' => $request->total,
            'total_cost' => $request->total_cost,
            'is_vatable' => $request->is_vatable,
            'vat_amount' => $request->vat_amount,
            'vatable_sales' => $request->vatable_sales,
            'vat_exempt_sales' => $request->vat_exempt_sales,
            'discount_amount' => $request->discount_amount,
            'department_id' => $request->department_id,
            'department_name' => $request->department_name,
            'category_id' => $request->category_id,
            'category_name' => $request->category_name,
            'subcategory_id' => $request->subcategory_id,
            'subcategory_name' => $request->subcategory_name,
            'unit_id' => $request->unit_id,
            'unit_name' => $request->unit_name,
            'is_void' => $request->is_void,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
            'is_back_out' => $request->is_back_out,
            'is_back_out_id' => $request->is_back_out_id,
            'back_out_by' => $request->back_out_by,
            'min_amount_sold' => $request->min_amount_sold,
            'is_paid' => $request->is_paid,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
            'branch_id' => $request->branch_id,
            'shift_number' => $request->shift_number,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
            'discount_details_id' => $request->discount_details_id,
            'treg' => $request->treg,
            'is_discount_exempt' => $request->is_discount_exempt,
            'is_open_price' => $request->is_open_price,
            'remarks' => $request->remarks,
            'vat_expense' => $request->vat_expense,
            'with_serial' => $request->with_serial,
            'is_return' => $request->is_return,
            'serial_number' => $request->serial_number,
        ];

        $order = TakeOrderOrder::where([
            'order_id' => $request->order_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Order created successfully.';
        if ($order) {
            $message = 'Order updated successfully.';
            $order->update($postData);
            return $this->sendResponse($order, $message);
        }

        return $this->sendResponse(TakeOrderOrder::create($postData), $message);
    }

    public function getTakeOrderOrders(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = TakeOrderOrder::where([
            'branch_id' => $request->branch_id,
            'is_completed' => false,
        ]);

        if ($request->has('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }

        $orders = $query->get();

        return $this->sendResponse($orders, 'Orders retrieved successfully.');
    }

    public function getOrders(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $orders = Order::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($orders->count() == 0) {
            $orders = Order::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('order_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($orders, 'Orders retrieved successfully.');
    }

    public function savePayments(Request $request)
    {
        $validator = validator($request->all(), [
            'payment_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'transaction_id' => 'required',
            'payment_type_id' => 'required',
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_advance_payment' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'is_void' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'payment_id' => $request->payment_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'transaction_id' => $request->transaction_id,
            'payment_type_id' => $request->payment_type_id,
            'payment_type_name' => $request->payment_type_name,
            'amount' => $request->amount,
            'is_advance_payment' => $request->is_advance_payment,
            'shift_number' => $request->shift_number,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
            'treg' => $request->treg,
            'is_void' => $request->is_void,
            'void_at' => $request->void_at ?? null,
            'void_by' => $request->void_by ?? null,
            'void_by_id' => $request->void_by_id ?? null,
        ];

        $payment = Payment::where([
            'payment_id' => $request->payment_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Payment created successfully.';
        if ($payment) {
            $message = 'Payment updated successfully.';
            $payment->update($postData);
            return $this->sendResponse($payment, $message);
        }

        return $this->sendResponse(Payment::create($postData), $message);
    }

    public function getPayments(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $payments = Payment::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($payments->count() == 0) {
            $payments = Payment::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('payment_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($payments, 'Payments retrieved successfully.');
    }

    public function saveSafekeepings(Request $request)
    {
        $validator = validator($request->all(), [
            'safekeeping_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cashier_id' => 'required',
            'authorize_id' => 'required',
            'is_cut_off' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',

            'end_of_day_id' => 'required',
            'is_auto' => 'required|boolean',
            'short_over' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'safekeeping_id' => $request->safekeeping_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'amount' => $request->amount,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'authorize_id' => $request->authorize_id,
            'authorize_name' => $request->authorize_name,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'shift_number' => $request->shift_number,
            'treg' => $request->treg,
            'end_of_day_id' => $request->end_of_day_id,
            'is_auto' => $request->is_auto,
            'short_over' => $request->short_over,
        ];

        $safekeeping = Safekeeping::where([
            'safekeeping_id' => $request->safekeeping_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Safekeeping created successfully.';
        if ($safekeeping) {
            $message = 'Safekeeping updated successfully.';
            $safekeeping->update($postData);
            return $this->sendResponse($safekeeping, $message);
        }

        return $this->sendResponse(Safekeeping::create($postData), $message);
    }

    public function getSafekeepings(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $safekeepings = Safekeeping::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
                'is_cut_off' => false,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($safekeepings->count() == 0) {
            $safekeepings = Safekeeping::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('safekeeping_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($safekeepings, 'Safekeepings retrieved successfully.');
    }

    public function saveSafekeepingsDenominations(Request $request)
    {
        $validator = validator($request->all(), [
            'safekeeping_denomination_id' => 'required|numeric|min:1',
            'safekeeping_id' => 'required',
            'cash_denomination_id' => 'required',
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'qty' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
            'end_of_day_id' => 'required',
            'is_cut_off' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
            'safekeeping_denomination_id' => $request->safekeeping_denomination_id,
            'safekeeping_id' => $request->safekeeping_id,
            'cash_denomination_id' => $request->cash_denomination_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'qty' => $request->qty,
            'total' => $request->total,
            'shift_number' => $request->shift_number,
            'cut_off_id' => $request->cut_off_id,
            'treg' => $request->treg,
            'end_of_day_id' => $request->end_of_day_id,
            'is_cut_off' => $request->is_cut_off,
            'is_sent_to_server' => $request->is_sent_to_server,
        ];

        $safekeepingDenomination = SafekeepingDenomination::where([
            'safekeeping_denomination_id' => $request->safekeeping_denomination_id,
            'safekeeping_id' => $request->safekeeping_id,
            'cash_denomination_id' => $request->cash_denomination_id,
        ])->first();

        $message = 'Safekeeping Denomination created successfully.';
        if ($safekeepingDenomination) {
            $message = 'Safekeeping Denomination updated successfully.';
            $safekeepingDenomination->update($postData);
            return $this->sendResponse($safekeepingDenomination, $message);
        }

        return $this->sendResponse(SafekeepingDenomination::create($postData), $message);
    }

    public function getSafekeepingDenominations(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $safekeepings = SafekeepingDenomination::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($safekeepings->count() == 0) {
            $safekeepings = SafekeepingDenomination::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('safekeeping_denomination_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($safekeepings, 'Safekeepings retrieved successfully.');
    }

    public function testConnection()
    {
        return $this->sendResponse([], 'Successfully connected to server.');
    }

    public function saveEndOfDays(Request $request)
    {
        $validator = validator($request->all(), [
            'end_of_day_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'beginning_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'ending_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_transactions' => 'required|numeric',
            'gross_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'void_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_change' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_payout' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_service_charge' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_sk' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cashier_id' => 'required',
            'shift_number' => 'required',
            'is_sent_to_server' => 'required|boolean',
            'reading_number' => 'required|numeric',
            'void_qty' => 'required|numeric',
            'total_short_over' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $branch = Branch::findOrFail($request->branch_id);

        $postData = [
            'end_of_day_id' => $request->end_of_day_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'beginning_or' => $request->beginning_or,
            'ending_or' => $request->ending_or,
            'beginning_amount' => $request->beginning_amount,
            'ending_amount' => $request->ending_amount,
            'total_transactions' => $request->total_transactions,
            'gross_sales' => $request->gross_sales,
            'net_sales' => $request->net_sales,
            'vatable_sales' => $request->vatable_sales,
            'vat_exempt_sales' => $request->vat_exempt_sales,
            'vat_amount' => $request->vat_amount,
            'vat_expense' => $request->vat_expense,
            'void_amount' => $request->void_amount,
            'total_change' => $request->total_change,
            'total_payout' => $request->total_payout,
            'total_service_charge' => $request->total_service_charge,
            'total_discount_amount' => $request->total_discount_amount,
            'total_cost' => $request->total_cost,
            'total_sk' => $request->total_sk,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'admin_id' => $request->admin_id,
            'admin_name' => $request->admin_name,
            'shift_number' => $request->shift_number,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
            'reading_number' => $request->reading_number,
            'void_qty' => $request->void_qty,
            'total_short_over' => $request->total_short_over,
            'generated_date' => $request->generated_date,
        ];

        if ($request['products']) {
            foreach ($request['products'] as $reqProduct) {
                $product = Product::find($reqProduct['productId']);

                if ($product) {
                    $this->productRepository->updateBranchQuantity($product, $branch, $reqProduct['endOfDayId'], 'end_of_days', $reqProduct['qty'], null, 'subtract', $product->uom_id);
                }
            }
        }

        $endOfDay = EndOfDay::where([
            'end_of_day_id' => $request->end_of_day_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        TakeOrderTransaction::where('branch_id', $request->branch_id)->delete();
        TakeOrderOrder::where('branch_id', $request->branch_id)->delete();

        $message = 'End of Day created successfully.';
        if ($endOfDay) {
            $message = 'End of Day updated successfully.';
            $endOfDay->update($postData);
            return $this->sendResponse($endOfDay, $message);
        }

        return $this->sendResponse(EndOfDay::create($postData), $message);
    }

    public function getEndOfDays(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $endOfDays = EndOfDay::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($endOfDays->count() == 0) {
            $endOfDays = EndOfDay::where([
                    'branch_id' => $request->branch_id,
                    'pos_machine_id' => $request->pos_machine_id,
                ])
                ->orderBy('end_of_day_id', 'desc')
                ->limit(2)
                ->get();
        }

        foreach ($endOfDays as $endOfDay) {
            $endOfDay->departments = $endOfDay->departments;
            $endOfDay->payments = $endOfDay->payments;
            $endOfDay->discounts = $endOfDay->discounts;
        }

        return $this->sendResponse($endOfDays, 'End of Days retrieved successfully.');
    }

    public function saveCutOffs(Request $request)
    {
        $validator = validator($request->all(), [
            'cut_off_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'beginning_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'ending_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_transactions' => 'required|numeric',
            'gross_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'void_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_change' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_payout' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_service_charge' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_sk' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cashier_id' => 'required',
            'shift_number' => 'required',
            'is_sent_to_server' => 'required|boolean',
            'reading_number' => 'required|numeric',
            'void_qty' => 'required|numeric',
            'total_short_over' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cut_off_id' => $request->cut_off_id,
            'end_of_day_id' => $request->end_of_day_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'beginning_or' => $request->beginning_or,
            'ending_or' => $request->ending_or,
            'beginning_amount' => $request->beginning_amount,
            'ending_amount' => $request->ending_amount,
            'total_transactions' => $request->total_transactions,
            'gross_sales' => $request->gross_sales,
            'net_sales' => $request->net_sales,
            'vatable_sales' => $request->vatable_sales,
            'vat_exempt_sales' => $request->vat_exempt_sales,
            'vat_amount' => $request->vat_amount,
            'vat_expense' => $request->vat_expense,
            'void_amount' => $request->void_amount,
            'total_change' => $request->total_change,
            'total_payout' => $request->total_payout,
            'total_service_charge' => $request->total_service_charge,
            'total_discount_amount' => $request->total_discount_amount,
            'total_cost' => $request->total_cost,
            'total_sk' => $request->total_sk,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'admin_id' => $request->admin_id,
            'admin_name' => $request->admin_name,
            'shift_number' => $request->shift_number,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
            'reading_number' => $request->reading_number,
            'void_qty' => $request->void_qty,
            'total_short_over' => $request->total_short_over,
        ];

        $cutOff = CutOff::where([
            'cut_off_id' => $request->cut_off_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Cut Off created successfully.';
        if ($cutOff) {
            $message = 'Cut Off updated successfully.';
            $cutOff->update($postData);
            return $this->sendResponse($cutOff, $message);
        }

        return $this->sendResponse(CutOff::create($postData), $message);
    }

    public function getCutOffs(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $cutOffs = CutOff::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($cutOffs->count() == 0) {
            $cutOffs = CutOff::where([
                    'branch_id' => $request->branch_id,
                    'pos_machine_id' => $request->pos_machine_id,
                ])
                ->orderBy('cut_off_id', 'desc')
                ->limit(2)
                ->get();
        }

        return $this->sendResponse($cutOffs, 'Cut Offs retrieved successfully.');
    }

    public function saveDiscounts(Request $request)
    {
        $validator = validator($request->all(), [
            'discount_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'transaction_id' => 'required',
            'custom_discount_id' => 'required|numeric',
            'discount_type_id' => 'required',
            'value' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cashier_id' => 'required',
            'is_void' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'discount_id' => $request->discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'transaction_id' => $request->transaction_id,
            'custom_discount_id' => $request->custom_discount_id,
            'discount_type_id' => $request->discount_type_id,
            'discount_name' => $request->discount_name,
            'value' => $request->value,
            'discount_amount' => $request->discount_amount,
            'vat_exempt_amount' => $request->vat_exempt_amount,
            'type' => $request->type,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'authorize_id' => $request->authorize_id,
            'authorize_name' => $request->authorize_name,
            'is_void' => $request->is_void,
            'void_by_id' => $request->void_by_id,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'shift_number' => $request->shift_number,
            'treg' => $request->treg,
            'vat_expense' => $request->vat_expense,
        ];

        $message = 'Discount created successfully.';
        $discount = Discount::where([
            'discount_id' => $request->discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($discount) {
            $message = 'Discount updated successfully.';
            $discount->update($postData);
            return $this->sendResponse($discount, $message);
        }

        return $this->sendResponse(Discount::create($postData), $message);
    }

    public function getDiscounts(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $discounts = Discount::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
        ])->get();

        return $this->sendResponse($discounts, 'Discounts retrieved successfully.');
    }

    public function saveDiscountDetails(Request $request)
    {
        $validator = validator($request->all(), [
            'discount_details_id' => 'required|numeric|min:1',
            'discount_id' => 'required|numeric',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'custom_discount_id' => 'required|numeric',
            'transaction_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'discount_type_id' => 'required|numeric',
            'value' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_exempt_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat_expense' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_void' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'is_vat_exempt' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'discount_details_id' => $request->discount_details_id,
            'discount_id' => $request->discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'custom_discount_id' => $request->custom_discount_id,
            'transaction_id' => $request->transaction_id,
            'order_id' => $request->order_id,
            'discount_type_id' => $request->discount_type_id,
            'value' => $request->value,
            'discount_amount' => $request->discount_amount,
            'vat_exempt_amount' => $request->vat_exempt_amount,
            'type' => $request->type,
            'is_void' => $request->is_void,
            'void_by_id' => $request->void_by_id,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'is_vat_exempt' => $request->is_vat_exempt,
            'shift_number' => $request->shift_number,
            'treg' => $request->treg,
            'vat_expense' => $request->vat_expense,
        ];

        $message = 'Discount Details created successfully.';
        $discountDetails = DiscountDetail::where([
            'discount_details_id' => $request->discount_details_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($discountDetails) {
            $message = 'Discount Details updated successfully.';
            $discountDetails->update($postData);
            return $this->sendResponse($discountDetails, $message);
        }

        return $this->sendResponse(DiscountDetail::create($postData), $message);
    }

    public function getDiscountDetails(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $discounts = DiscountDetail::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($discounts->count() == 0) {
            $discounts = DiscountDetail::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('discount_details_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($discounts, 'Discount details retrieved successfully.');
    }

    public function savePaymentOtherInformations(Request $request) 
    {
        $validator = validator($request->all(), [
            'payment_other_information_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'transaction_id' => 'required',
            'payment_id' => 'required',
            'name' => 'required',
            'value' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'is_void' => 'required',
            'is_sent_to_server' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'payment_other_information_id' => $request->payment_other_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'transaction_id' => $request->transaction_id,
            'payment_id' => $request->payment_id,
            'name' => $request->name,
            'value' => $request->value,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'is_void' => $request->is_void,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'payment other informations created successfully.';
        $record = PaymentOtherInformation::where([
            'payment_other_information_id' => $request->payment_other_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'payment other informations updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(PaymentOtherInformation::create($postData), $message);
    }

    public function getPaymentOtherInformations(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $records = PaymentOtherInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = PaymentOtherInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('payment_other_information_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'Payment Other Informations retrieved successfully.');
    }

    public function saveDiscountOtherInformations(Request $request) 
    {
        $validator = validator($request->all(), [
            'discount_other_information_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'transaction_id' => 'required',
            'discount_id' => 'required',
            'name' => 'required',
            'value' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'is_void' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'discount_other_information_id' => $request->discount_other_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'transaction_id' => $request->transaction_id,
            'discount_id' => $request->discount_id,
            'name' => $request->name,
            'value' => $request->value,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'is_void' => $request->is_void,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'discount other informations created successfully.';
        $record = DiscountOtherInformation::where([
            'discount_other_information_id' => $request->discount_other_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'discount other informations updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(DiscountOtherInformation::create($postData), $message);
    }

    public function getDiscountOtherInformations(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $records = DiscountOtherInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = DiscountOtherInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('discount_other_information_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'Discount Other Informations retrieved successfully.');
    }

    public function saveCutOffDepartments(Request $request) 
    {
        $validator = validator($request->all(), [
            'cut_off_department_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'cut_off_id' => 'required',
            'department_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'end_of_day_id' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
            'is_cut_off' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cut_off_department_id' => $request->cut_off_department_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'department_id' => $request->department_id,
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'end_of_day_id' => $request->end_of_day_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'cut off department created successfully.';
        $record = CutOffDepartment::where([
            'cut_off_department_id' => $request->cut_off_department_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'cut off department updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(CutOffDepartment::create($postData), $message);
    }

    public function getCutOffDepartments(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $records = CutOffDepartment::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id,
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = CutOffDepartment::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('cut_off_department_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'cut off departments retrieved successfully.');
    }

    public function saveCutOffDiscounts(Request $request) 
    {
        $validator = validator($request->all(), [
            'cut_off_discount_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'cut_off_id' => 'required',
            'discount_type_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'end_of_day_id' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cut_off_discount_id' => $request->cut_off_discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'cut_off_id' => $request->cut_off_id,
            'discount_type_id' => $request->discount_type_id,
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'end_of_day_id' => $request->end_of_day_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
            'is_cut_off' => $request->is_cut_off,
        ];

        $message = 'cut off discount created successfully.';
        $record = CutOffDiscount::where([
            'cut_off_discount_id' => $request->cut_off_discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'cut off discount updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(CutOffDiscount::create($postData), $message);
    }

    public function getCutOffDiscounts(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $records = CutOffDiscount::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = CutOffDiscount::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('cut_off_discount_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'cut off discounts retrieved successfully.');
    }

    public function saveCutOffPayments(Request $request) 
    {
        $validator = validator($request->all(), [
            'cut_off_payment_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'cut_off_id' => 'required',
            'payment_type_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'end_of_day_id' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
            'is_cut_off' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cut_off_payment_id' => $request->cut_off_payment_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'cut_off_id' => $request->cut_off_id,
            'payment_type_id' => $request->payment_type_id,
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'end_of_day_id' => $request->end_of_day_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
            'is_cut_off' => $request->is_cut_off,
        ];

        $message = 'cut off payment created successfully.';
        $record = CutOffPayment::where([
            'cut_off_payment_id' => $request->cut_off_payment_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'cut off payment updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(CutOffPayment::create($postData), $message);
    }

    public function getCutOffPayments(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $records = CutOffPayment::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = CutOffPayment::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('cut_off_payment_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'cut off payments retrieved successfully.');
    }

    public function saveEndOfDayDiscounts(Request $request) 
    {
        $validator = validator($request->all(), [
            'end_of_day_discount_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'end_of_day_id' => 'required',
            'discount_type_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'end_of_day_discount_id' => $request->end_of_day_discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'end_of_day_id' => $request->end_of_day_id,
            'discount_type_id' => $request->discount_type_id,
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'end of day discount created successfully.';
        $record = EndOfDayDiscount::where([
            'end_of_day_discount_id' => $request->end_of_day_discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'end of day discount updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(EndOfDayDiscount::create($postData), $message);
    }

    public function saveEndOfDayPayments(Request $request) 
    {
        $validator = validator($request->all(), [
            'end_of_day_payment_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'end_of_day_id' => 'required',
            'payment_type_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'end_of_day_payment_id' => $request->end_of_day_payment_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'end_of_day_id' => $request->end_of_day_id,
            'payment_type_id' => $request->payment_type_id,
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'end of day payment created successfully.';
        $record = EndOfDayPayment::where([
            'end_of_day_payment_id' => $request->end_of_day_payment_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'end of day payment updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(EndOfDayPayment::create($postData), $message);
    }

    public function saveEndOfDayDepartments(Request $request) 
    {
        $validator = validator($request->all(), [
            'end_of_day_department_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'end_of_day_id' => 'required',
            'discount_type_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'end_of_day_department_id' => $request->end_of_day_department_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'end_of_day_id' => $request->end_of_day_id,
            'discount_type_id' => $request->discount_type_id,
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'end of day department created successfully.';
        $record = EndOfDayDepartment::where([
            'end_of_day_department_id' => $request->end_of_day_department_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'end of day department updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(EndOfDayDepartment::create($postData), $message);
    }

    public function bulkSaveTransactions(Request $request)
    {
        $validator = validator($request->all(), [
            'conditions.branch_id' => 'required',
            'conditions.pos_machine_id' => 'required',
            'conditions.transaction_id' => 'required|array',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = $request->all();

        // Extract the data and conditions
        $data = $postData['data'];
        $conditions = $postData['conditions'];

        // Build the query dynamically
        $query = Transaction::query();

        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        try {
            // Perform the bulk update and check if it was successful
            $affectedRows = $query->update($data);

            if ($affectedRows > 0) {
                return response()->json(['message' => 'Records updated successfully!', 'affectedRows' => $affectedRows]);
            } else {
                return response()->json(['message' => 'No records were updated or fields did not exist.', 'affectedRows' => $affectedRows], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the records.', 'error' => $e->getMessage()], 500);
        }
    }
}
