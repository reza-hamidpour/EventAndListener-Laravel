<?php

namespace App\Listeners;

use App\Http\Controllers\AchievementsController;
use App\Models\Comment;
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
    public function handle(object $event): void
    {
        $count_comments = Comment::where('user_id', $event->comment->user_id)->count();
        $achievements = new AchievementsController();
        $achievements->store($count_comments, $event->comment->user_id, "Comment");

    }
}
