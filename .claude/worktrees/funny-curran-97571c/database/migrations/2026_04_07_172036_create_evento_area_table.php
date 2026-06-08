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
        Schema::create('evento_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->unique(['evento_id', 'area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_area');
    }
};
