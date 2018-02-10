<?php

namespace App;

use App\Filters\ThreadFilter;

class Thread extends Model
{
    use RecordsActivity;

    protected $with = ['creator', 'channel'];
    protected $appends = ['isSubscribedTo'];

    protected static function boot()
    {
        parent::boot();

//        static::addGlobalScope('replyCount', function ($builder) {
//            $builder->withCount('replies');
//        });

        // Sima $thread->replies()->delete() nem működne az activity
        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });
    }

    protected static function getRecordEvents()
    {
        return ['created', 'deleted'];
    }


    public function path($uri = ''): string
    {
        $url = "/threads/{$this->channel->slug}/{$this->id}";
        return $uri ? "{$url}/{$uri}" : $url;
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function addReply(array $reply): Reply
    {
        return $this->replies()->create($reply);
    }

    public function scopeFilter($query, ThreadFilter $filters)
    {
        return $filters->apply($query);
    }

    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id'   => $userId ?? auth()->id()
        ]);
    }

    public function unsubscribe($userId = null)
    {
        $this->subscriptions()
            ->where('user_id', $userId ?? auth()->id())
            ->delete([
                'user_id'   => $userId ?? auth()->id()
            ]);
    }

    public function getIsSubscribedToAttribute(): bool
    {
        return $this->subscriptions()
            ->where('user_id', auth()->id())
            ->exists();
    }
}
