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
        Schema::create('crash_reports', function (Blueprint $table) {
            $table->id();

            //device_id
            $table->unsignedBigInteger('device_id');
            $table->index('device_id', 'device_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');

            //machine_number
            $table->string('machine_number');

            //serial_number
            $table->string('serial_number');

            //version_code
            $table->unsignedInteger('version_code');

            //version_name
            $table->string('version_name');

            //device_model
            $table->string('device_model');

            //android_version
            $table->string('android_version');

            //occurred_at
            $table->dateTime('occurred_at');
            $table->index('occurred_at', 'occurred_at');

            //exception
            $table->longText('exception');

            //stack_trace
            $table->longText('stack_trace');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crash_reports');
    }
};
