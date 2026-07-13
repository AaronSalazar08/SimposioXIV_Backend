<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estas relaciones se creaban con cascadeOnDelete, lo que borraba en
     * silencio horarios/eventos al eliminar una entidad de la que dependían.
     * Ahora la app valida las dependencias antes de eliminar (ver
     * *Service::eliminar()), y la base de datos refuerza esa misma regla con
     * restrictOnDelete como última línea de defensa.
     *
     * inscripciones.evento_id / inscripciones.user_id se dejan en cascade a
     * propósito: una inscripción Cancelada es solo historial (no hay forma
     * de borrarla desde el admin), así que el check de la app solo bloquea
     * por inscripciones Confirmadas — si esas son cero, el evento/usuario
     * puede eliminarse y arrastrar consigo las filas Canceladas restantes.
     */
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
            $table->foreign('aula_id')->references('id')->on('aulas')->restrictOnDelete();
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['horario_id']);
            $table->foreign('horario_id')->references('id')->on('horarios')->restrictOnDelete();
        });

        Schema::table('evento_area', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->foreign('area_id')->references('id')->on('areas')->restrictOnDelete();
        });

        Schema::table('evento_ponente', function (Blueprint $table) {
            $table->dropForeign(['ponente_id']);
            $table->foreign('ponente_id')->references('id')->on('ponentes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
            $table->foreign('aula_id')->references('id')->on('aulas')->cascadeOnDelete();
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['horario_id']);
            $table->foreign('horario_id')->references('id')->on('horarios')->cascadeOnDelete();
        });

        Schema::table('evento_area', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->foreign('area_id')->references('id')->on('areas')->cascadeOnDelete();
        });

        Schema::table('evento_ponente', function (Blueprint $table) {
            $table->dropForeign(['ponente_id']);
            $table->foreign('ponente_id')->references('id')->on('ponentes')->cascadeOnDelete();
        });
    }
};
