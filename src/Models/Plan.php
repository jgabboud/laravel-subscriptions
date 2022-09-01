<?php

namespace Jgabboud\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;
use Jgabboud\Subscriptions\Models\PlanItem;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jgabboud\Subscriptions\Models\PlanSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;    
    use SortableTrait;
    use HasTranslations;

    protected $guarded = [];
    protected $table = 'plans';
    public $translatable = [
        'name',
        'description',
    ];
    public $sortable = [
        'order_column_name' => 'sort_order',
    ];
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

// == CONSTRUCT and DECLARATIONS

    //-- constructor
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //-- define default deleting action
    public static function boot() {
        parent::boot();

        static::deleting(function($plan) { 
                $plan->items()->delete();
        });
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
