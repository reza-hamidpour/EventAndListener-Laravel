<?php

namespace Tests\Feature;

use App\Events\CommentWritten;
use App\Listeners\CommentListener;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommentListenerTest extends TestCase
{
    use WithFaker;


    /**
     * Testing CommentWritten Event and its Listener are connected to each other or not.
     */
    public function test_listener_connected_to_event(): void
    {
        $faker = Event::fake( CommentWritten::class);

        $faker->assertListening(
            CommentWritten::class,
            CommentListener::class
        );
    }

    /**
     * Testing Generating 2 Comment and then faking CommentWritten event and getting the response.
     * @return void
     */
    public function test_check_unlocker_logic(){

        $comment = $this->generating_data(2);

        event( new CommentWritten($comment));

        $response = $this->get('/users/' . $comment->user_id . '/achievements/');
        $response->assertStatus( 200);
        $response->dd();


    }

    protected function generating_data( $num_comment){
        $this->setUpFaker();
        $user = User::factory()->create();

        while( $num_comment >= 0){
           Comment::create([
               "body" => $this->faker()->text,
               "user_id" => $user->id,
           ]);
           $num_comment--;
        }
        return Comment::first();
    }
}
