<?php

namespace Jgabboud\Subscriptions\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;
use Jgabboud\Subscriptions\Models\PlanItem;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jgabboud\Subscriptions\Models\PlanSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model implements Sortable
{
    use HasSlug;
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
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'currency',
        'trial_duration',
        'trial_duration_type',
        'package_duration',
        'package_duration_type',
        'subscriptions_limit',
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

    //-- get slugs options required from slug package by spatie
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
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

// == FUNCTIONS

    //-- check if plan is free
    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    //-- check if plan is active
    public function isActive(): bool
    {
        return $this->price <= 0;
    }

    //-- check if plan has trial
    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    //-- assign items to plan
    public function assignItems($items)
    {
        return $this->items()->sync($items, false);
    }

    //-- remove items from plan
    public function revokeItems($items)
    {
        return $this->items()->detach($items);
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
