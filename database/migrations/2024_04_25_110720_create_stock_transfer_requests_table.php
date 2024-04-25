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
        Schema::create('stock_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_branch_id');
            $table->foreign('source_branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('destination_branch_id');
            $table->foreign('destination_branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->unsignedBigInteger('delivery_location_id');
            $table->foreign('delivery_location_id')->references('id')->on('delivery_locations');
            $table->unsignedBigInteger('approved_by');
            $table->foreign('approved_by')->references('id')->on('users');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('str_number');
            $table->text('remarks')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_requests');
    }
};
