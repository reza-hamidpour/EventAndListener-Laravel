<?php

namespace Tests\Feature;

use App\Events\LessonWatched;
use App\Listeners\WatchedLessonsListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
