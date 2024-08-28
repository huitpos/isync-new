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
        Schema::create('official_receipt_informations', function (Blueprint $table) {
            $table->id();
            //official_receipt_information_id
            $table->unsignedBigInteger('official_receipt_information_id');
            $table->index('official_receipt_information_id', 'official_receipt_information_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');

            //transaction_id
            $table->unsignedBigInteger('transaction_id');
            $table->index('transaction_id', 'transaction_id');

            //name
            $table->string('name');

            //address
            $table->string('address');

            //tin
            $table->string('tin');

            //business_style
            $table->string('business_style');

            //is_void
            $table->boolean('is_void')->default(false);

            //void_by
            $table->unsignedBigInteger('void_by')->nullable();

            //void_name
            $table->string('void_name')->nullable();

            //void_at
            $table->dateTime('void_at')->nullable();

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
        Schema::dropIfExists('official_receipt_informations');
    }
};
