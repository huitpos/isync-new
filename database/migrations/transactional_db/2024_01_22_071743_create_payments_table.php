<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'transactional_db';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mainDb = env('DB_DATABASE', 'forge');
        $transactionalDb = env('DB_TRANSACTIONAL_DATABASE', 'forge');

        Schema::create('payments', function (Blueprint $table) use ($mainDb, $transactionalDb) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('transaction_id')->on($transactionalDb . '.transactions');
            $table->unsignedBigInteger('payment_type_id');
            $table->foreign('payment_type_id')->references('id')->on($mainDb . '.payment_types');
            $table->string('payment_type_name')->nullable();
            $table->double('amount', 15, 4);
            $table->string('other_informations')->nullable();
            $table->boolean('is_advance_payment')->default(false);
            $table->boolean('is_cut_off')->default(false);
            $table->unsignedBigInteger('cut_off_id')->nullable();
            $table->dateTime('cut_off_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
