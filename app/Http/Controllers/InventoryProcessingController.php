<?php

namespace App\Http\Controllers;

use App\Models\{
    PurchaseDelivery,
    StockTransferDelivery,
    StockTransferOrder,
    ProductDisposal,
    ProductPhysicalCount,
    InventoryMovementLog,
    Company
};
use App\DataTables\BranchProductMasterListDataTable;
use App\DataTables\InventoryProcessingHistoryDataTable;
use App\Exports\BranchProductMasterListExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\InventoryProcessor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryProcessingController extends Controller
{
    private InventoryProcessor $inventoryProcessor;

    public function __construct(InventoryProcessor $inventoryProcessor)
    {
        $this->inventoryProcessor = $inventoryProcessor;
    }

    /**
     * Display a listing of unprocessed inventory movements
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        //get branch ids from user
        $branchIds = $user->branches->pluck('id')->toArray();

        // Get all branches for the user's company
        $branches = DB::table('branches')
            ->whereIn('id', $branchIds)
            ->get();

        $company = Company::find($user->company_id);

        return view('inventory-tracking.index', [
            'types' => $this->getMovementTypes(),
            'branches' => $branches,
            'company' => $company,
        ]);
    }

    /**
     * Get movements data via AJAX
     */
    public function getMovementsData(Request $request)
    {
        $type = $request->input('type');
        $branch_id = $request->input('branch_id');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        // Type is required
        if (!$type || !in_array($type, array_keys($this->getMovementTypes()))) {
            return response()->json(['error' => 'Invalid movement type'], 400);
        }

        $user = Auth::user();
        $companyId = $user->company_id;

        if (!$branch_id) {
            // If no branch is specified, get all branches for the user's company
            $branches = DB::table('branches')
                ->where('company_id', $companyId)
                ->get();

            $branchIds = $branches->pluck('id')->toArray();
        } else {
            $branchIds = [$branch_id];
        }

        $movements = $this->getMovementsByType($type, $branchIds, $page, $perPage);

        return response()->json($movements);
    }

    /**
     * Display details of a specific movement
     */
    public function show($type, $id)
    {
        $movement = $this->getMovement($type, $id);
        if (!$movement) {
            abort(404, 'Movement not found');
        }

        $company = Company::find(Auth::user()->company_id);

        return view('inventory-tracking.show', [
            'movement' => $movement,
            'type' => $type,
            'types' => $this->getMovementTypes(),
            'company' => $company,
        ]);
    }

    /**
     * Process an inventory movement
     */
    public function process(Request $request, $type, $id)
    {
        // $this->authorize('process', InventoryMovementLog::class);

        $result = $this->inventoryProcessor->processMovement(
            type: $type,
            id: $id,
            userId: Auth::id()
        );

        if ($result['success']) {
            return redirect()->route('inventory-tracking.index', array_filter([
                'type' => $request->input('return_type'),
                'branch_id' => $request->input('return_branch_id'),
                'page' => $request->input('return_page'),
            ]))
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * Display processing history
     */
    public function history(Request $request, InventoryProcessingHistoryDataTable $dataTable)
    {
        addVendors(['datatables']);

        $user = Auth::user();
        $company = Company::find($user->company_id);

        $branchIds = $user->branches->pluck('id')->toArray();

        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('company_id', $user->company_id)
            ->whereIn('id', $branchIds)
            ->orderBy('name')
            ->get();

        return $dataTable->with([
            'movement_type' => $request->query('movement_type'),
            'branch_id' => $request->query('branch_id'),
            'product_id' => $request->query('product_id'),
            'branch_ids' => $branchIds,
            'company_slug' => $company->slug,
            'movement_types' => $this->getMovementTypes(),
        ])->render('inventory-tracking.history', [
            'types' => $this->getMovementTypes(),
            'branches' => $branches,
            'company' => $company,
        ]);
    }

    /**
     * Display branch product master list with current stock on hand
     */
    public function masterList(Request $request, BranchProductMasterListDataTable $dataTable)
    {
        addVendors(['datatables']);

        $user = Auth::user();
        $company = Company::find($user->company_id);
        $branchIds = $user->branches->pluck('id')->toArray();

        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('company_id', $user->company_id)
            ->whereIn('id', $branchIds)
            ->orderBy('name')
            ->get();

        return $dataTable->with([
            'branch_id' => $request->query('branch_id'),
            'branch_ids' => $branchIds,
            'company_id' => $user->company_id,
            'product_name' => $request->query('product_name'),
        ])->render('inventory-tracking.master-list', [
            'branches' => $branches,
            'company' => $company,
        ]);
    }

    /**
     * Export branch product master list to Excel based on current filters
     */
    public function masterListExport(Request $request)
    {
        $user = Auth::user();
        $branchIds = $user->branches->pluck('id')->toArray();

        return Excel::download(
            new BranchProductMasterListExport(
                $user->company_id,
                $branchIds,
                $request->query('branch_id') ? (int) $request->query('branch_id') : null,
                $request->query('product_name'),
            ),
            'Stock Master List - ' . Carbon::now()->format('Y-m-d') . '.xlsx',
        );
    }

    /**
     * Display branch inventory report (most/least stock, best selling)
     */
    public function inventoryReport(Request $request)
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $branchIds = $user->branches->pluck('id')->toArray();

        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('company_id', $user->company_id)
            ->whereIn('id', $branchIds)
            ->orderBy('name')
            ->get();

        $view = $request->query('view', 'most_stock');
        if (!in_array($view, ['most_stock', 'least_stock', 'best_selling'], true)) {
            $view = 'most_stock';
        }

        $branchId = $request->query('branch_id');
        $filterBranchIds = $branchId ? [(int) $branchId] : $branchIds;

        if ($branchId && !in_array((int) $branchId, $branchIds, true)) {
            abort(403);
        }

        $dateParam = $request->input('date_range');
        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            [$startDate, $endDate] = explode(' - ', $dateParam);
            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        $products = $this->getInventoryReportData($view, $filterBranchIds, !$branchId, $startDate, $endDate);

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate');
        $endDateParam = $request->input('endDate');

        return view('inventory-tracking.inventory-report', [
            'company' => $company,
            'branches' => $branches,
            'branchId' => $branchId,
            'view' => $view,
            'products' => $products,
            'aggregateBranches' => !$branchId,
            'selectedRangeParam' => $selectedRangeParam,
            'startDateParam' => $startDateParam,
            'endDateParam' => $endDateParam,
        ]);
    }

    /**
     * Search active products via AJAX
     */
    public function searchProducts(Request $request)
    {
        $search = $request->input('q', '');
        $limit = $request->input('limit', 15);

        $query = DB::table('products')
            ->where('status', 'active')
            ->select('id', 'name', 'sku');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $products->map(fn($p) => [
                'id' => $p->id,
                'text' => "{$p->name} (SKU: {$p->sku})"
            ])->toArray()
        ]);
    }

    /**
     * Get movements from database for a specific type with pagination
     */
    private function getMovementsByType($type, $branchIds = null, $page = 1, $perPage = 15)
    {
        $status = 'approved';
        $skip = ($page - 1) * $perPage;

        if ($type === 'purchase_deliveries') {
            $query = PurchaseDelivery::where('inventory_processed', false)
                ->join('branches', 'purchase_deliveries.branch_id', '=', 'branches.id')
                ->select('purchase_deliveries.*', 'branches.name as branch_name')
                ->where('purchase_deliveries.status', $status);
            
            if ($branchIds) $query->whereIn('branch_id', $branchIds);

            $total = $query->count();
            $data = $query->orderBy('purchase_deliveries.created_at')->skip($skip)->take($perPage)->get()
                ->map(fn($m) => [
                    ...$m->toArray(),
                    'type' => 'purchase_deliveries',
                    'description' => "PD #{$m->pd_number}",
                    'branch' => $m->branch_name ?? 'N/A',
                ])->toArray();

        } elseif ($type === 'stock_transfer_deliveries') {
            $query = StockTransferDelivery::where('inventory_processed', false)
                ->join('branches', 'stock_transfer_deliveries.destination_branch_id', '=', 'branches.id')
                ->select('stock_transfer_deliveries.*', 'branches.name as branch_name')
                ->where('stock_transfer_deliveries.status', $status);

            if ($branchIds) $query->whereIn('destination_branch_id', $branchIds);

            $total = $query->count();
            $data = $query->orderBy('stock_transfer_deliveries.created_at')->skip($skip)->take($perPage)->get()
                ->map(fn($m) => [
                    ...$m->toArray(),
                    'type' => 'stock_transfer_deliveries',
                    'description' => "STD #{$m->std_number}",
                    'branch' => $m->branch_name ?? 'N/A',
                ])->toArray();

        } elseif ($type === 'stock_transfer_orders') {
            $query = StockTransferOrder::where('inventory_processed', false)
                ->join('branches', 'stock_transfer_orders.source_branch_id', '=', 'branches.id')
                ->select('stock_transfer_orders.*', 'branches.name as branch_name')
                ->where('stock_transfer_orders.status', $status);

            if ($branchIds) $query->whereIn('source_branch_id', $branchIds);

            $total = $query->count();
            $data = $query->orderBy('stock_transfer_orders.created_at')->skip($skip)->take($perPage)->get()
                ->map(fn($m) => [
                    ...$m->toArray(),
                    'type' => 'stock_transfer_orders',
                    'description' => "STO #{$m->sto_number}",
                    'branch' => $m->branch_name ?? 'N/A',
                ])->toArray();

        } elseif ($type === 'product_disposals') {
            $query = ProductDisposal::where('inventory_processed', false)
                ->join('branches', 'product_disposals.branch_id', '=', 'branches.id')
                ->select('product_disposals.*', 'branches.name as branch_name')
                ->where('product_disposals.status', $status);

            if ($branchIds) $query->whereIn('branch_id', $branchIds);

            $total = $query->count();
            $data = $query->orderByDesc('created_at')->skip($skip)->take($perPage)->get()
                ->map(fn($m) => [
                    ...$m->toArray(),
                    'type' => 'product_disposals',
                    'description' => "Product Disposal",
                    'branch' => $m->branch_name ?? 'N/A',
                ])->toArray();
        } elseif ($type === 'product_physical_counts') {
            $query = ProductPhysicalCount::where('inventory_processed', false)
                ->join('branches', 'product_physical_counts.branch_id', '=', 'branches.id')
                ->select('product_physical_counts.*', 'branches.name as branch_name')
                ->where('product_physical_counts.status', $status);

            if ($branchIds) $query->whereIn('branch_id', $branchIds);

            $total = $query->count();
            $data = $query->orderByDesc('created_at')->skip($skip)->take($perPage)->get()
                ->map(fn($m) => [
                    ...$m->toArray(),
                    'type' => 'product_physical_counts',
                    'description' => "Physical Count",
                    'branch' => $m->branch_name ?? 'N/A',
                ])->toArray();
        } elseif ($type === 'transactions') {
            $query = DB::connection('transactional_db')
                ->table('transactions')
                ->join('isync.branches', 'transactions.branch_id', '=', 'isync.branches.id')
                ->select('transactions.*', 'branches.name as branch_name')
                ->where('inventory_processed', false)
                ->where('is_complete', true)
                ->where('transactions.receipt_number', '!=', null)
                ->where('is_void', false)
                ->where('is_back_out', false);

            if ($branchIds) {
                $query->whereIn('branch_id', $branchIds);
            }

            $total = $query->count();
            $transactions = $query->orderBy('completed_at')->skip($skip)->take($perPage)->get()
                ->map(fn($t) => [
                    ...(array) $t,
                    'type' => 'transactions',
                    'description' => "SI #{$t->receipt_number}",
                    'created_at' => $t->created_at,
                    'branch' => $t->branch_name ?? 'N/A',
                ])->toArray();
            $data = $transactions;
        } else {
            return ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Get a specific movement
     */
    private function getMovement($type, $id)
    {
        if ($type === 'transactions') {
            return DB::connection('transactional_db')
                ->table('transactions')
                ->where('id', $id)
                ->first();
        }
        return match($type) {
            'purchase_deliveries' => PurchaseDelivery::with('items', 'branch', 'purchaseOrder')->find($id),
            'stock_transfer_deliveries' => StockTransferDelivery::with('items', 'branch', 'sourceBranch')->find($id),
            'stock_transfer_orders' => StockTransferOrder::with('items', 'branch', 'sourceBranch')->find($id),
            'product_disposals' => ProductDisposal::with('items', 'branch')->find($id),
            'product_physical_counts' => ProductPhysicalCount::with('items', 'branch')->find($id),
            default => null
        };
    }

    /**
     * Get available movement types
     */
    private function getMovementTypes()
    {
        return [
            'purchase_deliveries' => 'Purchase Deliveries',
            'stock_transfer_deliveries' => 'Transfer Deliveries',
            'stock_transfer_orders' => 'Transfer Orders',
            'product_disposals' => 'Product Disposals',
            'product_physical_counts' => 'Physical Counts',
            'transactions' => 'POS Transactions',
        ];
    }

    private function getInventoryReportData(
        string $view,
        array $branchIds,
        bool $aggregateBranches,
        string $startDate,
        string $endDate
    ): array {
        if (empty($branchIds)) {
            return [];
        }

        $inventoryDb = config('database.connections.inventory.database');
        $isyncDb = config('database.connections.mysql.database');
        $branchIdList = implode(',', array_map('intval', $branchIds));

        if ($view === 'best_selling') {
            return $this->getBestSellingReportData(
                $inventoryDb,
                $isyncDb,
                $branchIdList,
                $aggregateBranches,
                $startDate,
                $endDate
            );
        }

        $orderDirection = $view === 'least_stock' ? 'ASC' : 'DESC';

        if ($aggregateBranches) {
            $query = "
                SELECT
                    bp.product_id,
                    p.name AS product_name,
                    p.sku,
                    SUM(bp.stock) AS stock
                FROM {$inventoryDb}.branch_product bp
                INNER JOIN {$isyncDb}.products p ON bp.product_id = p.id
                WHERE bp.branch_id IN ({$branchIdList})
                GROUP BY bp.product_id, p.name, p.sku
                ORDER BY stock {$orderDirection}
                LIMIT 100
            ";
        } else {
            $query = "
                SELECT
                    bp.product_id,
                    p.name AS product_name,
                    p.sku,
                    b.name AS branch_name,
                    bp.stock
                FROM {$inventoryDb}.branch_product bp
                INNER JOIN {$isyncDb}.products p ON bp.product_id = p.id
                INNER JOIN {$isyncDb}.branches b ON bp.branch_id = b.id
                WHERE bp.branch_id IN ({$branchIdList})
                ORDER BY bp.stock {$orderDirection}
                LIMIT 100
            ";
        }

        return DB::select($query);
    }

    private function getBestSellingReportData(
        string $inventoryDb,
        string $isyncDb,
        string $branchIdList,
        bool $aggregateBranches,
        string $startDate,
        string $endDate
    ): array {
        $outboundSubquery = "
            SELECT
                product_id,
                branch_id,
                SUM(CASE WHEN new_qty < previous_qty THEN previous_qty - new_qty ELSE 0 END) AS total_outbound_qty,
                SUM(CASE WHEN movement_type = 'transactions' AND new_qty < previous_qty
                    THEN previous_qty - new_qty ELSE 0 END) AS sales_qty,
                SUM(CASE WHEN movement_type = 'stock_transfer_orders' AND new_qty < previous_qty
                    THEN previous_qty - new_qty ELSE 0 END) AS transfer_out_qty,
                SUM(CASE WHEN movement_type = 'product_disposals' AND new_qty < previous_qty
                    THEN previous_qty - new_qty ELSE 0 END) AS disposal_qty
            FROM {$inventoryDb}.inventory_movement_logs
            WHERE movement_type IN ('transactions', 'stock_transfer_orders', 'product_disposals')
                AND processed_at BETWEEN '{$startDate}' AND '{$endDate}'
                AND branch_id IN ({$branchIdList})
            GROUP BY product_id, branch_id
        ";

        if ($aggregateBranches) {
            $query = "
                SELECT
                    bp.product_id,
                    p.name AS product_name,
                    p.sku,
                    SUM(bp.stock) AS stock,
                    COALESCE(SUM(outbound.total_outbound_qty), 0) AS total_outbound_qty,
                    COALESCE(SUM(outbound.sales_qty), 0) AS sales_qty,
                    COALESCE(SUM(outbound.transfer_out_qty), 0) AS transfer_out_qty,
                    COALESCE(SUM(outbound.disposal_qty), 0) AS disposal_qty
                FROM {$inventoryDb}.branch_product bp
                INNER JOIN {$isyncDb}.products p ON bp.product_id = p.id
                LEFT JOIN ({$outboundSubquery}) outbound
                    ON bp.product_id = outbound.product_id
                    AND bp.branch_id = outbound.branch_id
                WHERE bp.branch_id IN ({$branchIdList})
                GROUP BY bp.product_id, p.name, p.sku
                ORDER BY total_outbound_qty DESC
                LIMIT 100
            ";
        } else {
            $query = "
                SELECT
                    bp.product_id,
                    p.name AS product_name,
                    p.sku,
                    b.name AS branch_name,
                    bp.stock,
                    COALESCE(outbound.total_outbound_qty, 0) AS total_outbound_qty,
                    COALESCE(outbound.sales_qty, 0) AS sales_qty,
                    COALESCE(outbound.transfer_out_qty, 0) AS transfer_out_qty,
                    COALESCE(outbound.disposal_qty, 0) AS disposal_qty
                FROM {$inventoryDb}.branch_product bp
                INNER JOIN {$isyncDb}.products p ON bp.product_id = p.id
                INNER JOIN {$isyncDb}.branches b ON bp.branch_id = b.id
                LEFT JOIN ({$outboundSubquery}) outbound
                    ON bp.product_id = outbound.product_id
                    AND bp.branch_id = outbound.branch_id
                WHERE bp.branch_id IN ({$branchIdList})
                ORDER BY total_outbound_qty DESC
                LIMIT 100
            ";
        }

        return DB::select($query);
    }
}
