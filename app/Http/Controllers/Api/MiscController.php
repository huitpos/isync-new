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

        $request->merge(['return_data' => filter_var($request->return_data ?? true, FILTER_VALIDATE_BOOLEAN)]);
        
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
        $perPage = $request->get('per_page', 500); // Default to 15 per page if not specified
        $products = $productsQuery->paginate($perPage);

        if (!$request->return_data) {
            // Return the pagination information without the 'data'
            return $this->sendResponse([
                'current_page' => $products->currentPage(),
                'from' => $products->firstItem(),
                'last_page' => $products->lastPage(),
                'links' => $products->links(),
                'next_page_url' => $products->nextPageUrl(),
                'per_page' => $products->perPage(),
                'prev_page_url' => $products->previousPageUrl(),
                'to' => $products->lastItem(),
                'total' => $products->total(),
            ], 'Data not returned because return_data is false.');
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of transactions
                $data = $requestData['data'];
            } else {
                // If it's a single transaction object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single transaction object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of transactions (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }

        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $tx) {
                $validator = validator($tx, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $tx;
                    continue;
                }

                $postData = [
                    'transaction_id' => $tx['transaction_id'] ?? null,
                    'pos_machine_id' => $tx['pos_machine_id'] ?? null,
                    'control_number' => $tx['control_number'] ?? null,
                    'receipt_number' => $tx['receipt_number'] ?? null,
                    'gross_sales' => $tx['gross_sales'] ?? null,
                    'net_sales' => $tx['net_sales'] ?? null,
                    'vatable_sales' => $tx['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $tx['vat_exempt_sales'] ?? null,
                    'vat_amount' => $tx['vat_amount'] ?? null,
                    'discount_amount' => $tx['discount_amount'] ?? null,
                    'tender_amount' => $tx['tender_amount'] ?? null,
                    'change' => $tx['change'] ?? null,
                    'service_charge' => $tx['service_charge'] ?? null,
                    'type' => $tx['type'] ?? null,
                    'cashier_id' => $tx['cashier_id'] ?? null,
                    'cashier_name' => $tx['cashier_name'] ?? null,
                    'take_order_id' => $tx['take_order_id'] ?? null,
                    'take_order_name' => $tx['take_order_name'] ?? null,
                    'total_unit_cost' => $tx['total_unit_cost'] ?? null,
                    'total_void_amount' => $tx['total_void_amount'] ?? null,
                    'shift_number' => $tx['shift_number'] ?? null,
                    'is_void' => $tx['is_void'] ?? null,
                    'void_by_id' => $tx['void_by_id'] ?? null,
                    'void_by' => $tx['void_by'] ?? null,
                    'void_at' => $tx['void_at'] ?? null,
                    'is_back_out' => $tx['is_back_out'] ?? null,
                    'is_back_out_id' => $tx['is_back_out_id'] ?? null,
                    'back_out_by' => $tx['back_out_by'] ?? null,
                    'charge_account_id' => $tx['charge_account_id'] ?? null,
                    'charge_account_name' => $tx['charge_account_name'] ?? null,
                    'is_account_receivable' => $tx['is_account_receivable'] ?? null,
                    'is_sent_to_server' => $tx['is_sent_to_server'] ?? null,
                    'is_complete' => $tx['is_complete'] ?? null,
                    'completed_at' => $tx['completed_at'] ?? null,
                    'is_cut_off' => $tx['is_cut_off'] ?? null,
                    'cut_off_id' => $tx['cut_off_id'] ?? null,
                    'cut_off_at' => $tx['cut_off_at'] ?? null,
                    'branch_id' => $tx['branch_id'] ?? null,
                    'guest_name' => $tx['guest_name'] ?? null,
                    'is_resume_printed' => $tx['is_resume_printed'] ?? false,
                    'treg' => $tx['treg'] ?? null,
                    'backout_at' => $tx['backout_at'] ?? null,
                    'total_quantity' => $tx['total_quantity'] ?? null,
                    'total_void_qty' => $tx['total_void_qty'] ?? null,
                    'vat_expense' => $tx['vat_expense'] ?? null,
                    'is_return' => $tx['is_return'] ?? null,
                    'total_cash_amount' => $tx['total_cash_amount'] ?? null,
                    'total_return_amount' => $tx['total_return_amount'] ?? null,
                    'void_counter' => $tx['void_counter'] ?? null,
                    'void_remarks' => $tx['void_remarks'] ?? null,
                    'customer_name' => $tx['customer_name'] ?? null,
                    'total_zero_rated_amount' => $tx['total_zero_rated_amount'] ?? null,
                    'company_id' => $tx['company_id'] ?? null,
                    'is_account_receivable_redeem' => $tx['is_account_receivable_redeem'] ?? null,
                    'account_receivable_redeem_at' => $tx['account_receivable_redeem_at'] ?? null,
                    'remarks' => $tx['remarks'] ?? null,
                ];

                $transaction = Transaction::where([
                    'transaction_id' => $tx['transaction_id'],
                    'pos_machine_id' => $tx['pos_machine_id'],
                    'branch_id' => $tx['branch_id'],
                ])->first();

                if ($transaction) {
                    if (empty($transaction->receipt_number) && !empty($tx['receipt_number'])) {
                        $postData['receipt_number'] = $tx['receipt_number'];
                    } else {
                        unset($postData['receipt_number']);
                    }
                    $toUpdate[] = [
                        'model' => $transaction,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                Transaction::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Transactions processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of orders
                $data = $requestData['data'];
            } else {
                // If it's a single order object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single order object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of orders (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $order) {
                $validator = validator($order, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $order;
                    continue;
                }

                $postData = [
                    'order_id' => $order['order_id'] ?? null,
                    'pos_machine_id' => $order['pos_machine_id'] ?? null,
                    'transaction_id' => $order['transaction_id'] ?? null,
                    'product_id' => $order['product_id'] ?? null,
                    'code' => $order['code'] ?? null,
                    'name' => $order['name'] ?? null,
                    'description' => $order['description'] ?? null,
                    'abbreviation' => $order['abbreviation'] ?? null,
                    'cost' => $order['cost'] ?? null,
                    'qty' => $order['qty'] ?? null,
                    'amount' => $order['amount'] ?? null,
                    'original_amount' => $order['original_amount'] ?? null,
                    'gross' => $order['gross'] ?? null,
                    'total' => $order['total'] ?? null,
                    'total_cost' => $order['total_cost'] ?? null,
                    'is_vatable' => $order['is_vatable'] ?? null,
                    'vat_amount' => $order['vat_amount'] ?? null,
                    'vatable_sales' => $order['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $order['vat_exempt_sales'] ?? null,
                    'discount_amount' => $order['discount_amount'] ?? null,
                    'department_id' => $order['department_id'] ?? null,
                    'department_name' => $order['department_name'] ?? null,
                    'category_id' => $order['category_id'] ?? null,
                    'category_name' => $order['category_name'] ?? null,
                    'subcategory_id' => $order['subcategory_id'] ?? null,
                    'subcategory_name' => $order['subcategory_name'] ?? null,
                    'unit_id' => $order['unit_id'] ?? null,
                    'unit_name' => $order['unit_name'] ?? null,
                    'is_void' => $order['is_void'] ?? null,
                    'void_by' => $order['void_by'] ?? null,
                    'void_at' => $order['void_at'] ?? null,
                    'is_back_out' => $order['is_back_out'] ?? null,
                    'is_back_out_id' => $order['is_back_out_id'] ?? null,
                    'back_out_by' => $order['back_out_by'] ?? null,
                    'min_amount_sold' => $order['min_amount_sold'] ?? null,
                    'is_paid' => $order['is_paid'] ?? null,
                    'is_sent_to_server' => $order['is_sent_to_server'] ?? null,
                    'is_completed' => $order['is_completed'] ?? null,
                    'completed_at' => $order['completed_at'] ?? null,
                    'branch_id' => $order['branch_id'] ?? null,
                    'shift_number' => $order['shift_number'] ?? null,
                    'is_cut_off' => $order['is_cut_off'] ?? null,
                    'cut_off_id' => $order['cut_off_id'] ?? null,
                    'cut_off_at' => $order['cut_off_at'] ?? null,
                    'discount_details_id' => $order['discount_details_id'] ?? null,
                    'treg' => $order['treg'] ?? null,
                    'is_discount_exempt' => $order['is_discount_exempt'] ?? null,
                    'is_open_price' => $order['is_open_price'] ?? null,
                    'remarks' => $order['remarks'] ?? null,
                    'vat_expense' => $order['vat_expense'] ?? null,
                    'with_serial' => $order['with_serial'] ?? null,
                    'is_return' => $order['is_return'] ?? null,
                    'serial_number' => $order['serial_number'] ?? null,
                    'is_zero_rated' => $order['is_zero_rated'] ?? null,
                    'zero_rated_amount' => $order['zero_rated_amount'] ?? null,
                    'price_change_reason_id' => $order['price_change_reason_id'] ?? null,
                    'company_id' => $order['company_id'] ?? null,
                    'is_free' => $order['is_free'] ?? null,
                    'part_number' => $order['part_number'] ?? null,
                    'is_bundle' => $order['is_bundle'] ?? null,
                    'bundle_order_id' => $order['bundle_order_id'] ?? null,
                    'is_posted' => $order['is_posted'] ?? null,
                ];

                $existingOrder = Order::where([
                    'order_id' => $order['order_id'],
                    'pos_machine_id' => $order['pos_machine_id'],
                    'branch_id' => $order['branch_id'],
                ])->first();

                if ($existingOrder) {
                    $toUpdate[] = [
                        'model' => $existingOrder,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                Order::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Orders processed successfully.');
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
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = TakeOrderOrder::where([
            'branch_id' => $request->branch_id,
            'is_completed' => false,
            'pos_machine_id' => $request->pos_machine_id,
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of payments
                $data = $requestData['data'];
            } else {
                // If it's a single payment object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single payment object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of payments (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
            'payment_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'transaction_id' => 'required',
            'payment_type_id' => 'required',
            'amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'is_advance_payment' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'is_void' => 'required|boolean',
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $payment) {
                $validator = validator($payment, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $payment;
                    continue;
                }

                $postData = [
                    'payment_id' => $payment['payment_id'] ?? null,
                    'pos_machine_id' => $payment['pos_machine_id'] ?? null,
                    'branch_id' => $payment['branch_id'] ?? null,
                    'transaction_id' => $payment['transaction_id'] ?? null,
                    'payment_type_id' => $payment['payment_type_id'] ?? null,
                    'payment_type_name' => $payment['payment_type_name'] ?? null,
                    'amount' => $payment['amount'] ?? null,
                    'is_advance_payment' => $payment['is_advance_payment'] ?? null,
                    'shift_number' => $payment['shift_number'] ?? null,
                    'is_sent_to_server' => $payment['is_sent_to_server'] ?? null,
                    'is_cut_off' => $payment['is_cut_off'] ?? null,
                    'cut_off_id' => $payment['cut_off_id'] ?? null,
                    'cut_off_at' => $payment['cut_off_at'] ?? null,
                    'treg' => $payment['treg'] ?? null,
                    'is_void' => $payment['is_void'] ?? null,
                    'void_at' => $payment['void_at'] ?? null,
                    'void_by' => $payment['void_by'] ?? null,
                    'void_by_id' => $payment['void_by_id'] ?? null,
                    'company_id' => $payment['company_id'] ?? null,
                    'is_account_receivable' => $payment['is_account_receivable'] ?? null,
                    'is_completed' => $payment['is_completed'] ?? null,
                    'completed_at' => $payment['completed_at'] ?? null,
                ];

                $existingPayment = Payment::where([
                    'payment_id' => $payment['payment_id'],
                    'pos_machine_id' => $payment['pos_machine_id'],
                    'branch_id' => $payment['branch_id'],
                ])->first();

                if ($existingPayment) {
                    $toUpdate[] = [
                        'model' => $existingPayment,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                Payment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Payments processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of safekeepings
                $data = $requestData['data'];
            } else {
                // If it's a single safekeeping object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single safekeeping object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of safekeepings (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $sk) {
                $validator = validator($sk, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $sk;
                    continue;
                }

                $postData = [
                    'safekeeping_id' => $sk['safekeeping_id'] ?? null,
                    'pos_machine_id' => $sk['pos_machine_id'] ?? null,
                    'branch_id' => $sk['branch_id'] ?? null,
                    'amount' => $sk['amount'] ?? null,
                    'cashier_id' => $sk['cashier_id'] ?? null,
                    'cashier_name' => $sk['cashier_name'] ?? null,
                    'authorize_id' => $sk['authorize_id'] ?? null,
                    'authorize_name' => $sk['authorize_name'] ?? null,
                    'is_cut_off' => $sk['is_cut_off'] ?? null,
                    'cut_off_id' => $sk['cut_off_id'] ?? null,
                    'is_sent_to_server' => $sk['is_sent_to_server'] ?? null,
                    'shift_number' => $sk['shift_number'] ?? null,
                    'treg' => $sk['treg'] ?? null,
                    'end_of_day_id' => $sk['end_of_day_id'] ?? null,
                    'is_auto' => $sk['is_auto'] ?? null,
                    'short_over' => $sk['short_over'] ?? null,
                    'company_id' => $sk['company_id'] ?? null,
                ];

                $safekeeping = Safekeeping::where([
                    'safekeeping_id' => $sk['safekeeping_id'],
                    'pos_machine_id' => $sk['pos_machine_id'],
                    'branch_id' => $sk['branch_id'],
                ])->first();

                if ($safekeeping) {
                    $toUpdate[] = [
                        'model' => $safekeeping,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                Safekeeping::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Safekeepings processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of safekeeping denominations
                $data = $requestData['data'];
            } else {
                // If it's a single safekeeping denomination object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single safekeeping denomination object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of safekeeping denominations (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $skd) {
                $validator = validator($skd, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $skd;
                    continue;
                }

                $postData = [
                    'branch_id' => $skd['branch_id'] ?? null,
                    'pos_machine_id' => $skd['pos_machine_id'] ?? null,
                    'safekeeping_denomination_id' => $skd['safekeeping_denomination_id'] ?? null,
                    'safekeeping_id' => $skd['safekeeping_id'] ?? null,
                    'cash_denomination_id' => $skd['cash_denomination_id'] ?? null,
                    'name' => $skd['name'] ?? null,
                    'amount' => $skd['amount'] ?? null,
                    'qty' => $skd['qty'] ?? null,
                    'total' => $skd['total'] ?? null,
                    'shift_number' => $skd['shift_number'] ?? null,
                    'cut_off_id' => $skd['cut_off_id'] ?? null,
                    'treg' => $skd['treg'] ?? null,
                    'end_of_day_id' => $skd['end_of_day_id'] ?? null,
                    'is_cut_off' => $skd['is_cut_off'] ?? null,
                    'is_sent_to_server' => $skd['is_sent_to_server'] ?? null,
                    'company_id' => $skd['company_id'] ?? null,
                ];

                $safekeepingDenomination = SafekeepingDenomination::where([
                    'safekeeping_denomination_id' => $skd['safekeeping_denomination_id'],
                    'safekeeping_id' => $skd['safekeeping_id'],
                    'cash_denomination_id' => $skd['cash_denomination_id'],
                ])->first();

                if ($safekeepingDenomination) {
                    $toUpdate[] = [
                        'model' => $safekeepingDenomination,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                SafekeepingDenomination::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Safekeeping Denominations processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $eod) {
                $validator = validator($eod, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $eod;
                    continue;
                }

                $branch = Branch::find($eod['branch_id']);

                $postData = [
                    'end_of_day_id' => $eod['end_of_day_id'] ?? null,
                    'pos_machine_id' => $eod['pos_machine_id'] ?? null,
                    'branch_id' => $eod['branch_id'] ?? null,
                    'beginning_or' => $eod['beginning_or'] ?? null,
                    'ending_or' => $eod['ending_or'] ?? null,
                    'beginning_amount' => $eod['beginning_amount'] ?? null,
                    'ending_amount' => $eod['ending_amount'] ?? null,
                    'total_transactions' => $eod['total_transactions'] ?? null,
                    'gross_sales' => $eod['gross_sales'] ?? null,
                    'net_sales' => $eod['net_sales'] ?? null,
                    'vatable_sales' => $eod['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $eod['vat_exempt_sales'] ?? null,
                    'vat_amount' => $eod['vat_amount'] ?? null,
                    'vat_expense' => $eod['vat_expense'] ?? null,
                    'void_amount' => $eod['void_amount'] ?? null,
                    'total_change' => $eod['total_change'] ?? null,
                    'total_payout' => $eod['total_payout'] ?? null,
                    'total_service_charge' => $eod['total_service_charge'] ?? null,
                    'total_discount_amount' => $eod['total_discount_amount'] ?? null,
                    'total_cost' => $eod['total_cost'] ?? null,
                    'total_sk' => $eod['total_sk'] ?? null,
                    'cashier_id' => $eod['cashier_id'] ?? null,
                    'cashier_name' => $eod['cashier_name'] ?? null,
                    'admin_id' => $eod['admin_id'] ?? null,
                    'admin_name' => $eod['admin_name'] ?? null,
                    'shift_number' => $eod['shift_number'] ?? null,
                    'is_sent_to_server' => $eod['is_sent_to_server'] ?? null,
                    'treg' => $eod['treg'] ?? null,
                    'reading_number' => $eod['reading_number'] ?? null,
                    'void_qty' => $eod['void_qty'] ?? null,
                    'total_short_over' => $eod['total_short_over'] ?? null,
                    'generated_date' => $eod['generated_date'] ?? null,
                    'beg_reading_number' => $eod['beg_reading_number'] ?? null,
                    'end_reading_number' => $eod['end_reading_number'] ?? null,
                    'total_zero_rated_amount' => $eod['total_zero_rated_amount'] ?? null,
                    'print_string' => $eod['print_string'] ?? null,
                    'company_id' => $eod['company_id'] ?? null,
                    'beginning_counter_amount' => $eod['beginning_counter_amount'] ?? null,
                    'ending_counter_amount' => $eod['ending_counter_amount'] ?? null,
                    'total_cash_fund' => $eod['total_cash_fund'] ?? null,
                    'beginning_gt_counter' => $eod['beginning_gt_counter'] ?? null,
                    'ending_gt_counter' => $eod['ending_gt_counter'] ?? null,
                    'beginning_cut_off_counter' => $eod['beginning_cut_off_counter'] ?? null,
                    'ending_cut_off_counter' => $eod['ending_cut_off_counter'] ?? null,
                    'total_return' => $eod['total_return'] ?? null,
                    'is_complete' => $eod['is_complete'] ?? null,
                ];

                if (!empty($eod['products']) && $branch) {
                    foreach ($eod['products'] as $reqProduct) {
                        $product = Product::find($reqProduct['productId']);
                        if ($product) {
                            $this->productRepository->updateBranchQuantity($product, $branch, $reqProduct['endOfDayId'], 'end_of_days', $reqProduct['qty'], null, 'subtract', $product->uom_id);
                        }
                    }
                }

                $endOfDay = EndOfDay::where([
                    'end_of_day_id' => $eod['end_of_day_id'],
                    'pos_machine_id' => $eod['pos_machine_id'],
                    'branch_id' => $eod['branch_id'],
                ])->first();

                TakeOrderTransaction::where('branch_id', $eod['branch_id'])->delete();
                TakeOrderOrder::where('branch_id', $eod['branch_id'])->delete();
                TakeOrderDiscount::where('branch_id', $eod['branch_id'])->delete();
                TakeOrderDiscountDetail::where('branch_id', $eod['branch_id'])->delete();
                TakeOrderDiscountOtherInformation::where('branch_id', $eod['branch_id'])->delete();

                if ($endOfDay) {
                    $endOfDay->update($postData);
                } else {
                    EndOfDay::create($postData);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'End Of Days processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $cutoff) {
                $validator = validator($cutoff, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $cutoff;
                    continue;
                }

                $postData = [
                    'cut_off_id' => $cutoff['cut_off_id'] ?? null,
                    'end_of_day_id' => $cutoff['end_of_day_id'] ?? null,
                    'pos_machine_id' => $cutoff['pos_machine_id'] ?? null,
                    'branch_id' => $cutoff['branch_id'] ?? null,
                    'beginning_or' => $cutoff['beginning_or'] ?? null,
                    'ending_or' => $cutoff['ending_or'] ?? null,
                    'beginning_amount' => $cutoff['beginning_amount'] ?? null,
                    'ending_amount' => $cutoff['ending_amount'] ?? null,
                    'total_transactions' => $cutoff['total_transactions'] ?? null,
                    'gross_sales' => $cutoff['gross_sales'] ?? null,
                    'net_sales' => $cutoff['net_sales'] ?? null,
                    'vatable_sales' => $cutoff['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $cutoff['vat_exempt_sales'] ?? null,
                    'vat_amount' => $cutoff['vat_amount'] ?? null,
                    'vat_expense' => $cutoff['vat_expense'] ?? null,
                    'void_amount' => $cutoff['void_amount'] ?? null,
                    'total_change' => $cutoff['total_change'] ?? null,
                    'total_payout' => $cutoff['total_payout'] ?? null,
                    'total_service_charge' => $cutoff['total_service_charge'] ?? null,
                    'total_discount_amount' => $cutoff['total_discount_amount'] ?? null,
                    'total_cost' => $cutoff['total_cost'] ?? null,
                    'total_sk' => $cutoff['total_sk'] ?? null,
                    'cashier_id' => $cutoff['cashier_id'] ?? null,
                    'cashier_name' => $cutoff['cashier_name'] ?? null,
                    'admin_id' => $cutoff['admin_id'] ?? null,
                    'admin_name' => $cutoff['admin_name'] ?? null,
                    'shift_number' => $cutoff['shift_number'] ?? null,
                    'is_sent_to_server' => $cutoff['is_sent_to_server'] ?? null,
                    'treg' => $cutoff['treg'] ?? null,
                    'reading_number' => $cutoff['reading_number'] ?? null,
                    'void_qty' => $cutoff['void_qty'] ?? null,
                    'total_short_over' => $cutoff['total_short_over'] ?? null,
                    'total_zero_rated_amount' => $cutoff['total_zero_rated_amount'] ?? null,
                    'print_string' => $cutoff['print_string'] ?? null,
                    'company_id' => $cutoff['company_id'] ?? null,
                    'beginning_counter_amount' => $cutoff['beginning_counter_amount'] ?? null,
                    'ending_counter_amount' => $cutoff['ending_counter_amount'] ?? null,
                    'total_cash_fund' => $cutoff['total_cash_fund'] ?? null,
                    'beginning_gt_counter' => $cutoff['beginning_gt_counter'] ?? null,
                    'ending_gt_counter' => $cutoff['ending_gt_counter'] ?? null,
                    'total_return' => $cutoff['total_return'] ?? null,
                    'is_complete' => $cutoff['is_complete'] ?? null,
                ];

                $cutOff = CutOff::where([
                    'cut_off_id' => $cutoff['cut_off_id'],
                    'pos_machine_id' => $cutoff['pos_machine_id'],
                    'branch_id' => $cutoff['branch_id'],
                ])->first();

                if ($cutOff) {
                    $toUpdate[] = [
                        'model' => $cutOff,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CutOff::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cut Offs processed successfully.');
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
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = TakeOrderDiscount::where([
            'branch_id' => $request->branch_id,
            'pos_machine_id' => $request->pos_machine_id
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }

        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $discount) {
                $validator = validator($discount, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $discount;
                    continue;
                }

                $postData = [
                    'discount_id' => $discount['discount_id'] ?? null,
                    'pos_machine_id' => $discount['pos_machine_id'] ?? null,
                    'branch_id' => $discount['branch_id'] ?? null,
                    'transaction_id' => $discount['transaction_id'] ?? null,
                    'custom_discount_id' => $discount['custom_discount_id'] ?? null,
                    'discount_type_id' => $discount['discount_type_id'] ?? null,
                    'discount_name' => $discount['discount_name'] ?? null,
                    'value' => $discount['value'] ?? null,
                    'discount_amount' => $discount['discount_amount'] ?? null,
                    'vat_exempt_amount' => $discount['vat_exempt_amount'] ?? null,
                    'type' => $discount['type'] ?? null,
                    'cashier_id' => $discount['cashier_id'] ?? null,
                    'cashier_name' => $discount['cashier_name'] ?? null,
                    'authorize_id' => $discount['authorize_id'] ?? null,
                    'authorize_name' => $discount['authorize_name'] ?? null,
                    'is_void' => $discount['is_void'] ?? null,
                    'void_by_id' => $discount['void_by_id'] ?? null,
                    'void_by' => $discount['void_by'] ?? null,
                    'void_at' => $discount['void_at'] ?? null,
                    'is_sent_to_server' => $discount['is_sent_to_server'] ?? null,
                    'is_cut_off' => $discount['is_cut_off'] ?? null,
                    'cut_off_id' => $discount['cut_off_id'] ?? null,
                    'shift_number' => $discount['shift_number'] ?? null,
                    'treg' => $discount['treg'] ?? null,
                    'vat_expense' => $discount['vat_expense'] ?? null,
                    'is_zero_rated' => $discount['is_zero_rated'] ?? null,
                    'gross_amount' => $discount['gross_amount'] ?? null,
                    'net_amount' => $discount['net_amount'] ?? null,
                    'company_id' => $discount['company_id'] ?? null,
                    'is_completed' => $discount['is_completed'] ?? null,
                    'completed_at' => $discount['completed_at'] ?? null,
                ];

                $existingDiscount = Discount::where([
                    'discount_id' => $discount['discount_id'],
                    'pos_machine_id' => $discount['pos_machine_id'],
                    'branch_id' => $discount['branch_id'],
                ])->first();

                if ($existingDiscount) {
                    $toUpdate[] = [
                        'model' => $existingDiscount,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                Discount::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Discounts processed successfully.');
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
            'branch_id' => 'required',
            'pos_machine_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = TakeOrderDiscountDetail::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $detail) {
                $validator = validator($detail, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $detail;
                    continue;
                }

                $postData = [
                    'discount_details_id' => $detail['discount_details_id'] ?? null,
                    'discount_id' => $detail['discount_id'] ?? null,
                    'pos_machine_id' => $detail['pos_machine_id'] ?? null,
                    'branch_id' => $detail['branch_id'] ?? null,
                    'custom_discount_id' => $detail['custom_discount_id'] ?? null,
                    'transaction_id' => $detail['transaction_id'] ?? null,
                    'order_id' => $detail['order_id'] ?? null,
                    'discount_type_id' => $detail['discount_type_id'] ?? null,
                    'value' => $detail['value'] ?? null,
                    'discount_amount' => $detail['discount_amount'] ?? null,
                    'vat_exempt_amount' => $detail['vat_exempt_amount'] ?? null,
                    'type' => $detail['type'] ?? null,
                    'is_void' => $detail['is_void'] ?? null,
                    'void_by_id' => $detail['void_by_id'] ?? null,
                    'void_by' => $detail['void_by'] ?? null,
                    'void_at' => $detail['void_at'] ?? null,
                    'is_sent_to_server' => $detail['is_sent_to_server'] ?? null,
                    'is_cut_off' => $detail['is_cut_off'] ?? null,
                    'cut_off_id' => $detail['cut_off_id'] ?? null,
                    'is_vat_exempt' => $detail['is_vat_exempt'] ?? null,
                    'shift_number' => $detail['shift_number'] ?? null,
                    'treg' => $detail['treg'] ?? null,
                    'vat_expense' => $detail['vat_expense'] ?? null,
                    'is_zero_rated' => $detail['is_zero_rated'] ?? null,
                    'company_id' => $detail['company_id'] ?? null,
                    'is_completed' => $detail['is_completed'] ?? null,
                    'completed_at' => $detail['completed_at'] ?? null,
                ];

                $discountDetails = DiscountDetail::where([
                    'discount_details_id' => $detail['discount_details_id'],
                    'pos_machine_id' => $detail['pos_machine_id'],
                    'branch_id' => $detail['branch_id'],
                ])->first();

                if ($discountDetails) {
                    $toUpdate[] = [
                        'model' => $discountDetails,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                DiscountDetail::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Discount Details processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $info) {
                $validator = validator($info, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $info;
                    continue;
                }

                $postData = [
                    'payment_other_information_id' => $info['payment_other_information_id'] ?? null,
                    'pos_machine_id' => $info['pos_machine_id'] ?? null,
                    'branch_id' => $info['branch_id'] ?? null,
                    'transaction_id' => $info['transaction_id'] ?? null,
                    'payment_id' => $info['payment_id'] ?? null,
                    'name' => $info['name'] ?? null,
                    'value' => $info['value'] ?? null,
                    'is_cut_off' => $info['is_cut_off'] ?? null,
                    'cut_off_id' => $info['cut_off_id'] ?? null,
                    'is_void' => $info['is_void'] ?? null,
                    'is_sent_to_server' => $info['is_sent_to_server'] ?? null,
                    'treg' => $info['treg'] ?? null,
                    'company_id' => $info['company_id'] ?? null,
                    'is_mask' => $info['is_mask'] ?? null,
                ];

                $record = PaymentOtherInformation::where([
                    'payment_other_information_id' => $info['payment_other_information_id'],
                    'pos_machine_id' => $info['pos_machine_id'],
                    'branch_id' => $info['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                PaymentOtherInformation::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Payment Other Informations processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $info) {
                $validator = validator($info, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $info;
                    continue;
                }

                $postData = [
                    'discount_other_information_id' => $info['discount_other_information_id'] ?? null,
                    'pos_machine_id' => $info['pos_machine_id'] ?? null,
                    'branch_id' => $info['branch_id'] ?? null,
                    'transaction_id' => $info['transaction_id'] ?? null,
                    'discount_id' => $info['discount_id'] ?? null,
                    'name' => $info['name'] ?? null,
                    'value' => $info['value'] ?? null,
                    'is_cut_off' => $info['is_cut_off'] ?? null,
                    'cut_off_id' => $info['cut_off_id'] ?? null,
                    'is_void' => $info['is_void'] ?? null,
                    'is_sent_to_server' => $info['is_sent_to_server'] ?? null,
                    'treg' => $info['treg'] ?? null,
                    'company_id' => $info['company_id'] ?? null,
                ];

                $record = DiscountOtherInformation::where([
                    'discount_other_information_id' => $info['discount_other_information_id'],
                    'pos_machine_id' => $info['pos_machine_id'],
                    'branch_id' => $info['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                DiscountOtherInformation::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Discount Other Informations processed successfully.');
    }

    public function getTakeOrderDiscountOtherInformations(Request $request)
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

        $query = TakeOrderDiscountOtherInformation::where([
                'branch_id' => $request->branch_id,
                'pos_machine_id' => $request->pos_machine_id
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $dept) {
                $validator = validator($dept, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $dept;
                    continue;
                }

                $postData = [
                    'cut_off_department_id' => $dept['cut_off_department_id'] ?? null,
                    'pos_machine_id' => $dept['pos_machine_id'] ?? null,
                    'branch_id' => $dept['branch_id'] ?? null,
                    'is_cut_off' => $dept['is_cut_off'] ?? null,
                    'cut_off_id' => $dept['cut_off_id'] ?? null,
                    'department_id' => $dept['department_id'] ?? null,
                    'name' => $dept['name'] ?? null,
                    'transaction_count' => $dept['transaction_count'] ?? null,
                    'amount' => $dept['amount'] ?? null,
                    'end_of_day_id' => $dept['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $dept['is_sent_to_server'] ?? null,
                    'treg' => $dept['treg'] ?? null,
                    'company_id' => $dept['company_id'] ?? null,
                ];

                $record = CutOffDepartment::where([
                    'cut_off_department_id' => $dept['cut_off_department_id'],
                    'pos_machine_id' => $dept['pos_machine_id'],
                    'branch_id' => $dept['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CutOffDepartment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cut Off Departments processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $discount) {
                $validator = validator($discount, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $discount;
                    continue;
                }

                $postData = [
                    'cut_off_discount_id' => $discount['cut_off_discount_id'] ?? null,
                    'pos_machine_id' => $discount['pos_machine_id'] ?? null,
                    'branch_id' => $discount['branch_id'] ?? null,
                    'cut_off_id' => $discount['cut_off_id'] ?? null,
                    'discount_type_id' => $discount['discount_type_id'] ?? null,
                    'name' => $discount['name'] ?? null,
                    'transaction_count' => $discount['transaction_count'] ?? null,
                    'amount' => $discount['amount'] ?? null,
                    'end_of_day_id' => $discount['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $discount['is_sent_to_server'] ?? null,
                    'treg' => $discount['treg'] ?? null,
                    'is_cut_off' => $discount['is_cut_off'] ?? null,
                    'company_id' => $discount['company_id'] ?? null,
                    'is_zero_rated' => $discount['is_zero_rated'] ?? null,
                ];

                $record = CutOffDiscount::where([
                    'cut_off_discount_id' => $discount['cut_off_discount_id'],
                    'pos_machine_id' => $discount['pos_machine_id'],
                    'branch_id' => $discount['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CutOffDiscount::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cut Off Discounts processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $payment) {
                $validator = validator($payment, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $payment;
                    continue;
                }

                $postData = [
                    'cut_off_payment_id' => $payment['cut_off_payment_id'] ?? null,
                    'pos_machine_id' => $payment['pos_machine_id'] ?? null,
                    'branch_id' => $payment['branch_id'] ?? null,
                    'cut_off_id' => $payment['cut_off_id'] ?? null,
                    'payment_type_id' => $payment['payment_type_id'] ?? null,
                    'name' => $payment['name'] ?? null,
                    'transaction_count' => $payment['transaction_count'] ?? null,
                    'amount' => $payment['amount'] ?? null,
                    'end_of_day_id' => $payment['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $payment['is_sent_to_server'] ?? null,
                    'treg' => $payment['treg'] ?? null,
                    'is_cut_off' => $payment['is_cut_off'] ?? null,
                    'company_id' => $payment['company_id'] ?? null,
                ];

                $record = CutOffPayment::where([
                    'cut_off_payment_id' => $payment['cut_off_payment_id'],
                    'pos_machine_id' => $payment['pos_machine_id'],
                    'branch_id' => $payment['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CutOffPayment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cut Off Payments processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $discount) {
                $validator = validator($discount, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $discount;
                    continue;
                }

                $postData = [
                    'end_of_day_discount_id' => $discount['end_of_day_discount_id'] ?? null,
                    'pos_machine_id' => $discount['pos_machine_id'] ?? null,
                    'branch_id' => $discount['branch_id'] ?? null,
                    'end_of_day_id' => $discount['end_of_day_id'] ?? null,
                    'discount_type_id' => $discount['discount_type_id'] ?? null,
                    'name' => $discount['name'] ?? null,
                    'transaction_count' => $discount['transaction_count'] ?? null,
                    'amount' => $discount['amount'] ?? null,
                    'is_sent_to_server' => $discount['is_sent_to_server'] ?? null,
                    'treg' => $discount['treg'] ?? null,
                    'company_id' => $discount['company_id'] ?? null,
                    'is_zero_rated' => $discount['is_zero_rated'] ?? null,
                ];

                $record = EndOfDayDiscount::where([
                    'end_of_day_discount_id' => $discount['end_of_day_discount_id'],
                    'pos_machine_id' => $discount['pos_machine_id'],
                    'branch_id' => $discount['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                EndOfDayDiscount::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'End Of Day Discounts processed successfully.');
    }

    public function saveEndOfDayPayments(Request $request) 
    {
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $payment) {
                $validator = validator($payment, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $payment;
                    continue;
                }

                $postData = [
                    'end_of_day_payment_id' => $payment['end_of_day_payment_id'] ?? null,
                    'pos_machine_id' => $payment['pos_machine_id'] ?? null,
                    'branch_id' => $payment['branch_id'] ?? null,
                    'end_of_day_id' => $payment['end_of_day_id'] ?? null,
                    'payment_type_id' => $payment['payment_type_id'] ?? null,
                    'name' => $payment['name'] ?? null,
                    'transaction_count' => $payment['transaction_count'] ?? null,
                    'amount' => $payment['amount'] ?? null,
                    'is_sent_to_server' => $payment['is_sent_to_server'] ?? null,
                    'treg' => $payment['treg'] ?? null,
                    'company_id' => $payment['company_id'] ?? null,
                ];

                $record = EndOfDayPayment::where([
                    'end_of_day_payment_id' => $payment['end_of_day_payment_id'],
                    'pos_machine_id' => $payment['pos_machine_id'],
                    'branch_id' => $payment['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                EndOfDayPayment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'End Of Day Payments processed successfully.');
    }

    public function saveEndOfDayDepartments(Request $request)
    {
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
            'end_of_day_department_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'end_of_day_id' => 'required',
            'name' => 'required',
            'transaction_count' => 'required',
            'amount' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required',
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $dept) {
                $validator = validator($dept, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $dept;
                    continue;
                }

                $postData = [
                    'end_of_day_department_id' => $dept['end_of_day_department_id'] ?? null,
                    'pos_machine_id' => $dept['pos_machine_id'] ?? null,
                    'branch_id' => $dept['branch_id'] ?? null,
                    'end_of_day_id' => $dept['end_of_day_id'] ?? null,
                    'name' => $dept['name'] ?? null,
                    'transaction_count' => $dept['transaction_count'] ?? null,
                    'amount' => $dept['amount'] ?? null,
                    'is_sent_to_server' => $dept['is_sent_to_server'] ?? null,
                    'treg' => $dept['treg'] ?? null,
                    'company_id' => $dept['company_id'] ?? null,
                    'department_id' => $dept['department_id'] ?? null,
                ];

                $record = EndOfDayDepartment::where([
                    'end_of_day_department_id' => $dept['end_of_day_department_id'],
                    'pos_machine_id' => $dept['pos_machine_id'],
                    'branch_id' => $dept['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                EndOfDayDepartment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'End Of Day Departments processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $fund) {
                $validator = validator($fund, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $fund;
                    continue;
                }

                $postData = [
                    'cash_fund_id' => $fund['cash_fund_id'] ?? null,
                    'pos_machine_id' => $fund['pos_machine_id'] ?? null,
                    'branch_id' => $fund['branch_id'] ?? null,
                    'amount' => $fund['amount'] ?? null,
                    'cashier_id' => $fund['cashier_id'] ?? null,
                    'is_cut_off' => $fund['is_cut_off'] ?? null,
                    'cut_off_id' => $fund['cut_off_id'] ?? null,
                    'end_of_day_id' => $fund['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $fund['is_sent_to_server'] ?? null,
                    'shift_number' => $fund['shift_number'] ?? null,
                    'treg' => $fund['treg'] ?? null,
                    'cashier_name' => $fund['cashier_name'] ?? null,
                    'company_id' => $fund['company_id'] ?? null,
                ];

                $record = CashFund::where([
                    'cash_fund_id' => $fund['cash_fund_id'],
                    'pos_machine_id' => $fund['pos_machine_id'],
                    'branch_id' => $fund['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CashFund::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cash Funds processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $denom) {
                $validator = validator($denom, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $denom;
                    continue;
                }

                $postData = [
                    'cash_fund_denomination_id' => $denom['cash_fund_denomination_id'] ?? null,
                    'pos_machine_id' => $denom['pos_machine_id'] ?? null,
                    'branch_id' => $denom['branch_id'] ?? null,
                    'cash_fund_id' => $denom['cash_fund_id'] ?? null,
                    'cash_denomination_id' => $denom['cash_denomination_id'] ?? null,
                    'name' => $denom['name'] ?? null,
                    'amount' => $denom['amount'] ?? null,
                    'qty' => $denom['qty'] ?? null,
                    'total' => $denom['total'] ?? null,
                    'is_cut_off' => $denom['is_cut_off'] ?? null,
                    'cut_off_id' => $denom['cut_off_id'] ?? null,
                    'end_of_day_id' => $denom['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $denom['is_sent_to_server'] ?? null,
                    'shift_number' => $denom['shift_number'] ?? null,
                    'treg' => $denom['treg'] ?? null,
                    'company_id' => $denom['company_id'] ?? null,
                ];

                $record = CashFundDenomination::where([
                    'cash_fund_denomination_id' => $denom['cash_fund_denomination_id'],
                    'pos_machine_id' => $denom['pos_machine_id'],
                    'branch_id' => $denom['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CashFundDenomination::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cash Fund Denominations processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $audit) {
                $validator = validator($audit, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $audit;
                    continue;
                }

                $postData = [
                    'audit_trail_id' => $audit['audit_trail_id'] ?? null,
                    'pos_machine_id' => $audit['pos_machine_id'] ?? null,
                    'branch_id' => $audit['branch_id'] ?? null,
                    'user_id' => $audit['user_id'] ?? null,
                    'user_name' => $audit['user_name'] ?? null,
                    'transaction_id' => $audit['transaction_id'] ?? null,
                    'action' => $audit['action'] ?? null,
                    'description' => $audit['description'] ?? null,
                    'authorize_id' => $audit['authorize_id'] ?? null,
                    'authorize_name' => $audit['authorize_name'] ?? null,
                    'is_sent_to_server' => $audit['is_sent_to_server'] ?? null,
                    'treg' => $audit['treg'] ?? null,
                    'order_id' => $audit['order_id'] ?? null,
                    'price_change_reason_id' => $audit['price_change_reason_id'] ?? null,
                    'company_id' => $audit['company_id'] ?? null,
                ];

                $record = AuditTrail::where([
                    'audit_trail_id' => $audit['audit_trail_id'],
                    'pos_machine_id' => $audit['pos_machine_id'],
                    'branch_id' => $audit['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                AuditTrail::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Audit Trails processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $product) {
                $validator = validator($product, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $product;
                    continue;
                }

                $postData = [
                    'cut_off_product_id' => $product['cut_off_product_id'] ?? null,
                    'pos_machine_id' => $product['pos_machine_id'] ?? null,
                    'branch_id' => $product['branch_id'] ?? null,
                    'company_id' => $product['company_id'] ?? null,
                    'cut_off_id' => $product['cut_off_id'] ?? null,
                    'product_id' => $product['product_id'] ?? null,
                    'qty' => $product['qty'] ?? null,
                    'is_cut_off' => $product['is_cut_off'] ?? null,
                    'cut_off_at' => $product['cut_off_at'] ?? null,
                    'end_of_day_id' => $product['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $product['is_sent_to_server'] ?? null,
                    'treg' => $product['treg'] ?? null,
                ];

                $record = CutOffProduct::where([
                    'cut_off_product_id' => $product['cut_off_product_id'],
                    'pos_machine_id' => $product['pos_machine_id'],
                    'branch_id' => $product['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                CutOffProduct::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Cut Off Products processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $payout) {
                $validator = validator($payout, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $payout;
                    continue;
                }

                $postData = [
                    'payout_id' => $payout['payout_id'] ?? null,
                    'pos_machine_id' => $payout['pos_machine_id'] ?? null,
                    'branch_id' => $payout['branch_id'] ?? null,
                    'company_id' => $payout['company_id'] ?? null,
                    'control_number' => $payout['control_number'] ?? null,
                    'amount' => $payout['amount'] ?? null,
                    'reason' => $payout['reason'] ?? null,
                    'cashier_id' => $payout['cashier_id'] ?? null,
                    'cashier_name' => $payout['cashier_name'] ?? null,
                    'authorize_id' => $payout['authorize_id'] ?? null,
                    'authorize_name' => $payout['authorize_name'] ?? null,
                    'is_sent_to_server' => $payout['is_sent_to_server'] ?? null,
                    'is_cut_off' => $payout['is_cut_off'] ?? null,
                    'cut_off_id' => $payout['cut_off_id'] ?? null,
                    'cut_off_at' => $payout['cut_off_at'] ?? null,
                    'treg' => $payout['treg'] ?? null,
                    'safekeeping_id' => $payout['safekeeping_id'] ?? null,
                ];

                $record = Payout::where([
                    'payout_id' => $payout['payout_id'],
                    'pos_machine_id' => $payout['pos_machine_id'],
                    'branch_id' => $payout['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                Payout::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Payouts processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $info) {
                $validator = validator($info, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $info;
                    continue;
                }

                $postData = [
                    'official_receipt_information_id' => $info['official_receipt_information_id'] ?? null,
                    'pos_machine_id' => $info['pos_machine_id'] ?? null,
                    'branch_id' => $info['branch_id'] ?? null,
                    'company_id' => $info['company_id'] ?? null,
                    'transaction_id' => $info['transaction_id'] ?? null,
                    'name' => $info['name'] ?? null,
                    'address' => $info['address'] ?? null,
                    'tin' => $info['tin'] ?? null,
                    'business_style' => $info['business_style'] ?? null,
                    'is_void' => $info['is_void'] ?? null,
                    'void_by' => $info['void_by'] ?? null,
                    'void_name' => $info['void_name'] ?? null,
                    'void_at' => $info['void_at'] ?? null,
                    'is_sent_to_server' => $info['is_sent_to_server'] ?? null,
                    'treg' => $info['treg'] ?? null,
                ];

                $record = OfficialReceiptInformation::where([
                    'official_receipt_information_id' => $info['official_receipt_information_id'],
                    'pos_machine_id' => $info['pos_machine_id'],
                    'branch_id' => $info['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                OfficialReceiptInformation::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Official Receipt Informations processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $audit) {
                $validator = validator($audit, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $audit;
                    continue;
                }

                $postData = [
                    'spot_audit_id' => $audit['spot_audit_id'] ?? null,
                    'pos_machine_id' => $audit['pos_machine_id'] ?? null,
                    'branch_id' => $audit['branch_id'] ?? null,
                    'company_id' => $audit['company_id'] ?? null,
                    'beginning_or' => $audit['beginning_or'] ?? null,
                    'ending_or' => $audit['ending_or'] ?? null,
                    'beginning_amount' => $audit['beginning_amount'] ?? null,
                    'ending_amount' => $audit['ending_amount'] ?? null,
                    'total_transactions' => $audit['total_transactions'] ?? null,
                    'gross_sales' => $audit['gross_sales'] ?? null,
                    'net_sales' => $audit['net_sales'] ?? null,
                    'vatable_sales' => $audit['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $audit['vat_exempt_sales'] ?? null,
                    'vat_amount' => $audit['vat_amount'] ?? null,
                    'vat_expense' => $audit['vat_expense'] ?? null,
                    'void_qty' => $audit['void_qty'] ?? null,
                    'void_amount' => $audit['void_amount'] ?? null,
                    'total_change' => $audit['total_change'] ?? null,
                    'total_payout' => $audit['total_payout'] ?? null,
                    'total_service_charge' => $audit['total_service_charge'] ?? null,
                    'total_discount_amount' => $audit['total_discount_amount'] ?? null,
                    'total_cost' => $audit['total_cost'] ?? null,
                    'safekeeping_amount' => $audit['safekeeping_amount'] ?? null,
                    'safekeeping_short_over' => $audit['safekeeping_short_over'] ?? null,
                    'total_sk' => $audit['total_sk'] ?? null,
                    'total_short_over' => $audit['total_short_over'] ?? null,
                    'cashier_id' => $audit['cashier_id'] ?? null,
                    'cashier_name' => $audit['cashier_name'] ?? null,
                    'admin_id' => $audit['admin_id'] ?? null,
                    'admin_name' => $audit['admin_name'] ?? null,
                    'shift_number' => $audit['shift_number'] ?? null,
                    'is_cut_off' => $audit['is_cut_off'] ?? null,
                    'cut_off_id' => $audit['cut_off_id'] ?? null,
                    'is_sent_to_server' => $audit['is_sent_to_server'] ?? null,
                    'treg' => $audit['treg'] ?? null,
                ];

                $record = SpotAudit::where([
                    'spot_audit_id' => $audit['spot_audit_id'],
                    'pos_machine_id' => $audit['pos_machine_id'],
                    'branch_id' => $audit['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                SpotAudit::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Spot Audits processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
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
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $denom) {
                $validator = validator($denom, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $denom;
                    continue;
                }

                $postData = [
                    'spot_audit_denomination_id' => $denom['spot_audit_denomination_id'] ?? null,
                    'pos_machine_id' => $denom['pos_machine_id'] ?? null,
                    'branch_id' => $denom['branch_id'] ?? null,
                    'company_id' => $denom['company_id'] ?? null,
                    'spot_audit_id' => $denom['spot_audit_id'] ?? null,
                    'cash_denomination_id' => $denom['cash_denomination_id'] ?? null,
                    'name' => $denom['name'] ?? null,
                    'amount' => $denom['amount'] ?? null,
                    'qty' => $denom['qty'] ?? null,
                    'total' => $denom['total'] ?? null,
                    'is_cut_off' => $denom['is_cut_off'] ?? null,
                    'cut_off_id' => $denom['cut_off_id'] ?? null,
                    'is_sent_to_server' => $denom['is_sent_to_server'] ?? null,
                    'shift_number' => $denom['shift_number'] ?? null,
                    'treg' => $denom['treg'] ?? null,
                ];

                $record = SpotAuditDenomination::where([
                    'spot_audit_denomination_id' => $denom['spot_audit_denomination_id'],
                    'pos_machine_id' => $denom['pos_machine_id'],
                    'branch_id' => $denom['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                SpotAuditDenomination::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'Spot Audit Denominations processed successfully.');
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility (array or single object, with or without 'data' wrapper)
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                $data = $requestData['data'];
            } else {
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            $data = $requestData;
        } else {
            $data = [];
        }
        $failedRequests = [];
        $rules = [
            'end_of_day_product_id' => 'required',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'company_id' => 'required',
            'end_of_day_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required',
            'is_sent_to_server' => 'required',
            'treg' => 'required'
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $product) {
                $validator = validator($product, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $product;
                    continue;
                }

                $postData = [
                    'end_of_day_product_id' => $product['end_of_day_product_id'] ?? null,
                    'pos_machine_id' => $product['pos_machine_id'] ?? null,
                    'branch_id' => $product['branch_id'] ?? null,
                    'company_id' => $product['company_id'] ?? null,
                    'end_of_day_id' => $product['end_of_day_id'] ?? null,
                    'product_id' => $product['product_id'] ?? null,
                    'qty' => $product['qty'] ?? null,
                    'is_sent_to_server' => $product['is_sent_to_server'] ?? null,
                    'treg' => $product['treg'] ?? null,
                ];

                $record = EndOfDayProduct::where([
                    'end_of_day_product_id' => $product['end_of_day_product_id'],
                    'pos_machine_id' => $product['pos_machine_id'],
                    'branch_id' => $product['branch_id'],
                ])->first();

                if ($record) {
                    $toUpdate[] = [
                        'model' => $record,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                EndOfDayProduct::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'End Of Day Products processed successfully.');
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

    // Helper to check if array is associative
    private static function isAssoc(array $arr)
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}