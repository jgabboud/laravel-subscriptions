<?php

namespace Jgabboud\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
            $subscription->usage()->delete();
        });
    }

//

// == RELATIONS

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    //-- subscriber
    public function subscriber()
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    public function usage()
    {
        return $this->hasMany(PlanSubscriptionUsage::class);
    }

//

// == QUERIES
   

    //-- check if subscription is active
    public function active(): bool
    {
        return ! $this->ended() || $this->onTrial();
    }

    //-- check if subscription is inactive
    public function inactive(): bool
    {
        return ! $this->active();
    }

    //-- check if subscription is on trial
    public function onTrial(): bool
    {
        return $this->trial_ends_at ? Carbon::now()->lt($this->trial_ends_at) : false;
    }

    //-- check if subscription is canceled
    public function canceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    //-- check if subscription is ended
    public function ended(): bool
    {
        return $this->ends_at ? Carbon::now()->gte($this->ends_at) : false;
    }

    //-- cancel current subscription
    public function cancel($immediately = false)
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->ends_at = $this->canceled_at;
        }

        $this->save();

        return $this;
    }

    //-- change plan
    public function changePlan(Plan $plan)
    {
        // If plans does not have the same billing frequency
        // (e.g., invoice_interval and invoice_period) we will update
        // the billing dates starting today, and sice we are basically creating
        // a new billing cycle, the usage data will be cleared.
        if ($this->plan->invoice_interval !== $plan->invoice_interval || $this->plan->invoice_period !== $plan->invoice_period) {
            $this->setNewPeriod($plan->invoice_interval, $plan->invoice_period);
            $this->usage()->delete();
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->getKey();
        $this->save();

        return $this;
    }

    //-- renew subscription ===TODO
    public function renew()
    {
        if ($this->ended() && $this->canceled()) {
            #throw new LogicException('Unable to renew canceled ended subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription) {
            // Clear usage data
            $subscription->usage()->delete();

            // Renew period
            $subscription->setNewPeriod();
            $subscription->canceled_at = null;
            $subscription->save();
        });

        return $this;
    }

    //-- set new period === TODO
    protected function setNewPeriod($invoice_interval = '', $invoice_period = '', $start = '')
    {
        if (empty($invoice_interval)) {
            $invoice_interval = $this->plan->invoice_interval;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
        }

        // $period = new Period($invoice_interval, $invoice_period, $start);

        // $this->starts_at = $period->getStartDate();
        // $this->ends_at = $period->getEndDate();

        return $this;
    }

    //-- record item usage
    public function recordItemUsage(string $featureSlug, int $uses = 1, bool $incremental = true): PlanSubscriptionItem
    {
        $feature = $this->plan->features()->where('slug', $featureSlug)->first();

        $usage = $this->usage()->firstOrNew([
            'subscription_id' => $this->getKey(),
            'feature_id' => $feature->getKey(),
        ]);

        if ($feature->resettable_period) {
            // Set expiration date when the usage record is new or doesn't have one.
            if (is_null($usage->valid_until)) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate($this->created_at);
            } elseif ($usage->expired()) {
                // If the usage record has been expired, let's assign
                // a new expiration date and reset the uses to zero.
                $usage->valid_until = $feature->getResetDate($usage->valid_until);
                $usage->used = 0;
            }
        }

        $usage->used = ($incremental ? $usage->used + $uses : $uses);

        $usage->save();

        return $usage;
    }

    //-- reduce item usage
    public function reduceFeatureUsage(string $featureSlug, int $uses = 1): ?PlanSubscriptionItem
    {
        $usage = $this->usage()->byFeatureSlug($featureSlug)->first();

        if (is_null($usage)) {
            return null;
        }

        $usage->used = max($usage->used - $uses, 0);

        $usage->save();

        return $usage;
    }

    //-- check if user can use a specific feature
    public function canUseFeature(string $featureSlug): bool
    {
        $featureValue = $this->getFeatureValue($featureSlug);
        $usage = $this->usage()->byFeatureSlug($featureSlug)->first();

        if ($featureValue === 'true') {
            return true;
        }

        // If the feature value is zero, let's return false since
        // there's no uses available. (useful to disable countable features)
        if (! $usage || $usage->expired() || is_null($featureValue) || $featureValue === '0' || $featureValue === 'false') {
            return false;
        }

        // Check for available uses
        return $this->getFeatureRemainings($featureSlug) > 0;
    }

    //-- get item usage
    public function getFeatureUsage(string $featureSlug): int
    {
        $usage = $this->usage()->byFeatureSlug($featureSlug)->first();

        return (! $usage || $usage->expired()) ? 0 : $usage->used;
    }

    //-- get remaining usage
    public function getFeatureRemainings(string $featureSlug): int
    {
        return $this->getFeatureValue($featureSlug) - $this->getFeatureUsage($featureSlug);
    }

    //-- get item value by slug
    public function getFeatureValue(string $featureSlug)
    {
        $feature = $this->plan->features()->where('slug', $featureSlug)->first();

        return $feature->value ?? null;
    }
//

}
