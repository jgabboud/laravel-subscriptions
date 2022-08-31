<?php

namespace Jgabboud\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Jgabboud\Subscriptions\Models\PlanItem;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jgabboud\Subscriptions\Models\PlanSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $guarded = [];
    protected $table = 'plans';
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'active_subscribers_limit',
        'issue_date',
        'sort_order',
        'is_active'
    ];

// == CONSTRUCT 

    public function __construct(array $attributes = array())
    {
        $this->validate();
        parent::__construct($attributes);
    }

//

// == Validation

    //-- data validation
    public function validate(){
        $this->mergeRules([
            'slug' => 'required|alpha_dash|max:150|unique:plans,slug',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'currency' => 'required|alpha|size:3',
            'trial_period' => 'nullable|integer',
            'trial_interval' => 'nullable|in:hour,day,week,month',
            'invoice_period' => 'nullable|integer',
            'invoice_interval' => 'nullable|in:hour,day,week,month',
            'grace_period' => 'nullable|integer',
            'grace_interval' => 'nullable|in:hour,day,week,month',
            'active_subscribers_limit' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);
    }

//

// == RELATIONS

    //-- items
    public function items()
    {
        return $this->belongsToMany(PlanItem::class, 'plan_has_items');
    }

    //-- subscriptions
    public function subscriptions()
    {
        return $this->hasMany(PlanSubscription::class);
    }

//

// == QUERIES

    //-- check if plan is free
    public function isFree(): bool
    {
        return (float) $this->price <= 0.00;
    }

    //-- check if plan has trial
    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    //-- check if plan has grace
    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    //-- get plan item by slug
    public function getItemBySlug(string $itemSlug)
    {
        return $this->items()->where('slug', $itemSlug)->first();
    }

    //-- activate plan
    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    //-- deactivate plan
    public function deactivate()
    {
       return $this->update(['is_active' => false]);
    }
//

}
