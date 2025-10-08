<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['delivery_location_id']);
            $table->dropColumn('delivery_location_id');
        });
    }

    public function down() {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_location_id')->nullable();
            $table->foreign('delivery_location_id')->references('id')->on('delivery_locations');
        });
    }
};
