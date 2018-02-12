<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->signIn();
    }


    /** @test */
    public function a_notification_is_created_when_a_subscribed_thread_receives_a_new_reply()
    {
        $thread = create('Thread')->subscribe();

        $this->assertCount(0, auth()->user()->notifications);

        // And each time a new reply is left
        $thread->addReply([
            'user_id'   => create('User')->id,
            'body'      => 'Some new reply'
        ]);

        // Then a notification should be prepared for the user
        $this->assertCount(1, auth()->user()->fresh()->notifications);
    }

    /** @test */
    public function it_does_not_create_notification_for_auth_user_when_auth_user_replies()
    {
        $thread = create('Thread')->subscribe();

        $this->assertCount(0, auth()->user()->notifications);

        // And each time a new reply is left
        $thread->addReply([
            'user_id'   => auth()->id(),
            'body'      => 'Some new reply'
        ]);

        // Then a notification should be prepared for the user
        $this->assertCount(0, auth()->user()->fresh()->notifications);
    }

    /** @test */
    public function a_user_can_fetch_its_unread_notifications()
    {
        factory(DatabaseNotification::class)->create();

        $this->assertCount(
            1,
            $this->getJson("/profiles/" . auth()->user()->name . "/notifications")->json()
        );
    }

    /** @test */
    public function a_user_can_mark_a_notification_as_read()
    {
        factory(DatabaseNotification::class)->create();

        tap(auth()->user(), function ($user) {
            $this->assertCount(1, $user->unreadNotifications);

            $this->delete(
                "/profiles/{$user->name}/notifications/" . $user->unreadNotifications->first()->id);

            $this->assertCount(0, $user->fresh()->unreadNotifications);
        });
    }
}
