<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->index('esta_activo');
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->index(['user_id', 'estado']);
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->index('numero_dia');
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropIndex(['numero_dia']);
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'estado']);
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex(['esta_activo']);
        });
    }
};
