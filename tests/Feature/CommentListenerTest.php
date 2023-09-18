<?php

namespace Tests\Feature;

use App\Events\CommentWritten;
use App\Listeners\CommentListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommentListenerTest extends TestCase
{

    /**
     * A basic feature test example.
     */
    public function test_listener_connected_to_event(): void
    {
        $faker = Event::fake( CommentWritten::class);

        $faker->assertListening(
            CommentWritten::class,
            CommentListener::class
        );
    }
}
