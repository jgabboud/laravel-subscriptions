<?php

namespace Jgabboud\Subscriptions\Traits;

use Jgabboud\Subscriptions\Models\PlanSubscription;

trait HasSubscriptions
{
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    //
    protected static function bootHasSubscriptions()
    {
        static::deleted(function ($plan) {
            $plan->planSubscriptions()->delete();
        });
    }

    //
    public function planSubscriptions()
    {
        return $this->morphMany('plan_subscriptions', 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    //
    public function activePlanSubscriptions()
    {
        return $this->planSubscriptions->reject->inactive();
    }

    //
    public function planSubscription(string $subscriptionSlug): ?PlanSubscription
    {
        return $this->planSubscriptions()->where('slug', $subscriptionSlug)->first();
    }

    //
    public function subscribedPlans()
    {
        // $planIds = $this->planSubscriptions->reject->inactive()->pluck('plan_id')->unique();

        // return app('plans')->whereIn('id', $planIds)->get();
    }

    //
    public function subscribedTo($planId): bool
    {
        $subscription = $this->planSubscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    //
    // public function newPlanSubscription($subscription, Plan $plan, Carbon $startDate = null): PlanSubscription
    // {
    //     $trial = new Period($plan->trial_interval, $plan->trial_period, $startDate ?? now());
    //     $period = new Period($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

    //     return $this->planSubscriptions()->create([
    //         'name' => $subscription,
    //         'plan_id' => $plan->getKey(),
    //         'trial_ends_at' => $trial->getEndDate(),
    //         'starts_at' => $period->getStartDate(),
    //         'ends_at' => $period->getEndDate(),
    //     ]);
    // }
}