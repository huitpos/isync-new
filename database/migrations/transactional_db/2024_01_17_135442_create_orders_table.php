<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'transactional_db';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mainDb = env('DB_DATABASE', 'forge');
        $transactionalDb = env('DB_TRANSACTIONAL_DATABASE', 'forge');

        Schema::create('orders', function (Blueprint $table) use ($mainDb, $transactionalDb) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->index('order_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('transaction_id')->on($transactionalDb . '.transactions');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on($mainDb . '.products');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('abbreviation')->nullable();
            $table->double('cost', 15, 4);
            $table->double('qty', 15, 4);
            $table->double('amount', 15, 4);
            $table->double('original_amount', 15, 4);
            $table->double('gross', 15, 4);
            $table->double('total', 15, 4);
            $table->double('total_cost', 15, 4);
            $table->boolean('is_vatable')->default(false);
            $table->double('vat_amount', 15, 4);
            $table->double('vatable_sales', 15, 4);
            $table->double('vat_exempt_sales', 15, 4);
            $table->double('discount_amount', 15, 4);
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on($mainDb . '.departments');
            $table->string('department_name')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on($mainDb . '.categories');
            $table->string('category_name')->nullable();
            $table->unsignedBigInteger('subcategory_id');
            $table->foreign('subcategory_id')->references('id')->on($mainDb . '.subcategories');
            $table->string('subcategory_name')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->string('unit_name')->nullable();
            $table->boolean('is_void')->default(false);
            $table->string('void_by')->nullable();
            $table->dateTime('void_at')->nullable();
            $table->boolean('is_back_out')->default(false);
            $table->unsignedBigInteger('is_back_out_id')->nullable();
            $table->foreign('is_back_out_id')->references('id')->on($mainDb . '.users');
            $table->string('back_out_by')->nullable();
            $table->double('min_amount_sold', 15, 4);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_sent_to_server')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->boolean('is_cut_off')->default(false);
            $table->unsignedBigInteger('cut_off_id')->nullable();
            $table->dateTime('cut_off_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
