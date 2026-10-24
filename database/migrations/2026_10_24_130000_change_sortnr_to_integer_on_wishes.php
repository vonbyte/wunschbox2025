<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * sortnr was a string column, so '10' sorted before '5'.
     */
    public function up(): void
    {
        // Normalize existing values first; anything without digits becomes 0
        DB::table('wishes')->select('id', 'sortnr')->eachById(function ($wish) {
            $number = preg_match('/\d+/', (string) $wish->sortnr, $match) ? (int) $match[0] : 0;

            DB::table('wishes')->where('id', $wish->id)->update(['sortnr' => (string) $number]);
        });

        Schema::table('wishes', function (Blueprint $table) {
            $table->unsignedInteger('sortnr')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishes', function (Blueprint $table) {
            $table->string('sortnr')->default('000')->change();
        });

        DB::table('wishes')->select('id', 'sortnr')->eachById(function ($wish) {
            DB::table('wishes')
                ->where('id', $wish->id)
                ->update(['sortnr' => str_pad((string) $wish->sortnr, 3, '0', STR_PAD_LEFT)]);
        });
    }
};
