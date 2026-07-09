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
        Schema::create('evento_ponente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('ponente_id')->constrained('ponentes')->cascadeOnDelete();
            $table->unique(['evento_id', 'ponente_id']);
        });

        DB::table('eventos')
            ->whereNotNull('ponente_id')
            ->get(['id', 'ponente_id'])
            ->each(function ($evento): void {
                DB::table('evento_ponente')->insert([
                    'evento_id' => $evento->id,
                    'ponente_id' => $evento->ponente_id,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_ponente');
    }
};
