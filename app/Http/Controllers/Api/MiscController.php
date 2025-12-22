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

        $log = new ApiRequestLog();
        $log->type = 'saveTransactionsRequest';
        $log->method = $request->method();
        $log->request = json_encode($requestData);
        $log->response = '';
        $log->save();

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
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                Transaction::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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

        $log = new ApiRequestLog();
        $log->type = 'saveOrdersRequest';
        $log->method = $request->method();
        $log->request = json_encode($requestData);
        $log->response = '';
        $log->save();

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
            'amount' => ['required', 'numeric'],
            'original_amount' => ['required', 'numeric'],
            'gross' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'total_cost' => ['required', 'numeric'],
            'is_vatable' => 'required|boolean',
            'vat_amount' => ['required', 'numeric'],
            'vatable_sales' => ['required', 'numeric'],
            'vat_exempt_sales' => ['required', 'numeric'],
            'discount_amount' => ['required', 'numeric'],
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'unit_id' => 'required|numeric',
            'is_void' => 'required|boolean',
            'is_back_out' => 'required|boolean',
            'min_amount_sold' => ['required', 'numeric'],
            'is_paid' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_completed' => 'required|boolean',
            'branch_id' => 'required',
            'is_cut_off' => 'required|boolean',
            'is_discount_exempt' => 'required|boolean',
            'is_open_price' => 'required|boolean',
            'vat_expense' => ['required', 'numeric'],
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
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                Order::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
            'amount' => ['required', 'numeric'],
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
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                Payment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
            'amount' => ['required', 'numeric'],
            'cashier_id' => 'required',
            'authorize_id' => 'required',
            'is_cut_off' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'end_of_day_id' => 'required',
            'is_auto' => 'required|boolean',
            'short_over' => ['required', 'numeric'],
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $safekeeping) {
                $validator = validator($safekeeping, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $safekeeping;
                    continue;
                }

                $postData = [
                    'safekeeping_id' => $safekeeping['safekeeping_id'] ?? null,
                    'pos_machine_id' => $safekeeping['pos_machine_id'] ?? null,
                    'branch_id' => $safekeeping['branch_id'] ?? null,
                    'amount' => $safekeeping['amount'] ?? null,
                    'cashier_id' => $safekeeping['cashier_id'] ?? null,
                    'cashier_name' => $safekeeping['cashier_name'] ?? null,
                    'authorize_id' => $safekeeping['authorize_id'] ?? null,
                    'authorize_name' => $safekeeping['authorize_name'] ?? null,
                    'is_cut_off' => $safekeeping['is_cut_off'] ?? null,
                    'cut_off_id' => $safekeeping['cut_off_id'] ?? null,
                    'is_sent_to_server' => $safekeeping['is_sent_to_server'] ?? null,
                    'shift_number' => $safekeeping['shift_number'] ?? null,
                    'treg' => $safekeeping['treg'] ?? null,
                    'end_of_day_id' => $safekeeping['end_of_day_id'] ?? null,
                    'is_auto' => $safekeeping['is_auto'] ?? null,
                    'short_over' => $safekeeping['short_over'] ?? null,
                    'company_id' => $safekeeping['company_id'] ?? null,
                ];

                $existingSafekeeping = Safekeeping::where([
                    'safekeeping_id' => $safekeeping['safekeeping_id'],
                    'pos_machine_id' => $safekeeping['pos_machine_id'],
                    'branch_id' => $safekeeping['branch_id'],
                ])->first();

                if ($existingSafekeeping) {
                    $toUpdate[] = [
                        'model' => $existingSafekeeping,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                Safekeeping::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
            'amount' => ['required', 'numeric'],
            'qty' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
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
            foreach ($data as $idx => $denomination) {
                $validator = validator($denomination, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $denomination;
                    continue;
                }

                $postData = [
                    'branch_id' => $denomination['branch_id'] ?? null,
                    'pos_machine_id' => $denomination['pos_machine_id'] ?? null,
                    'safekeeping_denomination_id' => $denomination['safekeeping_denomination_id'] ?? null,
                    'safekeeping_id' => $denomination['safekeeping_id'] ?? null,
                    'cash_denomination_id' => $denomination['cash_denomination_id'] ?? null,
                    'name' => $denomination['name'] ?? null,
                    'amount' => $denomination['amount'] ?? null,
                    'qty' => $denomination['qty'] ?? null,
                    'total' => $denomination['total'] ?? null,
                    'shift_number' => $denomination['shift_number'] ?? null,
                    'cut_off_id' => $denomination['cut_off_id'] ?? null,
                    'treg' => $denomination['treg'] ?? null,
                    'end_of_day_id' => $denomination['end_of_day_id'] ?? null,
                    'is_cut_off' => $denomination['is_cut_off'] ?? null,
                    'is_sent_to_server' => $denomination['is_sent_to_server'] ?? null,
                    'company_id' => $denomination['company_id'] ?? null,
                ];

                $existingDenomination = SafekeepingDenomination::where([
                    'safekeeping_denomination_id' => $denomination['safekeeping_denomination_id'],
                    'safekeeping_id' => $denomination['safekeeping_id'],
                    'cash_denomination_id' => $denomination['cash_denomination_id'],
                ])->first();

                if ($existingDenomination) {
                    $toUpdate[] = [
                        'model' => $existingDenomination,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                SafekeepingDenomination::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of end of days
                $data = $requestData['data'];
            } else {
                // If it's a single end of day object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single end of day object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of end of days (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }

        $failedRequests = [];
        $rules = [
            'end_of_day_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'beginning_amount' => ['required', 'numeric'],
            'ending_amount' => ['required', 'numeric'],
            'total_transactions' => 'required|numeric',
            'gross_sales' => ['required', 'numeric'],
            'net_sales' => ['required', 'numeric'],
            'vatable_sales' => ['required', 'numeric'],
            'vat_exempt_sales' => ['required', 'numeric'],
            'vat_amount' => ['required', 'numeric'],
            'vat_expense' => ['required', 'numeric'],
            'void_amount' => ['required', 'numeric'],
            'total_change' => ['required', 'numeric'],
            'total_payout' => ['required', 'numeric'],
            'total_service_charge' => ['required', 'numeric'],
            'total_discount_amount' => ['required', 'numeric'],
            'total_cost' => ['required', 'numeric'],
            'total_sk' => ['required', 'numeric'],
            'cashier_id' => 'required',
            'shift_number' => 'required',
            'is_sent_to_server' => 'required|boolean',
            'reading_number' => 'required|numeric',
            'void_qty' => 'required|numeric',
            'total_short_over' => ['required', 'numeric'],
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $endOfDay) {
                $validator = validator($endOfDay, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $endOfDay;
                    continue;
                }

                $branch = Branch::findOrFail($endOfDay['branch_id']);

                $postData = [
                    'end_of_day_id' => $endOfDay['end_of_day_id'] ?? null,
                    'pos_machine_id' => $endOfDay['pos_machine_id'] ?? null,
                    'branch_id' => $endOfDay['branch_id'] ?? null,
                    'beginning_or' => $endOfDay['beginning_or'] ?? null,
                    'ending_or' => $endOfDay['ending_or'] ?? null,
                    'beginning_amount' => $endOfDay['beginning_amount'] ?? null,
                    'ending_amount' => $endOfDay['ending_amount'] ?? null,
                    'total_transactions' => $endOfDay['total_transactions'] ?? null,
                    'gross_sales' => $endOfDay['gross_sales'] ?? null,
                    'net_sales' => $endOfDay['net_sales'] ?? null,
                    'vatable_sales' => $endOfDay['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $endOfDay['vat_exempt_sales'] ?? null,
                    'vat_amount' => $endOfDay['vat_amount'] ?? null,
                    'vat_expense' => $endOfDay['vat_expense'] ?? null,
                    'void_amount' => $endOfDay['void_amount'] ?? null,
                    'total_change' => $endOfDay['total_change'] ?? null,
                    'total_payout' => $endOfDay['total_payout'] ?? null,
                    'total_service_charge' => $endOfDay['total_service_charge'] ?? null,
                    'total_discount_amount' => $endOfDay['total_discount_amount'] ?? null,
                    'total_cost' => $endOfDay['total_cost'] ?? null,
                    'total_sk' => $endOfDay['total_sk'] ?? null,
                    'cashier_id' => $endOfDay['cashier_id'] ?? null,
                    'cashier_name' => $endOfDay['cashier_name'] ?? null,
                    'admin_id' => $endOfDay['admin_id'] ?? null,
                    'admin_name' => $endOfDay['admin_name'] ?? null,
                    'shift_number' => $endOfDay['shift_number'] ?? null,
                    'is_sent_to_server' => $endOfDay['is_sent_to_server'] ?? null,
                    'treg' => $endOfDay['treg'] ?? null,
                    'reading_number' => $endOfDay['reading_number'] ?? null,
                    'void_qty' => $endOfDay['void_qty'] ?? null,
                    'total_short_over' => $endOfDay['total_short_over'] ?? null,
                    'generated_date' => $endOfDay['generated_date'] ?? null,
                    'beg_reading_number' => $endOfDay['beg_reading_number'] ?? null,
                    'end_reading_number' => $endOfDay['end_reading_number'] ?? null,
                    'total_zero_rated_amount' => $endOfDay['total_zero_rated_amount'] ?? null,
                    'print_string' => $endOfDay['print_string'] ?? null,
                    'company_id' => $endOfDay['company_id'] ?? null,
                    'beginning_counter_amount' => $endOfDay['beginning_counter_amount'] ?? null,
                    'ending_counter_amount' => $endOfDay['ending_counter_amount'] ?? null,
                    'total_cash_fund' => $endOfDay['total_cash_fund'] ?? null,
                    'beginning_gt_counter' => $endOfDay['beginning_gt_counter'] ?? null,
                    'ending_gt_counter' => $endOfDay['ending_gt_counter'] ?? null,
                    'beginning_cut_off_counter' => $endOfDay['beginning_cut_off_counter'] ?? null,
                    'ending_cut_off_counter' => $endOfDay['ending_cut_off_counter'] ?? null,
                    'total_return' => $endOfDay['total_return'] ?? null,
                    'is_complete' => $endOfDay['is_complete'] ?? null,
                ];

                if (isset($endOfDay['products'])) {
                    foreach ($endOfDay['products'] as $reqProduct) {
                        $product = Product::find($reqProduct['productId'])
                            ->load('bundledItems', 'rawItems');

                        if ($product) {
                            $this->productRepository->updateBranchQuantity($product, $branch, $reqProduct['endOfDayId'], 'end_of_days', $reqProduct['qty'], null, 'subtract', $product->uom_id);

                            foreach ($product->bundledItems as $bundledItem) {
                                $this->productRepository->updateBranchQuantity($bundledItem, $branch, $reqProduct['endOfDayId'], 'end_of_days', $reqProduct['qty'] * $bundledItem->bundled_item->quantity, null, 'subtract', $bundledItem->uom_id);
                            }

                            foreach ($product->rawItems as $rawItem) {
                                $this->productRepository->updateBranchQuantity($rawItem, $branch, $reqProduct['endOfDayId'], 'end_of_days', $reqProduct['qty'] * $rawItem->bundled_item->quantity, null, 'subtract', $rawItem->uom_id);
                            }
                        }
                    }
                }

                $existingEndOfDay = EndOfDay::where([
                    'end_of_day_id' => $endOfDay['end_of_day_id'],
                    'pos_machine_id' => $endOfDay['pos_machine_id'],
                    'branch_id' => $endOfDay['branch_id'],
                ])->first();

                if ($existingEndOfDay) {
                    $toUpdate[] = [
                        'model' => $existingEndOfDay,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }

                // Clean up take order data for this branch
                TakeOrderTransaction::where('branch_id', $endOfDay['branch_id'])->delete();
                TakeOrderOrder::where('branch_id', $endOfDay['branch_id'])->delete();
                TakeOrderDiscount::where('branch_id', $endOfDay['branch_id'])->delete();
                TakeOrderDiscountDetail::where('branch_id', $endOfDay['branch_id'])->delete();
                TakeOrderDiscountOtherInformation::where('branch_id', $endOfDay['branch_id'])->delete();
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                EndOfDay::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
                $item['model']->update($item['data']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Database Error', $e->getMessage(), 500);
        }

        return $this->sendResponse([
            'failed_requests' => array_values($failedRequests)
        ], 'End of Days processed successfully.');
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cut offs
                $data = $requestData['data'];
            } else {
                // If it's a single cut off object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cut off object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cut offs (not inside 'data')
            $data = $requestData;
        } else {
            $data = [];
        }

        $failedRequests = [];
        $rules = [
            'cut_off_id' => 'required|numeric|min:1',
            'pos_machine_id' => 'required',
            'branch_id' => 'required',
            'beginning_amount' => ['required', 'numeric'],
            'ending_amount' => ['required', 'numeric'],
            'total_transactions' => 'required|numeric',
            'gross_sales' => ['required', 'numeric'],
            'net_sales' => ['required', 'numeric'],
            'vatable_sales' => ['required', 'numeric'],
            'vat_exempt_sales' => ['required', 'numeric'],
            'vat_amount' => ['required', 'numeric'],
            'vat_expense' => ['required', 'numeric'],
            'void_amount' => ['required', 'numeric'],
            'total_change' => ['required', 'numeric'],
            'total_payout' => ['required', 'numeric'],
            'total_service_charge' => ['required', 'numeric'],
            'total_discount_amount' => ['required', 'numeric'],
            'total_cost' => ['required', 'numeric'],
            'total_sk' => ['required', 'numeric'],
            'cashier_id' => 'required',
            'shift_number' => 'required',
            'is_sent_to_server' => 'required|boolean',
            'reading_number' => 'required|numeric',
            'void_qty' => 'required|numeric',
            'total_short_over' => ['required', 'numeric'],
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $cutOff) {
                $validator = validator($cutOff, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $cutOff;
                    continue;
                }

                $postData = [
                    'cut_off_id' => $cutOff['cut_off_id'] ?? null,
                    'end_of_day_id' => $cutOff['end_of_day_id'] ?? null,
                    'pos_machine_id' => $cutOff['pos_machine_id'] ?? null,
                    'branch_id' => $cutOff['branch_id'] ?? null,
                    'beginning_or' => $cutOff['beginning_or'] ?? null,
                    'ending_or' => $cutOff['ending_or'] ?? null,
                    'beginning_amount' => $cutOff['beginning_amount'] ?? null,
                    'ending_amount' => $cutOff['ending_amount'] ?? null,
                    'total_transactions' => $cutOff['total_transactions'] ?? null,
                    'gross_sales' => $cutOff['gross_sales'] ?? null,
                    'net_sales' => $cutOff['net_sales'] ?? null,
                    'vatable_sales' => $cutOff['vatable_sales'] ?? null,
                    'vat_exempt_sales' => $cutOff['vat_exempt_sales'] ?? null,
                    'vat_amount' => $cutOff['vat_amount'] ?? null,
                    'vat_expense' => $cutOff['vat_expense'] ?? null,
                    'void_amount' => $cutOff['void_amount'] ?? null,
                    'total_change' => $cutOff['total_change'] ?? null,
                    'total_payout' => $cutOff['total_payout'] ?? null,
                    'total_service_charge' => $cutOff['total_service_charge'] ?? null,
                    'total_discount_amount' => $cutOff['total_discount_amount'] ?? null,
                    'total_cost' => $cutOff['total_cost'] ?? null,
                    'total_sk' => $cutOff['total_sk'] ?? null,
                    'cashier_id' => $cutOff['cashier_id'] ?? null,
                    'cashier_name' => $cutOff['cashier_name'] ?? null,
                    'admin_id' => $cutOff['admin_id'] ?? null,
                    'admin_name' => $cutOff['admin_name'] ?? null,
                    'shift_number' => $cutOff['shift_number'] ?? null,
                    'is_sent_to_server' => $cutOff['is_sent_to_server'] ?? null,
                    'treg' => $cutOff['treg'] ?? null,
                    'reading_number' => $cutOff['reading_number'] ?? null,
                    'void_qty' => $cutOff['void_qty'] ?? null,
                    'total_short_over' => $cutOff['total_short_over'] ?? null,
                    'total_zero_rated_amount' => $cutOff['total_zero_rated_amount'] ?? null,
                    'print_string' => $cutOff['print_string'] ?? null,
                    'company_id' => $cutOff['company_id'] ?? null,
                    'beginning_counter_amount' => $cutOff['beginning_counter_amount'] ?? null,
                    'ending_counter_amount' => $cutOff['ending_counter_amount'] ?? null,
                    'total_cash_fund' => $cutOff['total_cash_fund'] ?? null,
                    'beginning_gt_counter' => $cutOff['beginning_gt_counter'] ?? null,
                    'ending_gt_counter' => $cutOff['ending_gt_counter'] ?? null,
                    'total_return' => $cutOff['total_return'] ?? null,
                    'is_complete' => $cutOff['is_complete'] ?? null,
                ];

                $existingCutOff = CutOff::where([
                    'cut_off_id' => $cutOff['cut_off_id'],
                    'pos_machine_id' => $cutOff['pos_machine_id'],
                    'branch_id' => $cutOff['branch_id'],
                ])->first();

                if ($existingCutOff) {
                    $toUpdate[] = [
                        'model' => $existingCutOff,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CutOff::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of discounts
                $data = $requestData['data'];
            } else {
                // If it's a single discount object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single discount object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of discounts (not inside 'data')
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
            'value' => ['required', 'numeric'],
            'discount_amount' => ['required', 'numeric'],
            'vat_exempt_amount' => ['required', 'numeric'],
            'vat_expense' => ['required', 'numeric'],
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
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                Discount::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of discount details
                $data = $requestData['data'];
            } else {
                // If it's a single discount detail object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single discount detail object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of discount details (not inside 'data')
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
            'value' => ['required', 'numeric'],
            'discount_amount' => ['required', 'numeric'],
            'vat_exempt_amount' => ['required', 'numeric'],
            'vat_expense' => ['required', 'numeric'],
            'is_void' => 'required|boolean',
            'is_sent_to_server' => 'required|boolean',
            'is_cut_off' => 'required|boolean',
            'is_vat_exempt' => 'required|boolean',
        ];

        $toInsert = [];
        $toUpdate = [];

        DB::beginTransaction();
        try {
            foreach ($data as $idx => $discountDetail) {
                $validator = validator($discountDetail, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $discountDetail;
                    continue;
                }

                $postData = [
                    'discount_details_id' => $discountDetail['discount_details_id'] ?? null,
                    'discount_id' => $discountDetail['discount_id'] ?? null,
                    'pos_machine_id' => $discountDetail['pos_machine_id'] ?? null,
                    'branch_id' => $discountDetail['branch_id'] ?? null,
                    'custom_discount_id' => $discountDetail['custom_discount_id'] ?? null,
                    'transaction_id' => $discountDetail['transaction_id'] ?? null,
                    'order_id' => $discountDetail['order_id'] ?? null,
                    'discount_type_id' => $discountDetail['discount_type_id'] ?? null,
                    'value' => $discountDetail['value'] ?? null,
                    'discount_amount' => $discountDetail['discount_amount'] ?? null,
                    'vat_exempt_amount' => $discountDetail['vat_exempt_amount'] ?? null,
                    'type' => $discountDetail['type'] ?? null,
                    'is_void' => $discountDetail['is_void'] ?? null,
                    'void_by_id' => $discountDetail['void_by_id'] ?? null,
                    'void_by' => $discountDetail['void_by'] ?? null,
                    'void_at' => $discountDetail['void_at'] ?? null,
                    'is_sent_to_server' => $discountDetail['is_sent_to_server'] ?? null,
                    'is_cut_off' => $discountDetail['is_cut_off'] ?? null,
                    'cut_off_id' => $discountDetail['cut_off_id'] ?? null,
                    'is_vat_exempt' => $discountDetail['is_vat_exempt'] ?? null,
                    'shift_number' => $discountDetail['shift_number'] ?? null,
                    'treg' => $discountDetail['treg'] ?? null,
                    'vat_expense' => $discountDetail['vat_expense'] ?? null,
                    'is_zero_rated' => $discountDetail['is_zero_rated'] ?? null,
                    'company_id' => $discountDetail['company_id'] ?? null,
                    'is_completed' => $discountDetail['is_completed'] ?? null,
                    'completed_at' => $discountDetail['completed_at'] ?? null,
                ];

                $existingDiscountDetail = DiscountDetail::where([
                    'discount_details_id' => $discountDetail['discount_details_id'],
                    'pos_machine_id' => $discountDetail['pos_machine_id'],
                    'branch_id' => $discountDetail['branch_id'],
                ])->first();

                if ($existingDiscountDetail) {
                    $toUpdate[] = [
                        'model' => $existingDiscountDetail,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                DiscountDetail::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of payment other informations
                $data = $requestData['data'];
            } else {
                // If it's a single payment other information object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single payment other information object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of payment other informations (not inside 'data')
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
            foreach ($data as $idx => $paymentInfo) {
                $validator = validator($paymentInfo, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $paymentInfo;
                    continue;
                }

                $postData = [
                    'payment_other_information_id' => $paymentInfo['payment_other_information_id'] ?? null,
                    'pos_machine_id' => $paymentInfo['pos_machine_id'] ?? null,
                    'branch_id' => $paymentInfo['branch_id'] ?? null,
                    'transaction_id' => $paymentInfo['transaction_id'] ?? null,
                    'payment_id' => $paymentInfo['payment_id'] ?? null,
                    'name' => $paymentInfo['name'] ?? null,
                    'value' => $paymentInfo['value'] ?? null,
                    'is_cut_off' => $paymentInfo['is_cut_off'] ?? null,
                    'cut_off_id' => $paymentInfo['cut_off_id'] ?? null,
                    'is_void' => $paymentInfo['is_void'] ?? null,
                    'is_sent_to_server' => $paymentInfo['is_sent_to_server'] ?? null,
                    'treg' => $paymentInfo['treg'] ?? null,
                    'company_id' => $paymentInfo['company_id'] ?? null,
                    'is_mask' => $paymentInfo['is_mask'] ?? null,
                ];

                $existingRecord = PaymentOtherInformation::where([
                    'payment_other_information_id' => $paymentInfo['payment_other_information_id'],
                    'pos_machine_id' => $paymentInfo['pos_machine_id'],
                    'branch_id' => $paymentInfo['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                PaymentOtherInformation::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of discount other informations
                $data = $requestData['data'];
            } else {
                // If it's a single discount other information object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single discount other information object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of discount other informations (not inside 'data')
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
            foreach ($data as $idx => $discountInfo) {
                $validator = validator($discountInfo, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $discountInfo;
                    continue;
                }

                $postData = [
                    'discount_other_information_id' => $discountInfo['discount_other_information_id'] ?? null,
                    'pos_machine_id' => $discountInfo['pos_machine_id'] ?? null,
                    'branch_id' => $discountInfo['branch_id'] ?? null,
                    'transaction_id' => $discountInfo['transaction_id'] ?? null,
                    'discount_id' => $discountInfo['discount_id'] ?? null,
                    'name' => $discountInfo['name'] ?? null,
                    'value' => $discountInfo['value'] ?? null,
                    'is_cut_off' => $discountInfo['is_cut_off'] ?? null,
                    'cut_off_id' => $discountInfo['cut_off_id'] ?? null,
                    'is_void' => $discountInfo['is_void'] ?? null,
                    'is_sent_to_server' => $discountInfo['is_sent_to_server'] ?? null,
                    'treg' => $discountInfo['treg'] ?? null,
                    'company_id' => $discountInfo['company_id'] ?? null,
                ];

                $existingRecord = DiscountOtherInformation::where([
                    'discount_other_information_id' => $discountInfo['discount_other_information_id'],
                    'pos_machine_id' => $discountInfo['pos_machine_id'],
                    'branch_id' => $discountInfo['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                DiscountOtherInformation::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cut off departments
                $data = $requestData['data'];
            } else {
                // If it's a single cut off department object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cut off department object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cut off departments (not inside 'data')
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
            foreach ($data as $idx => $department) {
                $validator = validator($department, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $department;
                    continue;
                }

                $postData = [
                    'cut_off_department_id' => $department['cut_off_department_id'] ?? null,
                    'pos_machine_id' => $department['pos_machine_id'] ?? null,
                    'branch_id' => $department['branch_id'] ?? null,
                    'is_cut_off' => $department['is_cut_off'] ?? null,
                    'cut_off_id' => $department['cut_off_id'] ?? null,
                    'department_id' => $department['department_id'] ?? null,
                    'name' => $department['name'] ?? null,
                    'transaction_count' => $department['transaction_count'] ?? null,
                    'amount' => $department['amount'] ?? null,
                    'end_of_day_id' => $department['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $department['is_sent_to_server'] ?? null,
                    'treg' => $department['treg'] ?? null,
                    'company_id' => $department['company_id'] ?? null,
                ];

                $existingRecord = CutOffDepartment::where([
                    'cut_off_department_id' => $department['cut_off_department_id'],
                    'pos_machine_id' => $department['pos_machine_id'],
                    'branch_id' => $department['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CutOffDepartment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cut off discounts
                $data = $requestData['data'];
            } else {
                // If it's a single cut off discount object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cut off discount object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cut off discounts (not inside 'data')
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

                $existingRecord = CutOffDiscount::where([
                    'cut_off_discount_id' => $discount['cut_off_discount_id'],
                    'pos_machine_id' => $discount['pos_machine_id'],
                    'branch_id' => $discount['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CutOffDiscount::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cut off payments
                $data = $requestData['data'];
            } else {
                // If it's a single cut off payment object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cut off payment object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cut off payments (not inside 'data')
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

                $existingRecord = CutOffPayment::where([
                    'cut_off_payment_id' => $payment['cut_off_payment_id'],
                    'pos_machine_id' => $payment['pos_machine_id'],
                    'branch_id' => $payment['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CutOffPayment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of end of day discounts
                $data = $requestData['data'];
            } else {
                // If it's a single end of day discount object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single end of day discount object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of end of day discounts (not inside 'data')
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

                $existingRecord = EndOfDayDiscount::where([
                    'end_of_day_discount_id' => $discount['end_of_day_discount_id'],
                    'pos_machine_id' => $discount['pos_machine_id'],
                    'branch_id' => $discount['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                EndOfDayDiscount::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of end of day payments
                $data = $requestData['data'];
            } else {
                // If it's a single end of day payment object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single end of day payment object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of end of day payments (not inside 'data')
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

                $existingRecord = EndOfDayPayment::where([
                    'end_of_day_payment_id' => $payment['end_of_day_payment_id'],
                    'pos_machine_id' => $payment['pos_machine_id'],
                    'branch_id' => $payment['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                EndOfDayPayment::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        $requestData = $request->all();
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cash funds
                $data = $requestData['data'];
            } else {
                // If it's a single cash fund object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cash fund object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cash funds (not inside 'data')
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
            foreach ($data as $idx => $cashFund) {
                $validator = validator($cashFund, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $cashFund;
                    continue;
                }

                $postData = [
                    'cash_fund_id' => $cashFund['cash_fund_id'] ?? null,
                    'pos_machine_id' => $cashFund['pos_machine_id'] ?? null,
                    'branch_id' => $cashFund['branch_id'] ?? null,
                    'amount' => $cashFund['amount'] ?? null,
                    'cashier_id' => $cashFund['cashier_id'] ?? null,
                    'is_cut_off' => $cashFund['is_cut_off'] ?? null,
                    'cut_off_id' => $cashFund['cut_off_id'] ?? null,
                    'end_of_day_id' => $cashFund['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $cashFund['is_sent_to_server'] ?? null,
                    'shift_number' => $cashFund['shift_number'] ?? null,
                    'treg' => $cashFund['treg'] ?? null,
                    'cashier_name' => $cashFund['cashier_name'] ?? null,
                    'company_id' => $cashFund['company_id'] ?? null,
                ];

                $existingRecord = CashFund::where([
                    'cash_fund_id' => $cashFund['cash_fund_id'],
                    'pos_machine_id' => $cashFund['pos_machine_id'],
                    'branch_id' => $cashFund['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CashFund::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cash fund denominations
                $data = $requestData['data'];
            } else {
                // If it's a single cash fund denomination object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cash fund denomination object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cash fund denominations (not inside 'data')
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
            foreach ($data as $idx => $denomination) {
                $validator = validator($denomination, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $denomination;
                    continue;
                }

                $postData = [
                    'cash_fund_denomination_id' => $denomination['cash_fund_denomination_id'] ?? null,
                    'pos_machine_id' => $denomination['pos_machine_id'] ?? null,
                    'branch_id' => $denomination['branch_id'] ?? null,
                    'cash_fund_id' => $denomination['cash_fund_id'] ?? null,
                    'cash_denomination_id' => $denomination['cash_denomination_id'] ?? null,
                    'name' => $denomination['name'] ?? null,
                    'amount' => $denomination['amount'] ?? null,
                    'qty' => $denomination['qty'] ?? null,
                    'total' => $denomination['total'] ?? null,
                    'is_cut_off' => $denomination['is_cut_off'] ?? null,
                    'cut_off_id' => $denomination['cut_off_id'] ?? null,
                    'end_of_day_id' => $denomination['end_of_day_id'] ?? null,
                    'is_sent_to_server' => $denomination['is_sent_to_server'] ?? null,
                    'shift_number' => $denomination['shift_number'] ?? null,
                    'treg' => $denomination['treg'] ?? null,
                    'company_id' => $denomination['company_id'] ?? null,
                ];

                $existingRecord = CashFundDenomination::where([
                    'cash_fund_denomination_id' => $denomination['cash_fund_denomination_id'],
                    'pos_machine_id' => $denomination['pos_machine_id'],
                    'branch_id' => $denomination['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CashFundDenomination::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of audit trails
                $data = $requestData['data'];
            } else {
                // If it's a single audit trail object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single audit trail object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of audit trails (not inside 'data')
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
            foreach ($data as $idx => $auditTrail) {
                $validator = validator($auditTrail, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $auditTrail;
                    continue;
                }

                $postData = [
                    'audit_trail_id' => $auditTrail['audit_trail_id'] ?? null,
                    'pos_machine_id' => $auditTrail['pos_machine_id'] ?? null,
                    'branch_id' => $auditTrail['branch_id'] ?? null,
                    'user_id' => $auditTrail['user_id'] ?? null,
                    'user_name' => $auditTrail['user_name'] ?? null,
                    'transaction_id' => $auditTrail['transaction_id'] ?? null,
                    'action' => $auditTrail['action'] ?? null,
                    'description' => $auditTrail['description'] ?? null,
                    'authorize_id' => $auditTrail['authorize_id'] ?? null,
                    'authorize_name' => $auditTrail['authorize_name'] ?? null,
                    'is_sent_to_server' => $auditTrail['is_sent_to_server'] ?? null,
                    'treg' => $auditTrail['treg'] ?? null,
                    'order_id' => $auditTrail['order_id'] ?? null,
                    'price_change_reason_id' => $auditTrail['price_change_reason_id'] ?? null,
                    'company_id' => $auditTrail['company_id'] ?? null,
                ];

                $existingRecord = AuditTrail::where([
                    'audit_trail_id' => $auditTrail['audit_trail_id'],
                    'pos_machine_id' => $auditTrail['pos_machine_id'],
                    'branch_id' => $auditTrail['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                AuditTrail::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of cut off products
                $data = $requestData['data'];
            } else {
                // If it's a single cut off product object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single cut off product object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of cut off products (not inside 'data')
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

                $existingRecord = CutOffProduct::where([
                    'cut_off_product_id' => $product['cut_off_product_id'],
                    'pos_machine_id' => $product['pos_machine_id'],
                    'branch_id' => $product['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                CutOffProduct::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of payouts
                $data = $requestData['data'];
            } else {
                // If it's a single payout object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single payout object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of payouts (not inside 'data')
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

                $existingRecord = Payout::where([
                    'payout_id' => $payout['payout_id'],
                    'pos_machine_id' => $payout['pos_machine_id'],
                    'branch_id' => $payout['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                Payout::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of official receipt informations
                $data = $requestData['data'];
            } else {
                // If it's a single official receipt information object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single official receipt information object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of official receipt informations (not inside 'data')
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
            foreach ($data as $idx => $information) {
                $validator = validator($information, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $information;
                    continue;
                }

                $postData = [
                    'official_receipt_information_id' => $information['official_receipt_information_id'] ?? null,
                    'pos_machine_id' => $information['pos_machine_id'] ?? null,
                    'branch_id' => $information['branch_id'] ?? null,
                    'company_id' => $information['company_id'] ?? null,
                    'transaction_id' => $information['transaction_id'] ?? null,
                    'name' => $information['name'] ?? null,
                    'address' => $information['address'] ?? null,
                    'tin' => $information['tin'] ?? null,
                    'business_style' => $information['business_style'] ?? null,
                    'is_void' => $information['is_void'] ?? null,
                    'void_by' => $information['void_by'] ?? null,
                    'void_name' => $information['void_name'] ?? null,
                    'void_at' => $information['void_at'] ?? null,
                    'is_sent_to_server' => $information['is_sent_to_server'] ?? null,
                    'treg' => $information['treg'] ?? null,
                ];

                $existingRecord = OfficialReceiptInformation::where([
                    'official_receipt_information_id' => $information['official_receipt_information_id'],
                    'pos_machine_id' => $information['pos_machine_id'],
                    'branch_id' => $information['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                OfficialReceiptInformation::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of spot audits
                $data = $requestData['data'];
            } else {
                // If it's a single spot audit object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single spot audit object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of spot audits (not inside 'data')
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

                $existingRecord = SpotAudit::where([
                    'spot_audit_id' => $audit['spot_audit_id'],
                    'pos_machine_id' => $audit['pos_machine_id'],
                    'branch_id' => $audit['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                SpotAudit::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of spot audit denominations
                $data = $requestData['data'];
            } else {
                // If it's a single spot audit denomination object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single spot audit denomination object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of spot audit denominations (not inside 'data')
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
            foreach ($data as $idx => $denomination) {
                $validator = validator($denomination, $rules);
                if ($validator->fails()) {
                    $failedRequests[$idx] = $denomination;
                    continue;
                }

                $postData = [
                    'spot_audit_denomination_id' => $denomination['spot_audit_denomination_id'] ?? null,
                    'pos_machine_id' => $denomination['pos_machine_id'] ?? null,
                    'branch_id' => $denomination['branch_id'] ?? null,
                    'company_id' => $denomination['company_id'] ?? null,
                    'spot_audit_id' => $denomination['spot_audit_id'] ?? null,
                    'cash_denomination_id' => $denomination['cash_denomination_id'] ?? null,
                    'name' => $denomination['name'] ?? null,
                    'amount' => $denomination['amount'] ?? null,
                    'qty' => $denomination['qty'] ?? null,
                    'total' => $denomination['total'] ?? null,
                    'is_cut_off' => $denomination['is_cut_off'] ?? null,
                    'cut_off_id' => $denomination['cut_off_id'] ?? null,
                    'is_sent_to_server' => $denomination['is_sent_to_server'] ?? null,
                    'shift_number' => $denomination['shift_number'] ?? null,
                    'treg' => $denomination['treg'] ?? null,
                ];

                $existingRecord = SpotAuditDenomination::where([
                    'spot_audit_denomination_id' => $denomination['spot_audit_denomination_id'],
                    'pos_machine_id' => $denomination['pos_machine_id'],
                    'branch_id' => $denomination['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                SpotAuditDenomination::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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
        // Normalize input for backwards compatibility
        if (isset($requestData['data'])) {
            if (is_array($requestData['data'])) {
                // If it's an array of end of day products
                $data = $requestData['data'];
            } else {
                // If it's a single end of day product object
                $data = [$requestData['data']];
            }
        } elseif (is_array($requestData) && self::isAssoc($requestData)) {
            // If it's a single end of day product object (not inside 'data')
            $data = [$requestData];
        } elseif (is_array($requestData)) {
            // If it's an array of end of day products (not inside 'data')
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

                $existingRecord = EndOfDayProduct::where([
                    'end_of_day_product_id' => $product['end_of_day_product_id'],
                    'pos_machine_id' => $product['pos_machine_id'],
                    'branch_id' => $product['branch_id'],
                ])->first();

                if ($existingRecord) {
                    $toUpdate[] = [
                        'model' => $existingRecord,
                        'data' => $postData
                    ];
                } else {
                    $toInsert[] = $postData;
                }
            }

            // Bulk insert new records
            if (!empty($toInsert)) {
                // Add timestamps manually for bulk insert
                $now = now();
                foreach ($toInsert as &$item) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                }
                EndOfDayProduct::insert($toInsert);
            }

            // Bulk update existing records
            foreach ($toUpdate as $item) {
                $item['data']['updated_at'] = now();
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