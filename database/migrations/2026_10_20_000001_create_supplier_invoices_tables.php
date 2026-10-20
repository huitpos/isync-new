<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE purchase_deliveries MODIFY sales_invoice_number VARCHAR(255) NULL');

        Schema::table('purchase_deliveries', function (Blueprint $table) {
            $table->boolean('is_legacy_billed')->default(false)->after('sales_invoice_number');
        });

        DB::table('purchase_deliveries')
            ->whereNotNull('sales_invoice_number')
            ->where('sales_invoice_number', '!=', '')
            ->update(['is_legacy_billed' => true]);

        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedBigInteger('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->double('total_amount', 15, 4);
            $table->enum('status', ['draft', 'matched', 'variance', 'accepted', 'void'])->default('draft');
            $table->unsignedBigInteger('accepted_by')->nullable();
            $table->foreign('accepted_by')->references('id')->on('users');
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->foreign('voided_by')->references('id')->on('users');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();

            $table->index(['supplier_id', 'invoice_number']);
            $table->index('status');
        });

        Schema::create('supplier_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_invoice_id');
            $table->foreign('supplier_invoice_id')->references('id')->on('supplier_invoices');
            $table->unsignedBigInteger('purchase_order_item_id');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            $table->double('qty', 15, 4);
            $table->double('unit_price', 15, 4);
            $table->double('po_unit_price', 15, 4);
            $table->double('line_amount', 15, 4);
            $table->string('variance_type')->nullable();
            $table->text('variance_reason')->nullable();
            $table->unsignedBigInteger('variance_approved_by')->nullable();
            $table->foreign('variance_approved_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('supplier_invoice_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_invoice_item_id');
            $table->foreign('supplier_invoice_item_id')->references('id')->on('supplier_invoice_items');
            $table->unsignedBigInteger('purchase_delivery_item_id');
            $table->foreign('purchase_delivery_item_id')->references('id')->on('purchase_delivery_items');
            $table->double('qty', 15, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_allocations');
        Schema::dropIfExists('supplier_invoice_items');
        Schema::dropIfExists('supplier_invoices');

        Schema::table('purchase_deliveries', function (Blueprint $table) {
            $table->dropColumn('is_legacy_billed');
        });
    }
};
