<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            // Ajouté après coup pour correspondre au diagramme de classes
            // (attribut "typeCours" prévu en énumération mais oublié à la migration initiale).
            $table->enum('typeCours', ['CM', 'TD', 'TP'])
                ->default('CM')
                ->after('idSalle');
        });
    }

    public function down(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            $table->dropColumn('typeCours');
        });
    }
};