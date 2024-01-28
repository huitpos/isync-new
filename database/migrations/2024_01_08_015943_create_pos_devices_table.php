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
        Schema::create('pos_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_machine_id');
            $table->foreign('pos_machine_id')->references('id')->on('pos_machines');
            $table->string('serial')->nullable();
            $table->string('model')->nullable();
            $table->string('android_id')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('board')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_devices');
    }
};
