<?php

namespace Tests\Feature;

use App\Events\LessonWatched;
use App\Listeners\WatchedLessonsListener;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WatchedLessonsListenerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_listener_connected_to_event(): void
    {
        $fake_event = Event::fake();

        $fake_event->assertListening(
            LessonWatched::class,
            WatchedLessonsListener::class
        );
    }

    public function test_unlocker_logic(){

        $user_lesson = $this->generating_data(2);
        event( new LessonWatched($user_lesson[1], $user_lesson[0]));
        $response = $this->get('/users/' . $user_lesson[0]->id . '/achievements/');
        $response->assertStatus( 200);
        $response->dd();

    }


    protected function generating_data($num_watched){

        $user = User::all()->first();
        if( !$user )
        {
            User::factory()->create();
            $user = User::all()->first();
        }
        Lesson::factory()->count($num_watched)->create();
        $count_lessons = Lesson::all()->count();
        while($num_watched >= 0){

            DB::table('lesson_user')->insert([
                'user_id' => $user->id,
                'lesson_id' => Lesson::find(random_int(1, $count_lessons))->get()->last()->id,
                'watched' => true,
            ]);
            $num_watched--;

        }

        return [$user, Lesson::all()->first()];
    }
}
