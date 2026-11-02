<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Turn the oldest team into the shared team and add every user to it.
     * Other teams are left untouched, they are just no longer used.
     */
    public function up(): void
    {
        $team = DB::table('teams')->orderBy('id')->first();

        if ($team === null) {
            // Fresh install: the first registered user creates the shared team
            return;
        }

        DB::table('teams')->where('id', $team->id)->update(['personal_team' => false]);

        $memberIds = DB::table('team_user')->where('team_id', $team->id)->pluck('user_id');

        DB::table('users')
            ->where('id', '!=', $team->user_id)
            ->whereNotIn('id', $memberIds)
            ->pluck('id')
            ->each(fn (int $userId) => DB::table('team_user')->insert([
                'team_id' => $team->id,
                'user_id' => $userId,
                'role' => 'editor',
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        DB::table('users')->update(['current_team_id' => $team->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Memberships can't be told apart from ones added later, so this is not reversed
    }
};
