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

        Schema::create('transactions', function (Blueprint $table) use ($mainDb) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->index('transaction_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->string('control_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->double('gross_sales', 15, 4);
            $table->double('net_sales', 15, 4);
            $table->double('vatable_sales', 15, 4);
            $table->double('vat_exempt_sales', 15, 4);
            $table->double('vat_amount', 15, 4);
            $table->double('discount_amount', 15, 4);
            $table->double('tender_amount', 15, 4);
            $table->double('change', 15, 4);
            $table->double('service_charge', 15, 4);
            $table->string('type')->nullable();
            $table->unsignedBigInteger('cashier_id');
            $table->foreign('cashier_id')->references('id')->on($mainDb . '.users');
            $table->string('cashier_name')->nullable();
            $table->unsignedBigInteger('take_order_id')->nullable();
            $table->foreign('take_order_id')->references('id')->on($mainDb . '.pos_machines');
            $table->string('take_order_name')->nullable();
            $table->double('total_unit_cost', 15, 4);
            $table->double('total_void_amount', 15, 4);
            $table->string('shift_number')->nullable();
            $table->boolean('is_void')->default(false);
            $table->unsignedBigInteger('void_by_id')->nullable();
            $table->foreign('void_by_id')->references('id')->on($mainDb . '.users');
            $table->string('void_by')->nullable();
            $table->dateTime('void_at')->nullable();
            $table->boolean('is_back_out')->default(false);
            $table->unsignedBigInteger('is_back_out_id')->nullable();
            $table->foreign('is_back_out_id')->references('id')->on($mainDb . '.users');
            $table->string('back_out_by')->nullable();
            $table->unsignedBigInteger('charge_account_id')->nullable();
            $table->foreign('charge_account_id')->references('id')->on($mainDb . '.charge_accounts');
            $table->string('charge_account_name')->nullable();
            $table->boolean('is_account_receivable')->default(false);
            $table->boolean('is_sent_to_server')->default(false);
            $table->boolean('is_complete')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->boolean('is_cut_off')->default(false);
            $table->unsignedBigInteger('cut_off_id')->nullable();
            $table->dateTime('cut_off_at')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->string('guest_name')->nullable();
            $table->boolean('is_resume_printed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
