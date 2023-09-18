<?php

namespace App\Listeners;

use App\Http\Controllers\AchievementsController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class WatchedLessonsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $count_achievement = DB::table('lesson_user')->where('watched', true)->where('user_id', $event->user->id)->count();
        $achievementController = new AchievementsController();
        $achievementController->store($count_achievement, $event->user->id, "Lesson");
    }
}
