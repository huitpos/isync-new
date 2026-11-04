<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryMovementLog;

class InventoryProcessingPolicy
{
    /**
     * Determine whether the user can view inventory processing module
     */
    public function view(User $user): bool
    {
        return $user->hasRole(['admin', 'inventory_manager', 'inventory_staff']);
    }

    /**
     * Determine whether the user can process inventory movements
     */
    public function process(User $user): bool
    {
        return $user->hasRole(['admin', 'inventory_manager']);
    }

    /**
     * Determine whether the user can revert inventory movements
     */
    public function revert(User $user): bool
    {
        return $user->hasRole(['admin', 'inventory_manager', 'super_admin']);
    }

    /**
     * Determine whether the user can view history
     */
    public function viewHistory(User $user): bool
    {
        return $user->hasRole(['admin', 'inventory_manager', 'inventory_staff']);
    }
}
