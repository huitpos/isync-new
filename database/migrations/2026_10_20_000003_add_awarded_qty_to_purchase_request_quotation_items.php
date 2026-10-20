<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_quotation_items', function (Blueprint $table) {
            $table->decimal('awarded_qty', 15, 4)->nullable()->after('available_qty');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_request_quotation_items', function (Blueprint $table) {
            $table->dropColumn('awarded_qty');
        });
    }
};
