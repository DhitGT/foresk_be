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
            $table->id();
            $table->string('instansi_id');
            $table->string('eskul_id');
            $table->string('navbar_title');
            $table->string('jumbotron_title');
            $table->string('jumbotron_subtitle');
            $table->string('form_register_link');
            $table->text('about_desc');
            $table->text('activities_desc');
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
