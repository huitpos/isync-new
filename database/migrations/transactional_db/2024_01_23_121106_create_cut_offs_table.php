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

        Schema::create('cut_offs', function (Blueprint $table) use ($mainDb, $transactionalDb) {
            $table->id();
            $table->unsignedBigInteger('cut_off_id');
            $table->index('cut_off_id');
            $table->unsignedBigInteger('end_of_day_id');
            $table->foreign('end_of_day_id')->references('end_of_day_id')->on($transactionalDb . '.end_of_days');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->string('beginning_or')->nullable();
            $table->string('ending_or')->nullable();
            $table->double('beginning_amount', 15, 4);
            $table->double('ending_amount', 15, 4);
            $table->integer('total_transactions');
            $table->double('gross_sales', 15, 4);
            $table->double('net_sales', 15, 4);
            $table->double('vatable_sales', 15, 4);
            $table->double('vat_exempt_sales', 15, 4);
            $table->double('vat_amount', 15, 4);
            $table->double('vat_expense', 15, 4);
            $table->double('void_amount', 15, 4);
            $table->double('total_cash_payments', 15, 4);
            $table->double('total_card_payments', 15, 4);
            $table->double('total_online_payments', 15, 4);
            $table->double('total_ar_payments', 15, 4);
            $table->double('total_mobile_payments', 15, 4);
            $table->double('total_charge', 15, 4);
            $table->integer('senior_count');
            $table->double('senior_amount', 15, 4);
            $table->integer('pwd_count');
            $table->double('pwd_amount', 15, 4);
            $table->integer('others_count');
            $table->double('others_amount', 15, 4);
            $table->text('others_json')->nullable();
            $table->double('total_payout', 15, 4);
            $table->double('total_service_charge', 15, 4);
            $table->double('total_discount_amount', 15, 4);
            $table->double('total_ar_cash_redeemed_amount', 15, 4);
            $table->double('total_ar_card_redeemed_amount', 15, 4);
            $table->double('total_cost', 15, 4);
            $table->double('total_sk', 15, 4);
            $table->unsignedBigInteger('cashier_id');
            $table->foreign('cashier_id')->references('id')->on($mainDb . '.users');
            $table->string('cashier_name')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on($mainDb . '.users');
            $table->string('admin_name')->nullable();
            $table->string('shift_number');
            $table->boolean('is_sent_to_server')->default(false);
            $table->string('treg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cut_offs');
    }
};
