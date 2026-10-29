<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Admin wishes used to be stored with the admin's user name as receiver (e.g. "Tanja B."),
     * but the public wish note now only shows wishes of the owner. Guest wishes are never public,
     * so public wishes outside the receiver list are admin wishes and move to the owner.
     */
    public function up(): void
    {
        $owner = config('wishbox.owner');

        DB::table('wishes')
            ->where('is_public', true)
            ->whereNotIn('receiver', ['Sonia', 'Tanja', $owner])
            ->update(['receiver' => $owner]);
    }

    /**
     * Reverse the migrations.
     *
     * The old receivers are not kept, so there is nothing to restore.
     */
    public function down(): void
    {
        //
    }
};
