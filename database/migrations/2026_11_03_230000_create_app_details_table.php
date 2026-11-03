<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_details', function (Blueprint $table) {
            $table->id();
            $table->string('app')->unique();
            $table->unsignedInteger('version_code');
            $table->string('version_name');
            $table->string('apk_url');
            $table->string('sha256', 64);
            $table->boolean('mandatory')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('app_details')->insert([
            'app' => 'pos',
            'version_code' => 6,
            'version_name' => '1.3.0',
            'apk_url' => 'https://isync-bucket.s3.ap-southeast-1.amazonaws.com/pos/isyncpos-1.3.0.apk',
            'sha256' => 'C37A599144C0A111D6ED30FE0411635CEDEE669EF6CA306C882D15D016E98983',
            'mandatory' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_details');
    }
};
