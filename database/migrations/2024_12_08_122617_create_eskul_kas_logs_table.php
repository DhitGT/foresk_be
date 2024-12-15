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
        Schema::create('eskul_kas_logs', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('eskul_kas_id');
            $table->string('eskul_id');
            $table->string('instansi_id');
            $table->integer(column: 'amount');
            $table->string('flag');
            $table->text('description');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eskul_kas_logs');
    }
};
