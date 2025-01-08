<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_eskul_absensis', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('eskul_id');
            $table->string('eskul_report_activity_id');
            $table->string('absent_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_eskul_absensis');
    }
};
