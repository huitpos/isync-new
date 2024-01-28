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

        Schema::connection('transactional_db')->create('safekeepings', function (Blueprint $table) use ($mainDb, $transactionalDb) {
            $table->id();
            $table->unsignedBigInteger('safekeeping_id');
            $table->index('safekeeping_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on($mainDb . '.pos_machines');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on($mainDb . '.branches');
            $table->double('amount', 15, 4);
            $table->unsignedBigInteger('cashier_id');
            $table->foreign('cashier_id')->references('id')->on($mainDb . '.users');
            $table->string('cashier_name')->nullable();
            $table->unsignedBigInteger('authorize_id');
            $table->foreign('authorize_id')->references('id')->on($mainDb . '.users');
            $table->string('authorize_name')->nullable();
            $table->boolean('is_cut_off')->default(false);
            $table->unsignedBigInteger('cut_off_id')->nullable();
            $table->dateTime('cut_off_at')->nullable();
            $table->boolean('is_sent_to_server')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transactional_db')->dropIfExists('safekeepings');
    }
};
