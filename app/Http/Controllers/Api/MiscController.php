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
use Illuminate\Support\Facades\Redis;

class MiscController extends BaseController
{
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
        $branch = Branch::find($branchId);

        $users = $branch->users;

        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    public function paymentTypes($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $paymentTypes = PaymentType::where('company_id', $branch->company->id)
            ->orWhereNull('company_id')
            ->get();

        return $this->sendResponse($paymentTypes, 'Payment Types retrieved successfully.');
    }

    public function discountTypes($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $discountTypes = $branch->company->discountTypes;

        return $this->sendResponse($discountTypes, 'Discount Types retrieved successfully.');
    }

    public function chargeAccounts($branchId)
    {
        $branch = Branch::with('company')->find($branchId);

        $chargeAccounts = $branch->company->chargeAccounts;

        return $this->sendResponse($chargeAccounts, 'Charge Accounts retrieved successfully.');
    }

    public function products($branchId)
    {
        $branch = Branch::with([
            'company',
            'company.products' => function ($query) {
                $query->whereHas('itemType', function ($subQuery) {
                    $subQuery->where('show_in_cashier', true);
                })->with('bundledItems', 'rawItems');
            },
        ])->find($branchId);

        $products = $branch->company->products;

        return $this->sendResponse($products, 'Charge Accounts retrieved successfully.');
    }

    public function saveTransactions(Request $request)
    {
        $validator = validator($request->all(), [
            'transaction_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required|exists:pos_machines,id',
            'gross_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_excempt_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'tender_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'change' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'service_charge' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cashier_id' => 'required|exists:users,id',
            'total_unit_cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_void_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'is_account_receivable' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_complete' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
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
            'vat_excempt_sales' => $request->vat_excempt_sales,
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
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $transactions = Transaction::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
            'is_cut_off' => false,
        ])->get();

        return $this->sendResponse($transactions, 'Transactions retrieved successfully.');
    }

    public function saveOrders(Request $request)
    {
        $validator = validator($request->all(), [
            'order_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required|exists:pos_machines,id',
            'transaction_id' => 'required|exists:transactional_db.transactions,transaction_id',
            'product_id' => 'required|exists:products,id',
            'cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'qty' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'original_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'gross' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_vatable' => 'required|boolean',
            'vat_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'discount_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'unit_id' => 'required|numeric',
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'min_amount_sold' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_paid' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_completed' => 'required|boolean',
            'branch_id' => 'required|exists:branches,id',
            'is_cut_off' => 'required|boolean',
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
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
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

    public function getOrders(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $orders = Order::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
            'is_cut_off' => false,
        ])->get();

        return $this->sendResponse($orders, 'Orders retrieved successfully.');
    }

    public function savePayments(Request $request)
    {
        $validator = validator($request->all(), [
            'payment_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required|exists:pos_machines,id',
            'branch_id' => 'required|exists:branches,id',
            'transaction_id' => 'required|exists:transactional_db.transactions,transaction_id',
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_advance_payment' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
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
            'other_informations' => $request->other_informations,
            'is_advance_payment' => $request->is_advance_payment,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
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
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $payments = Payment::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
            'is_cut_off' => false,
        ])->get();

        return $this->sendResponse($payments, 'Payments retrieved successfully.');
    }

    public function saveSafekeepings(Request $request)
    {
        $validator = validator($request->all(), [
            'safekeeping_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required|exists:pos_machines,id',
            'branch_id' => 'required|exists:branches,id',
            'amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cashier_id' => 'required|exists:users,id',
            'authorize_id' => 'required|exists:users,id',
            'is_cut_off' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
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
            'cut_off_at' => $request->cut_off_at,
            'is_sent_to_server' => $request->is_sent_to_server,
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
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $safekeepings = Safekeeping::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
            'is_cut_off' => false,
        ])->get();

        return $this->sendResponse($safekeepings, 'Safekeepings retrieved successfully.');
    }

    public function saveSafekeepingsDenominations(Request $request)
    {
        $validator = validator($request->all(), [
            'safekeeping_denomination_id' => 'required|numeric|min:1',
            'safekeeping_id' => 'required|exists:transactional_db.safekeepings,safekeeping_id',
            'cash_denomination_id' => 'required|exists:cash_denominations,id',
            'amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'qty' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'safekeeping_denomination_id' => $request->safekeeping_denomination_id,
            'safekeeping_id' => $request->safekeeping_id,
            'cash_denomination_id' => $request->cash_denomination_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'qty' => $request->qty,
            'total' => $request->total,
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
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
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $safekeepings = SafekeepingDenomination::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
        ])->get();

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
            'pos_machine_id' => 'required|exists:pos_machines,id',
            'branch_id' => 'required|exists:branches,id',
            'beginning_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'ending_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_transactions' => 'required|numeric',
            'gross_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_expense' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'void_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_cash_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_card_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_online_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_ar_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_mobile_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_charge' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'senior_count' => 'required|numeric',
            'senior_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'pwd_count' => 'required|numeric',
            'pwd_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'others_count' => 'required|numeric',
            'others_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_payout' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_service_charge' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_discount_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_ar_cash_redeemed_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_ar_card_redeemed_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_sk' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cashier_id' => 'required|exists:users,id',
            'admin_id' => 'required|exists:users,id',
            'shift_number' => 'required',
            'is_sent_to_server' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

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
            'total_cash_payments' => $request->total_cash_payments,
            'total_card_payments' => $request->total_card_payments,
            'total_online_payments' => $request->total_online_payments,
            'total_ar_payments' => $request->total_ar_payments,
            'total_mobile_payments' => $request->total_mobile_payments,
            'total_charge' => $request->total_charge,
            'senior_count' => $request->senior_count,
            'senior_amount' => $request->senior_amount,
            'pwd_count' => $request->pwd_count,
            'pwd_amount' => $request->pwd_amount,
            'others_count' => $request->others_count,
            'others_amount' => $request->others_amount,
            'others_json' => $request->others_json,
            'total_payout' => $request->total_payout,
            'total_service_charge' => $request->total_service_charge,
            'total_discount_amount' => $request->total_discount_amount,
            'total_ar_cash_redeemed_amount' => $request->total_ar_cash_redeemed_amount,
            'total_ar_card_redeemed_amount' => $request->total_ar_card_redeemed_amount,
            'total_cost' => $request->total_cost,
            'total_sk' => $request->total_sk,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'admin_id' => $request->admin_id,
            'admin_name' => $request->admin_name,
            'shift_number' => $request->shift_number,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $endOfDay = EndOfDay::where([
            'end_of_day_id' => $request->end_of_day_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

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
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $endOfDays = EndOfDay::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
        ])->get();

        return $this->sendResponse($endOfDays, 'End of Days retrieved successfully.');
    }

    public function saveCutOffs(Request $request)
    {
        $validator = validator($request->all(), [
            'cut_off_id' => 'required|numeric|min:1',
            'end_of_day_id' => 'required|exists:transactional_db.end_of_days,end_of_day_id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
            'branch_id' => 'required|exists:branches,id',
            'beginning_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'ending_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_transactions' => 'required|numeric',
            'gross_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'net_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vatable_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_exempt_sales' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat_expense' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'void_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_cash_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_card_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_online_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_ar_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_mobile_payments' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_charge' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'senior_count' => 'required|numeric',
            'senior_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'pwd_count' => 'required|numeric',
            'pwd_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'others_count' => 'required|numeric',
            'others_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_payout' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_service_charge' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_discount_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_ar_cash_redeemed_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_ar_card_redeemed_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_sk' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cashier_id' => 'required|exists:users,id',
            'admin_id' => 'required|exists:users,id',
            'shift_number' => 'required',
            'is_sent_to_server' => 'required|boolean',
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
            'total_cash_payments' => $request->total_cash_payments,
            'total_card_payments' => $request->total_card_payments,
            'total_online_payments' => $request->total_online_payments,
            'total_ar_payments' => $request->total_ar_payments,
            'total_mobile_payments' => $request->total_mobile_payments,
            'total_charge' => $request->total_charge,
            'senior_count' => $request->senior_count,
            'senior_amount' => $request->senior_amount,
            'pwd_count' => $request->pwd_count,
            'pwd_amount' => $request->pwd_amount,
            'others_count' => $request->others_count,
            'others_amount' => $request->others_amount,
            'others_json' => $request->others_json,
            'total_payout' => $request->total_payout,
            'total_service_charge' => $request->total_service_charge,
            'total_discount_amount' => $request->total_discount_amount,
            'total_ar_cash_redeemed_amount' => $request->total_ar_cash_redeemed_amount,
            'total_ar_card_redeemed_amount' => $request->total_ar_card_redeemed_amount,
            'total_cost' => $request->total_cost,
            'total_sk' => $request->total_sk,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'admin_id' => $request->admin_id,
            'admin_name' => $request->admin_name,
            'shift_number' => $request->shift_number,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
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
            'branch_id' => 'required|exists:branches,id',
            'pos_machine_id' => 'required|exists:pos_machines,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $cutOffs = CutOff::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id,
        ])->get();

        return $this->sendResponse($cutOffs, 'Cut Offs retrieved successfully.');
    }
}
