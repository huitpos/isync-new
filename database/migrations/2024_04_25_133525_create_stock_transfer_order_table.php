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
        Schema::create('stock_transfer_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_branch_id');
            $table->foreign('source_branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('destination_branch_id');
            $table->foreign('destination_branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            //delivery_location_id
            $table->unsignedBigInteger('delivery_location_id');
            $table->foreign('delivery_location_id')->references('id')->on('delivery_locations');
            $table->unsignedBigInteger('stock_transfer_request_id');
            $table->foreign('stock_transfer_request_id')->references('id')->on('stock_transfer_requests');

            $table->string('sto_number');
            $table->text('str_remarks')->nullable();

            $table->enum('status', ['pending', 'for_review', 'approved', 'rejected'])->default('pending');

            //created_by
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            //updated_by
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');
            //action_by
            $table->unsignedBigInteger('action_by');
            $table->foreign('action_by')->references('id')->on('users');

            $table->boolean('is_closed')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_order');
    }
};
