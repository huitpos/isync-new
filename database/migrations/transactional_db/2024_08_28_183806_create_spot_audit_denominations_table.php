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
        Schema::create('spot_audit_denominations', function (Blueprint $table) {
            $table->id();
            //spot_audit_denomination_id
            $table->unsignedBigInteger('spot_audit_denomination_id');
            $table->index('spot_audit_denomination_id', 'spot_audit_denomination_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');

            //spot_audit_id
            $table->unsignedBigInteger('spot_audit_id');
            $table->index('spot_audit_id', 'spot_audit_id');

            //cash_denomination_id
            $table->unsignedBigInteger('cash_denomination_id');
            $table->index('cash_denomination_id', 'cash_denomination_id');

            //name
            $table->string('name');

            //amount
            $table->decimal('amount', 15, 2);

            //qty
            $table->integer('qty');

            //total
            $table->decimal('total', 15, 2);

            //is_cut_off
            $table->boolean('is_cut_off')->default(false);

            //cut_off_id
            $table->unsignedBigInteger('cut_off_id')->nullable();

            //is_sent_to_server
            $table->boolean('is_sent_to_server')->default(false);

            //shift_number
            $table->integer('shift_number');

            //treg
            $table->string('treg');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spot_audit_denominations');
    }
};
