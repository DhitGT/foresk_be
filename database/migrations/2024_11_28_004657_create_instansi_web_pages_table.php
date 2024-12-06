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
        Schema::create('instansi_web_pages', function (Blueprint $table) {
            $table->id();
            $table->string("description");
            $table->string("instansi_id");
            $table->string("img_profile");
            $table->string("badge");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instansi_web_pages');
    }
};
