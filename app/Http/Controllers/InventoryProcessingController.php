<?php

namespace App\Http\Controllers;

use App\Models\{
    PurchaseDelivery,
    StockTransferDelivery,
    StockTransferOrder,
    ProductDisposal,
    ProductPhysicalCount,
    InventoryMovementLog
};
use App\Services\InventoryProcessor;
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
        $companyId = $user->company_id;

        // Get all branches for the user's company
        $branches = DB::table('branches')
            ->where('company_id', $companyId)
            ->get();

        return view('inventory-tracking.index', [
            'types' => $this->getMovementTypes(),
            'branches' => $branches,
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

        return view('inventory-tracking.show', [
            'movement' => $movement,
            'type' => $type,
            'types' => $this->getMovementTypes(),
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
            return redirect()->route('inventory-tracking.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * Display processing history
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();
        
        // get all branch ids for the user's company
        $branchIds = $branches->pluck('id')->toArray();

        $branch_id = $request->input('branch_id');
        $movement_type = $request->input('movement_type');
        $product_id = $request->input('product_id');
        $perPage = $request->input('per_page', 20);

        $query = InventoryMovementLog::with(['branch', 'product', 'processedBy']);

        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        } else {
            $query->whereIn('branch_id', $branchIds);
        }

        if ($movement_type) {
            $query->where('movement_type', $movement_type);
        }

        if ($product_id) {
            $query->where('product_id', $product_id);
        }

        $logs = $query->orderByDesc('processed_at')->paginate($perPage);

        $products = DB::table('products')
            ->where('status', 'active')
            ->where('company_id', $user->company_id)
            ->select('id', 'name', 'sku')
            ->orderBy('name')
            ->get();

        return view('inventory-tracking.history', [
            'logs' => $logs,
            'types' => $this->getMovementTypes(),
            'currentBranch' => $branch_id,
            'currentType' => $movement_type,
            'currentProduct' => $product_id,
            'products' => $products,
            'branches' => $branches,
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
                ->where('is_void', false);

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
}
