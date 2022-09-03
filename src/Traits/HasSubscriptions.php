<?php

namespace Jgabboud\Subscriptions\Traits;

use Carbon\Carbon;
use Jgabboud\Subscriptions\Models\Plan;
use Jgabboud\Subscriptions\Models\PlanSubscription;
use stdClass;

trait HasSubscriptions
{
    

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
        #morphMany: $related, $name, $type = null, $id = null, $localKey = null
        return $this->morphMany(PlanSubscription::class, 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    //-- get date intervals
    private function intervalDates($interval, $period, $start){
        $date = new stdClass();
        $date->start_date = $start;
        switch($period){
            case "days":
                $date->end_date = $start->addDays($interval);
                break;
            case "month":
                $date->end_date = $start->addMonths($interval);
                break;
            case "year":
                $date->end_date = $start->addYears($interval);
                break;
            default:
                $date->end_date = $start->addMonths($interval);
                break;
        }
        return $date;
    }
//

// == GET

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

// 

// == EDIT

    //-- subscribe to a new plan
    public function subscribe(Plan $plan): PlanSubscription
    {
    
        $trial = $this->intervalDates($plan->trial_interval, $plan->trial_period, Carbon::now());
        $issue = $this->intervalDates($plan->trial_interval, $plan->trial_period, $trial->end_date);            

        return $this->planSubscriptions()->create([
            'plan_id' => $plan->id,
            'plan_slug' => $plan->slug,
            'plan_name' => $plan->name,
            'plan_description' => $plan->description,
            'trial_starts_at' => $trial->start_date,
            'trial_ends_at' => $trial->end_date,
            'starts_at' => $issue->start_date,
            'ends_at' => $issue->end_date
        ]);
    }
//


}