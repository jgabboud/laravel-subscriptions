<?php

namespace Jgabboud\Subscriptions\Traits;

use Carbon\Carbon;
use Jgabboud\Subscriptions\Models\Plan;
use Jgabboud\Subscriptions\Models\PlanSubscription;

trait HasSubscriptions
{
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

// == DECLARATION

    //-- default delete
    protected static function bootHasSubscriptions()
    {
        static::deleted(function ($plan) {
            $plan->planSubscriptions()->delete();
        });
    }

    //-- RELATION 
    public function planSubscriptions()
    {
        return $this->morphMany(PlanSubscription::class, 'subscriber', 'subscriber_type', 'subscriber_id');
    }
//

// == QUERIES

    //-- get currently active subscriptions
    public function activePlanSubscriptions()
    {
        return $this->planSubscriptions->reject->inactive();
    }

    //-- get subscriptions by slug
    public function planSubscription(string $subscriptionSlug): ?PlanSubscription
    {
        return $this->planSubscriptions()->where('slug', $subscriptionSlug)->first();
    }

    //-- get plans that are currently subscribed to
    public function subscribedPlans()
    {
        $planIds = $this->planSubscriptions->reject->inactive()->pluck('plan_id')->unique();
        return Plan::whereIn('id', $planIds)->get();
    }

    //-- check if user is subscribed to this plan 
    public function subscribedTo($planId): bool
    {
        $subscription = $this->planSubscriptions()->where('plan_id', $planId)->first();
        return $subscription && $subscription->active();
    }

    //-- subscribe to a new plan
    public function subscribe($subscription, Plan $plan, Carbon $startDate = null)#: PlanSubscription
    {
        // $trial = new Period($plan->trial_interval, $plan->trial_period, $startDate ?? now());
        // $period = new Period($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

        // return $this->planSubscriptions()->create([
        //     'name' => $subscription,
        //     'plan_id' => $plan->getKey(),
        //     'trial_ends_at' => $trial->getEndDate(),
        //     'starts_at' => $period->getStartDate(),
        //     'ends_at' => $period->getEndDate(),
        // ]);
    }

//

}