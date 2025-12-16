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
use App\Models\TakeOrderDiscount;
use App\Models\TakeOrderDiscountDetail;
use App\Models\TakeOrderDiscountOtherInformation;
use App\Models\CashFund;
use App\Models\CashFundDenomination;
use App\Models\AuditTrail;
use App\Models\CutOffProduct;
use App\Models\EndOfDayProduct;
use App\Models\OfficialReceiptInformation;
use App\Models\Payout;
use App\Models\SpotAudit;
use App\Models\SpotAuditDenomination;
use App\Models\TakeOrderTransaction;
use App\Models\TakeOrderOrder;
use App\Models\BranchProduct;

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

        $chargeAccounts = $branch->chargeAccounts;

        return $this->sendResponse($chargeAccounts, 'Charge Accounts retrieved successfully.');
    }

    public function priceChangeReasons(Request $request, $branchId)
    {
        $branch = Branch::with([
            'company',
            'company.products' => function ($query) {
                $query->whereHas('itemType', function ($subQuery) {
                    $subQuery->where('show_in_cashier', true);
                })->with('bundledItems', 'rawItems');
            },
        ])->find($branchId);

        $products = $branch->company->changePriceReasons()->get();

        return $this->sendResponse($products, 'Price Change Reasons retrieved successfully.');
    }

    public function products(Request $request, $branchId)
    {
        $branch = Branch::with([
            'company',
            'company.products' => function ($query) use ($request, $branchId) {
                $query->whereHas('itemType', function ($subQuery) {
                    $subQuery->where('show_in_cashier', true);
                })
                ->leftJoin('branch_product', function ($join) use ($branchId) {
                    $join->on('branch_product.product_id', '=', 'products.id')
                         ->where('branch_product.branch_id', '=', $branchId);
                })
                ->addSelect([
                    'products.*',
                    DB::raw('IFNULL(NULLIF(branch_product.price, 0), products.srp) as srp'),
                    DB::raw('IFNULL(NULLIF(branch_product.cost, 0), products.cost) as cost'),
                    DB::raw('IFNULL(NULLIF(branch_product.markup, 0), products.markup) as markup')
                ])
                ->with(
                    'itemType',
                    'uom',
                    'itemLocations',
                    'discounts',
                    'bundledItems',
                    'rawItems'
                )
                ->where('uom_id', '>', 0)
                ->when($request->from_date, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('products.updated_at', '>=', $request->from_date)
                              ->orWhere('products.created_at', '>=', $request->from_date);
                    });
                });
            },
        ])
        ->find($branchId);

        $products = $branch->company->products;

        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    public function productsPaginated(Request $request, $branchId)
    {
        $branch = Branch::with(['company'])->find($branchId);

        if (!$branch) {
            return $this->sendError('Branch not found.', 404);
        }

        $productsQuery = $branch->company->products()
            ->whereHas('itemType', function ($subQuery) {
                $subQuery->where('show_in_cashier', true);
            })
            ->with(
                'itemType',
                'uom',
                'itemLocations',
                'discounts',
                'bundledItems',
                'rawItems'
            )
            ->where('uom_id', '>', 0)
            ->when($request->from_date, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('updated_at', '>=', $request->from_date)
                        ->orWhere('created_at', '>=', $request->from_date);
                });
            });

        // Paginate the products
        $perPage = $request->get('per_page', 2); // Default to 15 per page if not specified
        $products = $productsQuery->paginate($perPage);

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
            'total_quantity' => ['required', 'numeric'],
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
            'company_id' => $request->company_id,
            'account_receivable_redeem_at' => $request->account_receivable_redeem_at,
            'is_account_receivable_redeem' => $request->is_account_receivable_redeem,
            'total_zero_rated_amount' => $request->total_zero_rated_amount,
            'remarks' => $request->remarks,
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
            'gross_sales' => ['required', 'numeric'],
            'net_sales' => ['required', 'numeric'],
            'vatable_sales' => ['required', 'numeric'],
            'vat_exempt_sales' => ['required', 'numeric'],
            'vat_amount' => ['required', 'numeric'],
            'discount_amount' => ['required', 'numeric'],
            'tender_amount' => ['required', 'numeric'],
            'change' => ['required', 'numeric'],
            'service_charge' => ['required', 'numeric'],
            'cashier_id' => 'required',
            'total_unit_cost' => ['required', 'numeric'],
            'total_void_amount' => ['required', 'numeric'],
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'is_account_receivable' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_complete' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'branch_id' => 'required',
            'total_quantity' => ['required', 'numeric'],
            'total_void_qty' => 'required|numeric',
            'vat_expense' => ['required', 'numeric'],
            'is_return' => 'required|boolean',
            'total_cash_amount' => ['required', 'numeric'],
            'total_return_amount' => ['required', 'numeric'],
            'void_counter' => 'required|numeric',
        ]);

        $log = new ApiRequestLog();
        $log->type = 'transaction_request';
        $log->method = $request->method();
        $log->request = json_encode($requestData);
        $log->control_number = $request->control_number;
        $log->receipt_number = $request->receipt_number;
        $log->branch_id = $request->branch_id;
        $log->save();

        if ($validator->fails()) {
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
            'total_zero_rated_amount' => $request->total_zero_rated_amount,
            'company_id' => $request->company_id,
            'is_account_receivable_redeem' => $request->is_account_receivable_redeem,
            'account_receivable_redeem_at' => $request->account_receivable_redeem_at,
            'remarks' => $request->remarks,
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

            // fill receipt_number if receipt_number is empty. exclude if already set
            if (empty($transaction->receipt_number) && !empty($request->receipt_number)) {
                $postData['receipt_number'] = $request->receipt_number;
            } else {
                unset($postData['receipt_number']);
            }

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
            'cost' => ['required', 'numeric'],
            'qty' => ['required', 'numeric'],
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'original_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'gross' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'total_cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_vatable' => 'required|boolean',
            'vat_amount' => ['required', 'numeric'],
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
            'is_zero_rated' => $request->is_zero_rated,
            'zero_rated_amount' => $request->zero_rated_amount,
            'price_change_reason_id' => $request->price_change_reason_id,
            'company_id' => $request->company_id,
            'is_free' => $request->is_free,
            'part_number' => $request->part_number,
            'is_bundle' => $request->is_bundle,
            'bundle_order_id' => $request->bundle_order_id,
            'is_posted' => $request->is_posted,
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
            'cost' => ['required', 'numeric'],
            'qty' => ['required', 'numeric'],
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
            'is_return' => 'required|boolean'
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
            'company_id' => $request->company_id,
            'price_change_reason_id' => $request->price_change_reason_id,
            'zero_rated_amount' => $request->zero_rated_amount,
            'is_free' => $request->is_free,
            'is_zero_rated' => $request->is_zero_rated,
            'part_number' => $request->part_number,
            'is_bundle' => $request->is_bundle,
            'bundle_order_id' => $request->bundle_order_id,
            'is_posted' => $request->is_posted,
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

        if ($request->has('pos_machine_id')) {
            $query->where('pos_machine_id', $request->pos_machine_id);
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
            'company_id' => $request->company_id,
            'is_account_receivable' => $request->is_account_receivable,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
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
            'company_id' => $request->company_id,
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
            'company_id' => $request->company_id,
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
            'beg_reading_number' => $request->beg_reading_number,
            'end_reading_number' => $request->end_reading_number,
            'total_zero_rated_amount' => $request->total_zero_rated_amount,
            'print_string' => $request->print_string,
            'company_id' => $request->company_id,
            'beginning_counter_amount' => $request->beginning_counter_amount,
            'ending_counter_amount' => $request->ending_counter_amount,
            'total_cash_fund' => $request->total_cash_fund,
            'beginning_gt_counter' => $request->beginning_gt_counter,
            'ending_gt_counter' => $request->ending_gt_counter,
            'beginning_cut_off_counter' => $request->beginning_cut_off_counter,
            'ending_cut_off_counter' => $request->ending_cut_off_counter,
            'total_return' => $request->total_return,
            'is_complete' => $request->is_complete,
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
        TakeOrderDiscount::where('branch_id', $request->branch_id)->delete();
        TakeOrderDiscountDetail::where('branch_id', $request->branch_id)->delete();
        TakeOrderDiscountOtherInformation::where('branch_id', $request->branch_id)->delete();

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

        $log = new ApiRequestLog();
        $log->type = 'cutoff_request';
        $log->method = $request->method();
        $log->request = json_encode($request->all());
        $log->branch_id = $request->branch_id;
        $log->save();

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
            'total_zero_rated_amount' => $request->total_zero_rated_amount,
            'print_string' => $request->print_string,
            'company_id' => $request->company_id,
            'beginning_counter_amount' => $request->beginning_counter_amount,
            'ending_counter_amount' => $request->ending_counter_amount,
            'total_cash_fund' => $request->total_cash_fund,
            'beginning_gt_counter' => $request->beginning_gt_counter,
            'ending_gt_counter' => $request->ending_gt_counter,
            'total_return' => $request->total_return,
            'is_complete' => $request->is_complete,
        ];

        $cutOff = CutOff::where([
            'cut_off_id' => $request->cut_off_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        $message = 'Cut Off created successfully.';
        if ($cutOff) {
            if (empty($cutOff->end_of_day_id) && !empty($cutOff->end_of_day_id)) {
                $postData['end_of_day_id'] = $request->end_of_day_id;
            } else {
                unset($postData['end_of_day_id']);
            }
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

    public function saveTakeOrderDiscounts(Request $request)
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
            'is_zero_rated' => $request->is_zero_rated,
            'gross_amount' => $request->gross_amount,
            'net_amount' => $request->net_amount,
            'company_id' => $request->company_id,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
        ];

        $message = 'Discount created successfully.';
        $discount = TakeOrderDiscount::where([
            'discount_id' => $request->discount_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($discount) {
            $message = 'Discount updated successfully.';
            $discount->update($postData);
            return $this->sendResponse($discount, $message);
        }

        return $this->sendResponse(TakeOrderDiscount::create($postData), $message);
    }

    public function getTakeOrderDiscounts(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = TakeOrderDiscount::where([
            'branch_id' => $request->branch_id
        ]);

        if ($request->has('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }

        if ($request->has('pos_machine_id')) {
            $query->where('pos_machine_id', $request->pos_machine_id);
        }

        $discounts = $query->get();

        return $this->sendResponse($discounts, 'Discounts retrieved successfully.');
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
            'is_zero_rated' => $request->is_zero_rated,
            'gross_amount' => $request->gross_amount,
            'net_amount' => $request->net_amount,
            'company_id' => $request->company_id,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
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

    public function saveTakeorderDiscountDetails(Request $request)
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
            'is_zero_rated' => $request->is_zero_rated,
            'company_id' => $request->company_id,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
        ];

        $message = 'Discount Details created successfully.';
        $discountDetails = TakeOrderDiscountDetail::where([
            'discount_details_id' => $request->discount_details_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($discountDetails) {
            $message = 'Discount Details updated successfully.';
            $discountDetails->update($postData);
            return $this->sendResponse($discountDetails, $message);
        }

        return $this->sendResponse(TakeOrderDiscountDetail::create($postData), $message);
    }

    public function getTakeOrderDiscountDetails(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = TakeOrderDiscountDetail::where([
                'branch_id' => $request->branch_id
            ]);

        if ($request->has('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }

        if ($request->has('pos_machine_id')) {
            $query->where('pos_machine_id', $request->pos_machine_id);
        }

        $discounts = $query->get();

        return $this->sendResponse($discounts, 'Discount details retrieved successfully.');
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
            'is_zero_rated' => $request->is_zero_rated,
            'company_id' => $request->company_id,
            'is_completed' => $request->is_completed,
            'completed_at' => $request->completed_at,
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
            'company_id' => $request->company_id,
            'is_mask' => $request->is_mask,
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

    public function saveTakeOrderDiscountOtherInformations(Request $request) 
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
            'company_id' => $request->company_id,
        ];

        $message = 'discount other informations created successfully.';
        $record = TakeOrderDiscountOtherInformation::where([
            'discount_other_information_id' => $request->discount_other_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'discount other informations updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(TakeOrderDiscountOtherInformation::create($postData), $message);
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
            'company_id' => $request->company_id,
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

    public function getTakeOrderDiscountOtherInformations(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $today = Carbon::today()->format('Y-m-d 23:59:59');
        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $query = TakeOrderDiscountOtherInformation::where([
                'branch_id' => $request->branch_id
            ]);

        if ($request->has('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }

        if ($request->has('pos_machine_id')) {
            $query->where('pos_machine_id', $request->pos_machine_id);
        }

        $records = $query->get();

        return $this->sendResponse($records, 'Discount Other Informations retrieved successfully.');
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
            'company_id' => $request->company_id,
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
            'company_id' => $request->company_id,
            'is_zero_rated' => $request->is_zero_rated,
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
            'company_id' => $request->company_id,
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
            'company_id' => $request->company_id,
            'is_zero_rated' => $request->is_zero_rated,
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
            'company_id' => $request->company_id,
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
            'name' => $request->name,
            'transaction_count' => $request->transaction_count,
            'amount' => $request->amount,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
            'company_id' => $request->company_id,
            'department_id' => $request->department_id,
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

    public function saveCashFunds(Request $request)
    {
        $validator = validator($request->all(), [
            'cash_fund_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'amount' => 'required',
            'cashier_id' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'end_of_day_id' => 'required',
            'is_sent_to_server' => 'required',
            'shift_number' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cash_fund_id' => $request->cash_fund_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'amount' => $request->amount,
            'cashier_id' => $request->cashier_id,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'end_of_day_id' => $request->end_of_day_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'shift_number' => $request->shift_number,
            'treg' => $request->treg,
            'cashier_name' => $request->cashier_name,
            'company_id' => $request->company_id,
        ];

        $message = 'Cash fund created successfully.';
        $record = CashFund::where([
            'cash_fund_id' => $request->cash_fund_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Cash fund updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(CashFund::create($postData), $message);
    }

    public function getCashFunds(Request $request)
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

        $records = CashFund::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = CashFund::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('cash_fund_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'cash fund retrieved successfully.');
    }

    public function saveCashFundDenominations(Request $request)
    {
        $validator = validator($request->all(), [
            'cash_fund_denomination_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'cash_fund_id' => 'required',
            'cash_denomination_id' => 'required',
            'amount' => 'required',
            'qty' => 'required',
            'total' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'end_of_day_id' => 'required',
            'is_sent_to_server' => 'required',
            'shift_number' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cash_fund_denomination_id' => $request->cash_fund_denomination_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'cash_fund_id' => $request->cash_fund_id,
            'cash_denomination_id' => $request->cash_denomination_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'qty' => $request->qty,
            'total' => $request->total,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'end_of_day_id' => $request->end_of_day_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'shift_number' => $request->shift_number,
            'treg' => $request->treg,
            'company_id' => $request->company_id,
        ];

        $message = 'Cash fund denomination created successfully.';
        $record = CashFundDenomination::where([
            'cash_fund_denomination_id' => $request->cash_fund_denomination_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Cash fund denomination updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(CashFundDenomination::create($postData), $message);
    }

    public function getCashFundDenominations(Request $request)
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

        $records = CashFundDenomination::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = CashFundDenomination::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('cash_fund_denomination_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'cash fund denomination retrieved successfully.');
    }

    public function saveAuditTrails(Request $request)
    {
        $validator = validator($request->all(), [
            'audit_trail_id' => 'required',
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
            'user_id' => 'required',
            'transaction_id' => 'required',
            'authorize_id' => 'required',
            'is_sent_to_server' => 'required',
            'order_id' => 'required',
            'price_change_reason_id' => 'required',
            'company_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'audit_trail_id' => $request->audit_trail_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'user_id' => $request->user_id,
            'user_name' => $request->user_name,
            'transaction_id' => $request->transaction_id,
            'action' => $request->action,
            'description' => $request->description,
            'authorize_id' => $request->authorize_id,
            'authorize_name' => $request->authorize_name,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
            'order_id' => $request->order_id,
            'price_change_reason_id' => $request->price_change_reason_id,
            'company_id' => $request->company_id,
        ];

        $message = 'Audit Trail created successfully.';
        $record = AuditTrail::where([
            'audit_trail_id' => $request->audit_trail_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Audit Trail updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(AuditTrail::create($postData), $message);
    }

    public function getAuditTrails(Request $request)
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

        $records = AuditTrail::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = AuditTrail::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('audit_trail_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'audit trail retrieved successfully.');
    }

    public function saveCutOffProducts(Request $request)
    {
        $validator = validator($request->all(), [
            'cut_off_product_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'cut_off_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required',
            'is_cut_off' => 'required',
            'end_of_day_id' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'cut_off_product_id' => $request->cut_off_product_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'company_id' => $request->company_id,
            'cut_off_id' => $request->cut_off_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_at' => $request->cut_off_at,
            'end_of_day_id' => $request->end_of_day_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'Cut off product created successfully.';
        $record = CutOffProduct::where([
            'cut_off_product_id' => $request->cut_off_product_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Cut off product updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(CutOffProduct::create($postData), $message);
    }

    public function getCutOffProducts(Request $request)
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

        $records = CutOffProduct::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = CutOffProduct::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('cut_off_product_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'cut off product retrieved successfully.');
    }

    public function savePayouts(Request $request)
    {
        $validator = validator($request->all(), [
            'payout_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'control_number' => 'required',
            'amount' => 'required',
            'reason' => 'required',
            'cashier_id' => 'required',
            'cashier_name' => 'required',
            'authorize_id' => 'required',
            'is_sent_to_server' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'treg' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'payout_id' => $request->payout_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'company_id' => $request->company_id,
            'control_number' => $request->control_number,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'authorize_id' => $request->authorize_id,
            'authorize_name' => $request->authorize_name,
            'is_sent_to_server' => $request->is_sent_to_server,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'cut_off_at' => $request->cut_off_at,
            'treg' => $request->treg,
            'safekeeping_id' => $request->safekeeping_id,
        ];

        $message = 'Payout created successfully.';
        $record = Payout::where([
            'payout_id' => $request->payout_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Payout updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(Payout::create($postData), $message);
    }

    public function getPayouts(Request $request)
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

        $records = Payout::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = Payout::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('payout_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'payout retrieved successfully.');
    }

    public function saveOfficialReceiptInformations(Request $request)
    {
        $validator = validator($request->all(), [
            'official_receipt_information_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'transaction_id' => 'required',
            'name' => 'required',
            'address' => 'required',
            'tin' => 'required',
            'business_style' => 'required',
            'is_void' => 'required',
            'is_sent_to_server' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'official_receipt_information_id' => $request->official_receipt_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'company_id' => $request->company_id,
            'transaction_id' => $request->transaction_id,
            'name' => $request->name,
            'address' => $request->address,
            'tin' => $request->tin,
            'business_style' => $request->business_style,
            'is_void' => $request->is_void,
            'void_by' => $request->void_by,
            'void_name' => $request->void_name,
            'void_at' => $request->void_at,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'Official receipt information created successfully.';
        $record = OfficialReceiptInformation::where([
            'official_receipt_information_id' => $request->official_receipt_information_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Official receipt information updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(OfficialReceiptInformation::create($postData), $message);
    }

    public function getOfficialReceiptInformations(Request $request)
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

        $records = OfficialReceiptInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = OfficialReceiptInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('official_receipt_information_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'official receipt information retrieved successfully.');
    }

    public function saveSpotAudits(Request $request)
    {
        $validator = validator($request->all(), [
            'spot_audit_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'beginning_or' => 'required',
            'ending_or' => 'required',
            'beginning_amount' => 'required',
            'ending_amount' => 'required',
            'total_transactions' => 'required',
            'gross_sales' => 'required',
            'net_sales' => 'required',
            'vatable_sales' => 'required',
            'vat_exempt_sales' => 'required',
            'vat_amount' => 'required',
            'vat_expense' => 'required',
            'void_qty' => 'required',
            'void_amount' => 'required',
            'total_change' => 'required',
            'total_payout' => 'required',
            'total_service_charge' => 'required',
            'total_discount_amount' => 'required',
            'total_cost' => 'required',
            'safekeeping_amount' => 'required',
            'safekeeping_short_over' => 'required',
            'total_sk' => 'required',
            'total_short_over' => 'required',
            'cashier_id' => 'required',
            'cashier_name' => 'required',
            'admin_id' => 'required',
            'admin_name' => 'required',
            'shift_number' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'spot_audit_id' => $request->spot_audit_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'company_id' => $request->company_id,
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
            'void_qty' => $request->void_qty,
            'void_amount' => $request->void_amount,
            'total_change' => $request->total_change,
            'total_payout' => $request->total_payout,
            'total_service_charge' => $request->total_service_charge,
            'total_discount_amount' => $request->total_discount_amount,
            'total_cost' => $request->total_cost,
            'safekeeping_amount' => $request->safekeeping_amount,
            'safekeeping_short_over' => $request->safekeeping_short_over,
            'total_sk' => $request->total_sk,
            'total_short_over' => $request->total_short_over,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name,
            'admin_id' => $request->admin_id,
            'admin_name' => $request->admin_name,
            'shift_number' => $request->shift_number,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'Spot audit created successfully.';
        $record = SpotAudit::where([
            'spot_audit_id' => $request->spot_audit_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Spot audit updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(SpotAudit::create($postData), $message);
    }

    public function getSpotAudits(Request $request)
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

        $records = SpotAudit::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = SpotAudit::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('spot_audit_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'spot audit retrieved successfully.');
    }

    public function saveSpotAuditDenominations(Request $request)
    {
        $validator = validator($request->all(), [
            'spot_audit_denomination_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'spot_audit_id' => 'required',
            'cash_denomination_id' => 'required',
            'name' => 'required',
            'amount' => 'required',
            'qty' => 'required',
            'total' => 'required',
            'is_cut_off' => 'required',
            'cut_off_id' => 'required',
            'is_sent_to_server' => 'required',
            'shift_number' => 'required',
            'treg' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'spot_audit_denomination_id' => $request->spot_audit_denomination_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'company_id' => $request->company_id,
            'spot_audit_id' => $request->spot_audit_id,
            'cash_denomination_id' => $request->cash_denomination_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'qty' => $request->qty,
            'total' => $request->total,
            'is_cut_off' => $request->is_cut_off,
            'cut_off_id' => $request->cut_off_id,
            'is_sent_to_server' => $request->is_sent_to_server,
            'shift_number' => $request->shift_number,
            'treg' => $request->treg,
        ];

        $message = 'Spot audit denomination created successfully.';
        $record = SpotAuditDenomination::where([
            'spot_audit_denomination_id' => $request->spot_audit_denomination_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'Spot audit denomination updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(SpotAuditDenomination::create($postData), $message);
    }

    public function getSpotAuditDenominations(Request $request)
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

        $records = SpotAuditDenomination::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = SpotAuditDenomination::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('spot_audit_denomination_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'spot audit denomination retrieved successfully.');
    }

    public function saveEndOfDayProducts(Request $request)
    {
        $validator = validator($request->all(), [
            'end_of_day_product_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'end_of_day_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postData = [
            'end_of_day_product_id' => $request->end_of_day_product_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
            'company_id' => $request->company_id,
            'end_of_day_id' => $request->end_of_day_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'is_sent_to_server' => $request->is_sent_to_server,
            'treg' => $request->treg,
        ];

        $message = 'End of day product created successfully.';
        $record = EndOfDayProduct::where([
            'end_of_day_product_id' => $request->end_of_day_product_id,
            'pos_machine_id' => $request->pos_machine_id,
            'branch_id' => $request->branch_id,
        ])->first();

        if ($record) {
            $message = 'End of day product updated successfully.';
            $record->update($postData);
            return $this->sendResponse($record, $message);
        }

        return $this->sendResponse(EndOfDayProduct::create($postData), $message);
    }

    public function getEndOfDayProducts(Request $request)
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

        $records = EndOfDayProduct::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->whereBetween('treg', [$yesterday, $today])
            ->get();

        if ($records->count() == 0) {
            $records = EndOfDayProduct::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
            ])
            ->orderBy('end_of_day_product_id', 'desc')
            ->limit(2)
            ->get();
        }

        return $this->sendResponse($records, 'end of day product retrieved successfully.');
    }

    public function getProductSoh(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $product = Product::findOrFail($request->product_id);

        $pivotData = $product->branches->where('id', $request->branch_id)->first()?->pivot;

        $response = [
            'product_id' => $product->id,
            'name' => $product->name,
            'soh' => $pivotData->stock ?? 0,
        ];

        return $this->sendResponse($response, 'product retrieved successfully.');
    }

    public function getUnredeemedArTransactions(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $transactions = Transaction::where([
                'branch_id' => $request->branch_id,
                'is_account_receivable' => true,
                'is_account_receivable_redeem' => false,
                'is_void' => false,
            ])
            ->get();

        foreach ($transactions as $transaction) {
            $transaction->items;
            $transaction->discounts;
            $transaction->discountDetails;
            $transaction->payments;
            $transaction->paymentOtherInformations;
            $transaction->officialReceiptInformations;
            $transaction->discountOtherInformation;
        }

        return $this->sendResponse($transactions, 'Transactions retrieved successfully.');
    }

    public function updateArTransaction(Request $request)
    {
        $validator = validator($request->all(), [
            'branch_id' => 'required',
            'transaction_id' => 'required',
            'pos_machine_id' => 'required',
            'is_void' => 'required',
            'void_by' => 'required',
            'void_at' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $transaction = Transaction::where([
            'branch_id' => $request->branch_id,
            'transaction_id' => $request->transaction_id,
            'pos_machine_id' => $request->pos_machine_id,
        ])
        ->first();

        if (!$transaction) {
            return $this->sendError('Transaction not found.', [], 404);
        }

        $transaction->update([
            'is_void' => $request->is_void,
            'void_by' => $request->void_by,
            'void_at' => $request->void_at,
        ]);

        return $this->sendResponse($transaction, 'Transaction updated successfully.');
    }
}