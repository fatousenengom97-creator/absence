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
    Schema::table('rapports_cours', function (Blueprint $table) {
        $table->foreignId('idCours')->after('id')->constrained('cours', 'idCours')->onDelete('cascade');
        $table->boolean('lu')->default(false)->after('idCours');
    });
}

public function down(): void
{
    Schema::table('rapports_cours', function (Blueprint $table) {
        $table->dropForeign(['idCours']);
        $table->dropColumn(['idCours', 'lu']);
    });
}
};
