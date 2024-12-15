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
        Schema::create('eskuls', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("instansi_id");
            $table->string("name");
            $table->string("leader_id");
            $table->string("logo");
            $table->string("badge");
            $table->string("gen");
            $table->integer("alumni");
            $table->string("instagram_url");
            $table->string("whatsapp_number");
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eskuls');
    }
};
