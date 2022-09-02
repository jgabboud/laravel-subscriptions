<?php

namespace Jgabboud\Subscriptions\Models;

use Spatie\Sluggable\HasSlug;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Jgabboud\Subscriptions\Models\Plan;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanItem extends Model
{
    use HasSlug;
    use HasFactory;
    use SoftDeletes; 
    use SortableTrait;
    use HasTranslations;
    
    protected $guarded = [];
    protected $table = 'plan_items';
    public $translatable = [
        'name',
        'description',
    ];
    public $sortable = [
        'order_column_name' => 'sort_order',
    ];
    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order'
    ];
   
// == CONSTRUCT 

    //-- constructor
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //-- define default deleting action
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($plan_feature) {
            $plan_feature->usage()->delete();
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

    //-- plan
    public function plan()
    {
        return $this->belongsToMany(Plan::class, 'plan_has_items');
    }

    //-- subscription usage
    public function usage()
    {
        return $this->hasMany(PlanSubscriptionUsage::class);
    }

//


// == QUERIES

    //-- get reset date of a plan item
    public function getResetDate(Carbon $dateFrom)#: Carbon
    {
        #$period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? now());
        #return $period->getEndDate();
    }

}
