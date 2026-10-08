<?php

namespace App\Services;

use App\Models\{
    PurchaseDelivery,
    StockTransferDelivery,
    StockTransferOrder,
    ProductDisposal,
    ProductPhysicalCount,
    InventoryMovementLog,
    BranchProduct
};
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryProcessor
{
    private const REVERTABLE_TYPES = [
        'purchase_deliveries',
        'stock_transfer_deliveries',
        'stock_transfer_orders',
        'product_disposals',
        'product_physical_counts',
        'transactions',
    ];

    /**
     * Process an inventory movement and update inventory_db
     * 
     * @param string $type Movement type (purchase_deliveries, stock_transfer_deliveries, stock_transfer_orders, product_disposals, product_physical_counts, transactions)
     * @param int $id Movement ID
     * @param int|null $userId User ID who is processing (optional)
     * @return array Result with success/error status and message
     */
    public function processMovement(string $type, int $id, ?int $userId = null): array
    {
        try {
            return match($type) {
                'purchase_deliveries' => $this->processPurchaseDelivery($id, $userId),
                'stock_transfer_deliveries' => $this->processStockTransferDelivery($id, $userId),
                'stock_transfer_orders' => $this->processStockTransferOrder($id, $userId),
                'product_disposals' => $this->processProductDisposal($id, $userId),
                'product_physical_counts' => $this->processProductPhysicalCount($id, $userId),
                'transactions' => $this->processTransaction($id, $userId),
                default => [
                    'success' => false,
                    'message' => "Invalid movement type: {$type}"
                ]
            };
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Revert a processed inventory movement and restore stock levels
     */
    public function revertMovement(string $type, int $objectId, int $branchId, ?int $userId = null): array
    {
        try {
            if (!in_array($type, self::REVERTABLE_TYPES, true)) {
                return ['success' => false, 'message' => "Invalid movement type for revert: {$type}"];
            }

            if ($this->isMovementReverted($type, $objectId, $branchId)) {
                return ['success' => false, 'message' => 'This movement has already been reverted'];
            }

            $logs = InventoryMovementLog::where('movement_type', $type)
                ->where('object_id', $objectId)
                ->where('branch_id', $branchId)
                ->whereNull('reverted_at')
                ->get();

            if ($logs->isEmpty()) {
                return ['success' => false, 'message' => 'No active movement logs found to revert'];
            }

            DB::connection('inventory')->beginTransaction();

            try {
                foreach ($logs as $log) {
                    [$operation, $qty] = $this->getRevertOperation($type, $log);

                    $this->updateInventory(
                        branchId: $log->branch_id,
                        productId: $log->product_id,
                        qty: $qty,
                        operation: $operation,
                        movementType: "{$type}_revert",
                        objectId: $objectId,
                        processedBy: $userId
                    );
                }

                InventoryMovementLog::whereIn('id', $logs->pluck('id'))
                    ->update(['reverted_at' => now()]);

                DB::connection('inventory')->commit();

                return [
                    'success' => true,
                    'message' => 'Movement reverted successfully',
                ];
            } catch (Exception $e) {
                DB::connection('inventory')->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get preview data for reverting a movement
     */
    public function getRevertPreview(string $type, int $objectId, int $branchId): array
    {
        if (!in_array($type, self::REVERTABLE_TYPES, true)) {
            return ['success' => false, 'message' => "Invalid movement type for revert: {$type}"];
        }

        if ($this->isMovementReverted($type, $objectId, $branchId)) {
            return ['success' => false, 'message' => 'This movement has already been reverted'];
        }

        $logs = InventoryMovementLog::where('movement_type', $type)
            ->where('object_id', $objectId)
            ->where('branch_id', $branchId)
            ->whereNull('reverted_at')
            ->with(['product', 'processedBy'])
            ->orderBy('product_id')
            ->get();

        if ($logs->isEmpty()) {
            return ['success' => false, 'message' => 'No active movement logs found to revert'];
        }

        $items = $logs->map(function (InventoryMovementLog $log) use ($type) {
            [$operation, $qty] = $this->getRevertOperation($type, $log);

            return [
                'product_name' => $log->product?->name ?? "Product #{$log->product_id}",
                'previous_qty' => (float) $log->previous_qty,
                'new_qty' => (float) $log->new_qty,
                'revert_operation' => $operation,
                'revert_qty' => $qty,
            ];
        });

        return [
            'success' => true,
            'items' => $items,
            'item_count' => $items->count(),
            'processed_at' => $logs->min('processed_at'),
            'processed_by' => $logs->first()->processedBy?->name ?? 'System',
        ];
    }

    private function isMovementReverted(string $type, int $objectId, int $branchId): bool
    {
        return InventoryMovementLog::where('movement_type', $type)
            ->where('object_id', $objectId)
            ->where('branch_id', $branchId)
            ->whereNotNull('reverted_at')
            ->exists();
    }

    /**
     * @return array{0: string, 1: float}
     */
    private function getRevertOperation(string $type, InventoryMovementLog $log): array
    {
        if ($type === 'product_physical_counts') {
            return ['set', (float) $log->previous_qty];
        }

        if (in_array($type, ['purchase_deliveries', 'stock_transfer_deliveries'], true)) {
            return ['subtract', (float) ($log->new_qty - $log->previous_qty)];
        }

        return ['add', (float) ($log->previous_qty - $log->new_qty)];
    }

    /**
     * Process purchase delivery (ADD to inventory)
     */
    private function processPurchaseDelivery(int $id, ?int $userId): array
    {
        $delivery = PurchaseDelivery::find($id);
        
        if (!$delivery) {
            return ['success' => false, 'message' => 'Purchase delivery not found'];
        }

        if ($delivery->status !== 'approved') {
            return ['success' => false, 'message' => 'Purchase delivery must be approved first'];
        }

        if ($this->isMovementReverted('purchase_deliveries', $delivery->id, $delivery->branch_id)) {
            return ['success' => false, 'message' => 'This movement has been reverted and cannot be processed again'];
        }

        if ($delivery->inventory_processed) {
            return ['success' => false, 'message' => 'This purchase delivery has already been processed'];
        }

        // Get items and process
        $items = $delivery->items;
        if ($items->isEmpty()) {
            return ['success' => false, 'message' => 'Purchase delivery has no items'];
        }

        DB::connection('inventory')->beginTransaction();
        try {
            foreach ($items as $item) {
                $this->updateInventory(
                    branchId: $delivery->branch_id,
                    productId: $item->product_id,
                    qty: $item->qty,
                    operation: 'add',
                    movementType: 'purchase_deliveries',
                    objectId: $delivery->id,
                    processedBy: $userId
                );
            }

            // Mark as processed
            $delivery->update(['inventory_processed' => true]);

            DB::connection('inventory')->commit();

            return [
                'success' => true,
                'message' => "Purchase delivery #{$delivery->pd_number} processed successfully"
            ];
        } catch (Exception $e) {
            DB::connection('inventory')->rollBack();
            throw $e;
        }
    }

    /**
     * Process stock transfer delivery (ADD to destination inventory)
     */
    private function processStockTransferDelivery(int $id, ?int $userId): array
    {
        $delivery = StockTransferDelivery::find($id);
        
        if (!$delivery) {
            return ['success' => false, 'message' => 'Stock transfer delivery not found'];
        }

        if ($delivery->status !== 'approved') {
            return ['success' => false, 'message' => 'Stock transfer delivery must be approved first'];
        }

        if ($this->isMovementReverted('stock_transfer_deliveries', $delivery->id, $delivery->destination_branch_id)) {
            return ['success' => false, 'message' => 'This movement has been reverted and cannot be processed again'];
        }

        if ($delivery->inventory_processed) {
            return ['success' => false, 'message' => 'This transfer delivery has already been processed'];
        }

        $items = $delivery->items;
        if ($items->isEmpty()) {
            return ['success' => false, 'message' => 'Transfer delivery has no items'];
        }

        DB::connection('inventory')->beginTransaction();
        try {
            foreach ($items as $item) {
                // Add to destination
                $this->updateInventory(
                    branchId: $delivery->destination_branch_id,
                    productId: $item->product_id,
                    qty: $item->qty,
                    operation: 'add',
                    movementType: 'stock_transfer_deliveries',
                    objectId: $delivery->id,
                    processedBy: $userId
                );
            }

            $delivery->update(['inventory_processed' => true]);
            DB::connection('inventory')->commit();

            return [
                'success' => true,
                'message' => 'Stock transfer delivery processed successfully'
            ];
        } catch (Exception $e) {
            DB::connection('inventory')->rollBack();
            throw $e;
        }
    }

    /**
     * Process stock transfer order (SUBTRACT from source inventory)
     */
    private function processStockTransferOrder(int $id, ?int $userId): array
    {
        $order = StockTransferOrder::find($id);

        if (!$order) {
            return ['success' => false, 'message' => 'Stock transfer order not found'];
        }

        if ($order->status !== 'approved') {
            return ['success' => false, 'message' => 'Stock transfer order must be approved first'];
        }

        if ($this->isMovementReverted('stock_transfer_orders', $order->id, $order->source_branch_id)) {
            return ['success' => false, 'message' => 'This movement has been reverted and cannot be processed again'];
        }

        if ($order->inventory_processed) {
            return ['success' => false, 'message' => 'This transfer order has already been processed'];
        }

        $items = $order->items;
        if ($items->isEmpty()) {
            return ['success' => false, 'message' => 'Transfer order has no items'];
        }

        DB::connection('inventory')->beginTransaction();
        try {
            foreach ($items as $item) {
                $this->updateInventory(
                    branchId: $order->source_branch_id,
                    productId: $item->product_id,
                    qty: $item->quantity,
                    operation: 'subtract',
                    movementType: 'stock_transfer_orders',
                    objectId: $order->id,
                    processedBy: $userId
                );
            }

            $order->update(['inventory_processed' => true]);
            DB::connection('inventory')->commit();

            return [
                'success' => true,
                'message' => 'Stock transfer order processed successfully'
            ];
        } catch (Exception $e) {
            DB::connection('inventory')->rollBack();
            throw $e;
        }
    }

    /**
     * Process product disposal (SUBTRACT from inventory)
     */
    private function processProductDisposal(int $id, ?int $userId): array
    {
        $disposal = ProductDisposal::find($id);

        if (!$disposal) {
            return ['success' => false, 'message' => 'Product disposal not found'];
        }

        if ($disposal->status !== 'approved') {
            return ['success' => false, 'message' => 'Product disposal must be approved first'];
        }

        if ($this->isMovementReverted('product_disposals', $disposal->id, $disposal->branch_id)) {
            return ['success' => false, 'message' => 'This movement has been reverted and cannot be processed again'];
        }

        if ($disposal->inventory_processed) {
            return ['success' => false, 'message' => 'This disposal has already been processed'];
        }

        $items = $disposal->items;

        if ($items->isEmpty()) {
            return ['success' => false, 'message' => 'Disposal has no items'];
        }

        DB::connection('inventory')->beginTransaction();
        try {
            foreach ($items as $item) {
                $this->updateInventory(
                    branchId: $disposal->branch_id,
                    productId: $item->product_id,
                    qty: $item->quantity,
                    operation: 'subtract',
                    movementType: 'product_disposals',
                    objectId: $disposal->id,
                    processedBy: $userId
                );
            }

            $disposal->update(['inventory_processed' => true]);
            DB::connection('inventory')->commit();

            return [
                'success' => true,
                'message' => 'Product disposal processed successfully'
            ];
        } catch (Exception $e) {
            DB::connection('inventory')->rollBack();
            throw $e;
        }
    }

    /**
     * Process product physical count (SET inventory to counted qty)
     */
    private function processProductPhysicalCount(int $id, ?int $userId): array
    {
        $count = ProductPhysicalCount::find($id);
        
        if (!$count) {
            return ['success' => false, 'message' => 'Physical count not found'];
        }

        if ($count->status !== 'approved') {
            return ['success' => false, 'message' => 'Physical count must be approved first'];
        }

        if ($this->isMovementReverted('product_physical_counts', $count->id, $count->branch_id)) {
            return ['success' => false, 'message' => 'This movement has been reverted and cannot be processed again'];
        }

        if ($count->inventory_processed) {
            return ['success' => false, 'message' => 'This physical count has already been processed'];
        }

        $items = $count->items;
        if ($items->isEmpty()) {
            return ['success' => false, 'message' => 'Physical count has no items'];
        }

        DB::connection('inventory')->beginTransaction();
        try {
            foreach ($items as $item) {
                // For physical counts, we SET the quantity (not add/subtract)
                $this->updateInventory(
                    branchId: $count->branch_id,
                    productId: $item->product_id,
                    qty: $item->quantity, // Use counted quantity
                    operation: 'set',
                    movementType: 'product_physical_counts',
                    objectId: $count->id,
                    processedBy: $userId
                );
            }

            $count->update(['inventory_processed' => true]);
            DB::connection('inventory')->commit();

            return [
                'success' => true,
                'message' => 'Physical count processed successfully'
            ];
        } catch (Exception $e) {
            DB::connection('inventory')->rollBack();
            throw $e;
        }
    }

    /**
     * Process transaction (SUBTRACT completed orders from inventory)
     */
    private function processTransaction(int $id, ?int $userId): array
    {
        // Get transaction with completed orders from transactional_db
        $transaction = DB::connection('transactional_db')
            ->table('transactions')
            ->find($id);

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        if ($this->isMovementReverted('transactions', $transaction->transaction_id, $transaction->branch_id)) {
            return ['success' => false, 'message' => 'This movement has been reverted and cannot be processed again'];
        }

        if ($transaction->inventory_processed ?? false) {
            return ['success' => false, 'message' => 'This transaction has already been processed'];
        }

        // Get completed orders for this transaction, grouped by product
        $orders = DB::connection('transactional_db')
            ->table('orders')
            ->where('transaction_id', $transaction->transaction_id)
            ->where('branch_id', $transaction->branch_id)
            ->where('pos_machine_id', $transaction->pos_machine_id)
            ->where('is_completed', true)
            ->where('is_void', false)
            ->where('is_back_out', false)
            ->select('product_id', DB::raw('SUM(qty) as qty'))
            ->groupBy('product_id')
            ->get();

        if ($orders->isEmpty()) {
            return ['success' => false, 'message' => 'Transaction has no completed orders'];
        }

        DB::connection('inventory')->beginTransaction();

        try {
            foreach ($orders as $order) {
                $log = InventoryMovementLog::where('object_id', $transaction->transaction_id)
                    ->where('product_id', $order->product_id)
                    ->where('branch_id', $transaction->branch_id)
                    ->where('movement_type', 'transactions')
                    ->whereNull('reverted_at')
                    ->first();

                if ($log) {
                    continue;
                }

                $this->updateInventory(
                    branchId: $transaction->branch_id,
                    productId: $order->product_id,
                    qty: $order->qty,
                    operation: 'subtract',
                    movementType: 'transactions',
                    objectId: $transaction->transaction_id,
                    processedBy: $userId
                );
            }

            DB::connection('transactional_db')
                ->table('transactions')
                ->where('id', $transaction->id)
                ->update(['inventory_processed' => true]);

            DB::connection('inventory')->commit();

            return [
                'success' => true,
                'message' => 'Transaction processed successfully'
            ];
        } catch (Exception $e) {
            DB::connection('inventory')->rollBack();
            throw $e;
        }
    }

    /**
     * Update inventory in inventory_db and create log entry
     */
    private function updateInventory(
        int $branchId,
        int $productId,
        float $qty,
        string $operation, // 'add', 'subtract', 'set'
        string $movementType,
        int $objectId,
        ?int $processedBy = null
    ): void {
        // Get current inventory from inventory_db
        $branchProduct = DB::connection('inventory')
            ->table('branch_product')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (!$branchProduct) {
            $branchProduct = DB::table('branch_product')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();
        }

        $previousQty = $branchProduct?->stock ?? 0;

        // Calculate new quantity
        $newQty = match($operation) {
            'add' => $previousQty + $qty,
            'subtract' => $previousQty - $qty,
            'set' => $qty,
            default => $previousQty
        };

        // Upsert to branch_product
        DB::connection('inventory')
            ->table('branch_product')
            ->updateOrInsert(
                ['branch_id' => $branchId, 'product_id' => $productId],
                [
                    'stock' => $newQty,
                    'updated_at' => now(),
                    'created_at' => $branchProduct ? $branchProduct->created_at : now()
                ]
            );

        // Create log entry
        InventoryMovementLog::create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'movement_type' => $movementType,
            'object_id' => $objectId,
            'previous_qty' => $previousQty,
            'new_qty' => $newQty,
            'processed_by' => $processedBy,
            'processed_at' => now(),
        ]);
    }
}
