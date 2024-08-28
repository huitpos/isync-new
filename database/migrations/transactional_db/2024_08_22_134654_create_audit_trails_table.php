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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_trail_id');
            $table->index('audit_trail_id');
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id');
            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id');
            //user_id
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            //user_name nullable
            $table->string('user_name')->nullable();
            //transaction_id
            $table->unsignedBigInteger('transaction_id');
            $table->index('transaction_id');
            //action nullable
            $table->string('action')->nullable();
            //description nullable
            $table->string('description')->nullable();
            //authorize_id
            $table->unsignedBigInteger('authorize_id');
            $table->index('authorize_id');
            //authorize_name nullable
            $table->string('authorize_name')->nullable();
            //is_sent_to_server boolean false
            $table->boolean('is_sent_to_server')->default(false);
            //treg nullable
            $table->string('treg')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
