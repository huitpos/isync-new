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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            //department_id
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            //delivery_location_id
            $table->unsignedBigInteger('delivery_location_id');
            $table->foreign('delivery_location_id')->references('id')->on('delivery_locations');
            //supplier_id
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            //payment_term_id
            $table->unsignedBigInteger('payment_term_id');
            $table->foreign('payment_term_id')->references('id')->on('payment_terms');
            //supplier_term_id
            $table->unsignedBigInteger('supplier_term_id');
            $table->foreign('supplier_term_id')->references('id')->on('supplier_terms');
            //purchase_request_id
            $table->unsignedBigInteger('purchase_request_id');
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests');
            //po_number
            $table->string('po_number');
            //date_needed
            $table->date('date_needed');
            //pr_remarks
            $table->text('pr_remarks')->nullable();
            //total
            $table->double('total', 15, 4);
            //status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            //created_by
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            //updated_by
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');
            //action_by
            $table->unsignedBigInteger('action_by');
            $table->foreign('action_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
