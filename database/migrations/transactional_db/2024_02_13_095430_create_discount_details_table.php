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
        $mainDb = env('DB_DATABASE', 'forge');
        $transactionalDb = env('DB_TRANSACTIONAL_DATABASE', 'forge');

        Schema::create('discount_details', function (Blueprint $table) use($mainDb, $transactionalDb) {
            $table->id();
            $table->unsignedBigInteger('discount_details_id');
            $table->index('discount_details_id');
            $table->unsignedBigInteger('discount_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->unsignedBigInteger('custom_discount_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('discount_type_id');
            $table->double('value', 15, 4);
            $table->double('discount_amount', 15, 4);
            $table->double('vat_exempt_amount', 15, 4);
            $table->string('type')->nullable();
            $table->boolean('is_void')->default(false);
            $table->unsignedBigInteger('void_by_id')->nullable();
            $table->string('void_by')->nullable();
            $table->dateTime('void_at')->nullable();
            $table->boolean('is_sent_to_server')->default(false);
            $table->boolean('is_cut_off')->default(false);
            $table->unsignedBigInteger('cut_off_id')->nullable();
            $table->boolean('is_vat_exempt')->default(false);
            $table->string('shift_number')->nullable();
            $table->string('treg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_details');
    }
};
