<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\UnlockedAchievements;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    const BADGES = [
        ["Beginner", 0],
        ["Intermediate", 4],
        ["Advanced", 8],
        ["Master", 10]
    ];


    public function index(User $user)
    {
        $achievements = $this->unlockedAchievement($user->id);
        $next_available = $this->nextAvailableAchievements($user->id);
        $badge_content = $this->getBadgesContents($user->id);

        return response()->json([
            'unlocked_achievements' => [
                "Lesson" => $achievements['lesson'],
                "Comment" => $achievements['comment'],
            ],
            'next_available_achievements' => [
                "Lesson" => $next_available['lesson'],
                "Comment" => $next_available['comment'],
            ],
            'current_badge' => $badge_content['current'],
            'next_badge' => $badge_content['next'],
            'remaining_to_unlock_next_badge' => $badge_content['remain'],
        ]);

    }

    public function store($achievements, $user_id, $path)
    {
        $current_achievement = $this->check_achievement($achievements, $user_id, $path);
        if ($current_achievement['status'] === true) {
            $new_achievement = new UnlockedAchievements();
            $new_achievement->user_id = $user_id;
            $new_achievement->achievement_index = $current_achievement['index'];
            $new_achievement->achievement_type = $path;
            $new_achievement->save();

        }

    }

    protected function unlockedAchievement($user_id)
    {
        $response = [
            "lesson" => null,
            "comment" => null,
        ];
        $achievements_comment = UnlockedAchievements::where('user_id', $user_id)->where('achievement_type', "Comment")->latest()->get();
        $achievements_lesson = UnlockedAchievements::where('user_id', $user_id)->where('achievement_type', "Lesson")->latest()->get();

        if ( $achievements_comment->isNotEmpty()) {
            $response["comment"] = self::COMMENT_ACHIEVEMENTS[$achievements_comment->first()->achievement_index][0];
        }

        if ( $achievements_lesson->isNotEmpty() ) {
            $response["lesson"] = self::LESSON_ACHIEVEMENTS[$achievements_lesson->first()->achievement_index][0];
        }
        return $response;
    }

    protected function nextAvailableAchievements($user_id)
    {
        $response = [
            "lesson" => null,
            "comment" => null,
        ];
        $achievements_comment = UnlockedAchievements::where('user_id', $user_id)->where('achievement_type', "Comment")->latest()->get();
        $achievements_lesson = UnlockedAchievements::where('user_id', $user_id)->where('achievement_type', "Lesson")->latest()->get();

        if ($achievements_comment->isNotEmpty()) {
            if ($achievements_comment->first()->achievement_index + 1 <= count(self::COMMENT_ACHIEVEMENTS) - 1) {
                $response["comment"] =
                    self::COMMENT_ACHIEVEMENTS[$achievements_comment->first()->achievement_index + 1][0];
            }
        } else {
            $response['comment'] = self::COMMENT_ACHIEVEMENTS[0][0];
        }

        if ($achievements_lesson->isNotEmpty()) {
            if ($achievements_lesson->first()->achievement_index + 1 <= count(self::LESSON_ACHIEVEMENTS) - 1) {
                $response["lesson"] =
                    self::LESSON_ACHIEVEMENTS[$achievements_lesson->first()->achievement_index + 1][0];
            }
        } else {
            $response['lesson'] = self::LESSON_ACHIEVEMENTS[0][0];
        }

        return $response;

    }

    protected function getBadgesContents($user_id)
    {
        $count_achievements = $this->getAchievementsCount($user_id);
//        dd($count_achievements);
        if ($count_achievements > 3) { // the Intermediate level would start at 4

            $index_badges = $this->getAchievementIndex(self::BADGES, $count_achievements);
            $next_badge = self::BADGES[$index_badges][0];
            $remain = 0;
            if (isset(self::BADGES[$index_badges + 1])) {
                $next_badge = self::BADGES[$index_badges + 1][0];
                $remain = self::BADGES[$index_badges + 1][1] - $count_achievements;
            }

            return [
                "current" => self::BADGES[$index_badges][0],
                "next" => $next_badge,
                "remain" => $remain,
            ];
        }

        return ["current" => self::BADGES[0][0],
            "next" => self::BADGES[1][0],
            "remain" => self::BADGES[1][1] - $count_achievements];
    }

    public function getAchievementsCount($user_id)
    {
        $all_achieves = UnlockedAchievements::where("user_id", $user_id)->latest()->get();

        $total_achieves = 0;
        foreach ($all_achieves as $achieve) {
            if ($achieve->achievement_type == "Comment") {
                $total_achieves += self::COMMENT_ACHIEVEMENTS[$achieve->achievement_index][1];
            } else if ($achieve->achievement_type == "Lesson") {
                $total_achieves += self::LESSON_ACHIEVEMENTS[$achieve->achievement_index][1];
            }
        }
        return $total_achieves;
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
        $current_achievement = $this->getLatestAchievement($user_id, $path);

        if ($current_achievement < $achievement_index)
            return [
                "status" => true,
                "index" => $achievement_index
            ];

        if ($achievement_index === -1 && $current_achievement !== -1)
            return [
                "status" => false,
                "index" => $current_achievement,
            ];

        return [
            "status" => false,
            "index" => $current_achievement,
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
        return $achievements_count - 1;

    }

    protected function getLatestAchievement($user_id, $path)
    {
        $first_ach = UnlockedAchievements::where('user_id', $user_id)->where('achievement_type', $path)->get();
        if ($first_ach->isNotEmpty()) {
            if ($path == "Lesson")
                return $first_ach->last()->achievement_index;
            else if ($path === "Comment")
                return $first_ach->last()->achievement_index;
        } else {
            return -1;
        }
    }

}
