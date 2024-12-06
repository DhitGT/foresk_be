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
        Schema::create('eskul_report_activities', function (Blueprint $table) {
            $table->id();
            $table->string("eskul_id");
            $table->string("picture");
            $table->text("description");
            $table->date("date_start");
            $table->date("date_end");
            $table->string("absent-code");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eskul_report_activities');
    }
};
