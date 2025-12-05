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
        Schema::table('wishes', function (Blueprint $table) {
            $table->string('status')->default('idea')->after('is_pulic');
            $table->string('sortnr')->default('000')->after('status');
            $table->index(['is_public', 'status', 'sortnr']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishes', function (Blueprint $table) {
            $table->dropIndex(['is_public', 'status', 'sortnr']);
            $table->dropColumn('status');
            $table->dropColumn('sortnr');
        });
    }

};
