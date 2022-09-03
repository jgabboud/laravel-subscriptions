<?php

namespace Jgabboud\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jgabboud\Subscriptions\Models\PlanSubscriptionItem;

class PlanSubscription extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = [
        'plan_name',
        'plan_description',
    ];
    protected $fillable = [
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'plan_slug',
        'plan_name',
        'plan_description',
        'trial_starts_at',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancels_at',
        'canceled_at',
    ];

// == CONSTRUCTOR

    //-- boot
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($subscription) {
            $subscription->subscriptionItems()->delete();
        });
    }

//

// == RELATIONS

    //-- plan 
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    //-- subscriber
    public function subscriber()
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    //-- susbcription items
    public function subscriptionItems()
    {
        return $this->hasMany(PlanSubscriptionItem::class);
    }

//

// == FUNCTIONS
   

    //-- check if subscription is active
    public function isActive(): bool
    {
        return ! $this->ended() || $this->onTrial();
    }

    //-- check if subscription is on trial
    public function isTrial(): bool
    {
        return $this->trial_ends_at ? Carbon::now()->lt($this->trial_ends_at) : false;
    }

    //-- cancel current subscription
    public function cancel()
    {
        $this->canceled_at = Carbon::now();
        $this->save();
        return $this;
    }

    //-- check if subscription is canceled
    public function isCanceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    //-- end subscription
    public function end()
    {
        $this->canceled_at = Carbon::now();
        $this->ends_at = $this->canceled_at;
        $this->save();
        return $this;
    }

    //-- check if subscription is ended
    public function isEnded(): bool
    {
        return $this->ends_at ? Carbon::now()->gte($this->ends_at) : false;
    }

//

}
