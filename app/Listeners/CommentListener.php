<?php

namespace App\Listeners;

use App\Http\Controllers\AchievementsController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CommentListener
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
    public function handle(object $event, Achievements $achievements): void
    {
        $count_comments = $event->comment->user()->user_id;
        $achievements->store($count_comments, $event->comment->user(), "Comment");
    }
}
