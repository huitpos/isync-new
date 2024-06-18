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
        Schema::create('end_of_day_departments', function (Blueprint $table) {
            $table->id(); // Primary key column
            $table->unsignedBigInteger('end_of_day_department_id');
            $table->string('pos_machine_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('end_of_day_id');
            $table->unsignedBigInteger('discount_type_id');
            $table->string('name');
            $table->integer('transaction_count');
            $table->double('amount', 15, 4);
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
        Schema::dropIfExists('end_of_day_departments');
    }
};
