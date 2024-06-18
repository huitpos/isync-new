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
        Schema::create('cut_off_departments', function (Blueprint $table) {
            $table->id();
            $table->string('cut_off_department_id');
            $table->string('pos_machine_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('cut_off_id');
            $table->unsignedBigInteger('department_id');
            $table->string('name');
            $table->integer('transaction_count');
            $table->double('amount', 15, 4);
            $table->unsignedBigInteger('end_of_day_id');
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
        Schema::dropIfExists('cut_off_departments');
    }
};
