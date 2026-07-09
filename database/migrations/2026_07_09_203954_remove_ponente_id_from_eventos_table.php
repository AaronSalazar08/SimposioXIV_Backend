<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['ponente_id']);
            $table->dropColumn('ponente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('ponente_id')->nullable()->after('horario_id')->constrained('ponentes')->cascadeOnDelete();
        });

        DB::table('evento_ponente')
            ->orderBy('id')
            ->get(['evento_id', 'ponente_id'])
            ->unique('evento_id')
            ->each(function ($pivot): void {
                DB::table('eventos')->where('id', $pivot->evento_id)->update(['ponente_id' => $pivot->ponente_id]);
            });
    }
};
