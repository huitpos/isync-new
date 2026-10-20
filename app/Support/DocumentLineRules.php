<?php

namespace App\Support;

class DocumentLineRules
{
    public static function itemRules(): array
    {
        return [
            'pr_items' => ['required', 'array', function ($attribute, $value, $fail) {
                $ids = collect($value)->pluck('product_id')->filter(function ($id) {
                    return $id !== null && $id !== '';
                });

                if ($ids->isEmpty()) {
                    $fail('Select at least 1 item.');
                    return;
                }

                if ($ids->count() !== $ids->unique()->count()) {
                    $fail('Each product can only be added once.');
                }
            }],
            'pr_items.*.product_id' => 'required',
            'pr_items.*.quantity' => 'required|numeric|gt:0',
            'pr_items.*.uom_id' => 'required',
        ];
    }

    public static function itemMessages(): array
    {
        return [
            'pr_items.required' => 'Select at least 1 item.',
            'pr_items.*.product_id.required' => 'Select a product.',
            'pr_items.*.quantity.required' => 'Quantity is required.',
            'pr_items.*.quantity.numeric' => 'Quantity must be a number.',
            'pr_items.*.quantity.gt' => 'Quantity must be greater than zero.',
            'pr_items.*.uom_id.required' => 'The product you selected has no UOM. Please assign a UOM first before continuing.',
        ];
    }
}
