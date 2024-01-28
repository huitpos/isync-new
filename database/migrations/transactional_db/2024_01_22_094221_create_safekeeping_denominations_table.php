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

        Schema::create('safekeeping_denominations', function (Blueprint $table) use ($mainDb, $transactionalDb) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->unsignedBigInteger('safekeeping_denomination_id');
            $table->unsignedBigInteger('safekeeping_id');
            $table->foreign('safekeeping_id')->references('safekeeping_id')->on($transactionalDb . '.safekeepings');
            $table->unsignedBigInteger('cash_denomination_id');
            $table->foreign('cash_denomination_id')->references('id')->on($mainDb . '.cash_denominations');
            $table->string('name')->nullable();
            $table->double('amount', 15, 4);
            $table->integer('qty');
            $table->double('total', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safekeeping_denominations');
    }
};
