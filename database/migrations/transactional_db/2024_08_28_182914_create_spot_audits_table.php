<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spot_audits', function (Blueprint $table) {
            $table->id();

            //spot_audit_id
            $table->unsignedBigInteger('spot_audit_id');
            $table->index('spot_audit_id', 'spot_audit_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');

            //beginning_or
            $table->string('beginning_or');

            //ending_or
            $table->string('ending_or');

            //beginning_amount
            $table->decimal('beginning_amount', 15, 2);

            //ending_amount
            $table->decimal('ending_amount', 15, 2);

            //total_transactions
            $table->integer('total_transactions');

            //gross_sales
            $table->decimal('gross_sales', 15, 2);

            //net_sales
            $table->decimal('net_sales', 15, 2);

            //vatable_sales
            $table->decimal('vatable_sales', 15, 2);

            //vat_exempt_sales
            $table->decimal('vat_exempt_sales', 15, 2);

            //vat_amount
            $table->decimal('vat_amount', 15, 2);

            //vat_expense
            $table->decimal('vat_expense', 15, 2);

            //void_qty
            $table->integer('void_qty');

            //void_amount
            $table->decimal('void_amount', 15, 2);

            //total_change
            $table->decimal('total_change', 15, 2);

            //total_payout
            $table->decimal('total_payout', 15, 2);

            //total_service_charge
            $table->decimal('total_service_charge', 15, 2);

            //total_discount_amount
            $table->decimal('total_discount_amount', 15, 2);

            //total_cost
            $table->decimal('total_cost', 15, 2);

            //safekeeping_amount
            $table->decimal('safekeeping_amount', 15, 2);

            //safekeeping_short_over
            $table->decimal('safekeeping_short_over', 15, 2);

            //total_sk
            $table->decimal('total_sk', 15, 2);

            //total_short_over
            $table->decimal('total_short_over', 15, 2);

            //cashier_id
            $table->unsignedBigInteger('cashier_id');
            $table->index('cashier_id', 'cashier_id');

            //cashier_name
            $table->string('cashier_name');

            //admin_id
            $table->unsignedBigInteger('admin_id');

            //admin_name
            $table->string('admin_name');

            //shift_number
            $table->integer('shift_number');

            //is_cut_off
            $table->boolean('is_cut_off')->default(false);

            //cut_off_id
            $table->unsignedBigInteger('cut_off_id')->nullable();

            //is_sent_to_server
            $table->boolean('is_sent_to_server')->default(false);

            //treg
            $table->string('treg')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spot_audits');
    }
};
