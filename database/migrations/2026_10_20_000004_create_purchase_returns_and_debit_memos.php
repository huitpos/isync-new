<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedBigInteger('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->string('return_number');
            $table->date('return_date');
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'void'])->default('pending');
            $table->unsignedBigInteger('action_by')->nullable();
            $table->foreign('action_by')->references('id')->on('users');
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->foreign('voided_by')->references('id')->on('users');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_return_id');
            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns');
            $table->unsignedBigInteger('purchase_order_item_id');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            $table->double('qty', 15, 4);
            $table->double('unit_price', 15, 4);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('debit_memos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedBigInteger('supplier_invoice_id');
            $table->foreign('supplier_invoice_id')->references('id')->on('supplier_invoices');
            $table->string('memo_number');
            $table->date('memo_date');
            $table->double('total_amount', 15, 4);
            $table->text('remarks')->nullable();
            $table->enum('status', ['issued', 'void'])->default('issued');
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->foreign('voided_by')->references('id')->on('users');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('debit_memo_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debit_memo_id');
            $table->foreign('debit_memo_id')->references('id')->on('debit_memos');
            $table->unsignedBigInteger('supplier_invoice_item_id');
            $table->foreign('supplier_invoice_item_id')->references('id')->on('supplier_invoice_items');
            $table->unsignedBigInteger('purchase_return_item_id')->nullable();
            $table->foreign('purchase_return_item_id')->references('id')->on('purchase_return_items');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            $table->double('qty', 15, 4);
            $table->double('unit_price', 15, 4);
            $table->double('line_amount', 15, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_memo_items');
        Schema::dropIfExists('debit_memos');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};
