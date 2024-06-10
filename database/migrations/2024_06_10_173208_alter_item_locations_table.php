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
        Schema::table('item_locations', function (Blueprint $table) {
            //drop unit_floor_number, street, region_id, province_id, city_idm, barangay_id
            if (Schema::hasColumn('item_locations', 'unit_floor_number')) {
                $table->dropColumn('unit_floor_number');
            }
            
            if (Schema::hasColumn('item_locations', 'street')) {
                $table->dropColumn('street');
            }
            $table->dropForeign('item_locations_region_id_foreign');
            $table->dropColumn('region_id');

            $table->dropForeign('item_locations_province_id_foreign');
            $table->dropColumn('province_id');

            $table->dropForeign('item_locations_city_id_foreign');
            $table->dropColumn('city_id');

            $table->dropForeign('item_locations_barangay_id_foreign');
            $table->dropColumn('barangay_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_locations', function (Blueprint $table) {
            //
        });
    }
};
