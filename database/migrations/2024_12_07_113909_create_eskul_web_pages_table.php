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
        Schema::create('eskul_web_pages', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('instansi_id');
            $table->string('eskul_id');
            $table->string('custom_domain_name')->nullable();
            $table->string('navbar_title')->nullable();
            $table->string('jumbotron_image')->nullable();
            $table->string('jumbotron_title')->nullable();
            $table->string('jumbotron_subtitle')->nullable();
            $table->string('form_register_link')->nullable();
            $table->text('about_desc')->nullable();
            $table->text('activities_desc')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eskul_web_pages');
    }
};
