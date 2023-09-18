<?php

namespace App\Http\Controllers;

use App\Models\UnlockedAchievements;
use App\Models\User;
use Illuminate\Http\Request;

class AchievementsController extends Controller
{

    const LESSON_ACHIEVEMENTS = [
        ["First Lesson Watched", 1],
        ["5 Lessons Watched", 5],
        ["10 Lessons Watched", 10],
        ["25 Lessons Watched", 25],
        ["50 Lessons Watched", 50],
    ];

    const COMMENT_ACHIEVEMENTS = [
        ["First Comment Achievement", 1],
        ["3 Comment Achievement", 3],
        ["5 Comment Achievement", 5],
        ["10 Comment Achievement", 10],
        ["20 Comment Achievement", 20],
    ];


    public function index(User $user)
    {
        return response()->json([
            'unlocked_achievements' => [],
            'next_available_achievements' => [],
            'current_badge' => '',
            'next_badge' => '',
            'remaing_to_unlock_next_badge' => 0
        ]);
    }

    public function store($achievements, User $user, $path)
    {
        $current_achievement = $this->check_achievement($achievements, $user->id, $path);

        if( $current_achievement['status'] === true ) {
            $new_achievement = new UnlockedAchievements();
            $new_achievement->user_id = $user->id;
            $new_achievement->achievemenet_index = $current_achievement['index'];
            $new_achievement->achievement_type = $path;

            $new_achievement->save();

        }

    }

    protected function check_achievement($achieves, $user_id, $path)
    {

        $response = [
            "status" => false,
            "index" => 0,
        ];

        if ($path == "Comment") {
            $achievements = self::COMMENT_ACHIEVEMENTS;
        } else if ($path == "Lesson") {
            $achievements = self::LESSON_ACHIEVEMENTS;
        }

        $achievement_index = $this->getAchievementIndex($achievements, $achieves);
        $last_achievement = $this->getLastAchievement($user_id, $path);


        if( $last_achievement < $achievement_index )
            return [
                "status" => true,
                "index" => $achievement_index
            ];

        if( $achievement_index === -1 && $last_achievement !== -1 )
            return [
                "status" => false,
                "index" => $last_achievement,
            ];

        return [
            "status" => false,
            "index" => $last_achievement,
        ];

    }

    protected function getAchievementIndex($achievements, $count_achieved)
    {

        $achievements_count = count($achievements);
        foreach ($achievements as $index => $achievement) {
            if ($count_achieved >= $achievement[1] &&
                $index !== ($achievements_count - 1) &&
                $count_achieved < $achievements[$index + 1][1]) {

                return $index;
            }
        }
        return -1;

    }

    protected function getLastAchievement($user_id, $path)
    {
        $last_ach = UnlockedAchievements::where('user_id', $user_id)->where('achievement_type', $path);

        if ($last_ach->isNotEmpty()) {
            $last_ach = $last_ach->latest();
            if ($path == "Lesson")
                return self::LESSON_ACHIEVEMENTS[$last_ach->achievement_index];
            else if( $path === "Comment")
                return self::COMMENT_ACHIEVEMENTS[$last_ach->achievement_index];
        } else {
            return -1;
        }
    }

}
