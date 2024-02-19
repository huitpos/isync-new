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
        Schema::create('discount_type_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_type_id');
            $table->foreign('discount_type_id')->references('id')->on('discount_types');
            $table->string('name');
            $table->enum('field_type', ['textbox', 'select', 'radio', 'checkbox'])->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_type_fields');
    }
};
