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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();

            //payout_id
            $table->unsignedBigInteger('payout_id');
            $table->index('payout_id', 'payout_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');

            //control_number
            $table->string('control_number');

            //amount
            $table->double('amount', 15, 4);

            //reason
            $table->string('reason');

            //cashier_id
            $table->unsignedBigInteger('cashier_id');
            $table->index('cashier_id', 'cashier_id');

            //cashier_name
            $table->string('cashier_name');

            //authorize_id
            $table->unsignedBigInteger('authorize_id');
            $table->index('authorize_id', 'authorize_id');
            
            //authorize_name
            $table->string('authorize_name');

            //is_sent_to_server
            $table->boolean('is_sent_to_server')->default(false);

            //is_cut_off
            $table->boolean('is_cut_off')->default(false);

            //cut_off_id
            $table->unsignedBigInteger('cut_off_id')->nullable();

            //cut_off_at
            $table->dateTime('cut_off_at')->nullable();

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
        Schema::dropIfExists('payouts');
    }
};
