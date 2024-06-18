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
        Schema::create('payment_other_informations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_other_information_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('name');
            $table->decimal('value', 15, 4);
            $table->boolean('is_cut_off')->default(false);
            $table->unsignedBigInteger('cut_off_id')->nullable();
            $table->boolean('is_void')->default(false);
            $table->boolean('is_sent_to_server')->default(false);
            $table->timestamp('treg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_other_informations');
    }
};
