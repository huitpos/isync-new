<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        DB::statement('ALTER TABLE purchase_requests MODIFY supplier_id BIGINT UNSIGNED NULL');

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });

        Schema::create('purchase_request_quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['purchase_request_id', 'supplier_id'], 'pr_quotations_request_supplier_unique');
        });

        Schema::create('purchase_request_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_quotation_id');
            $table->foreign('purchase_request_quotation_id', 'pr_quote_items_quotation_fk')
                ->references('id')->on('purchase_request_quotations');
            $table->unsignedBigInteger('purchase_request_item_id');
            $table->foreign('purchase_request_item_id', 'pr_quote_items_request_item_fk')
                ->references('id')->on('purchase_request_items');
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->decimal('available_qty', 15, 4)->nullable();
            $table->boolean('is_awarded')->default(false);
            $table->timestamps();

            $table->unique(
                ['purchase_request_quotation_id', 'purchase_request_item_id'],
                'pr_quote_items_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_quotation_items');
        Schema::dropIfExists('purchase_request_quotations');

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        DB::statement('ALTER TABLE purchase_requests MODIFY supplier_id BIGINT UNSIGNED NOT NULL');

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });
    }
};
