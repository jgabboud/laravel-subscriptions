<?php

namespace Jgabboud\Subscriptions\Models;

use Spatie\Sluggable\HasSlug;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\SlugOptions;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Jgabboud\Subscriptions\Models\Plan;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanItem extends Model implements Sortable
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
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'item_duration',
        'item_duration_type',
        'sort_order'
    ];
   
// == CONSTRUCT 

    //-- constructor
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
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
    public function subscriptionItems()
    {
        return $this->hasMany(PlanSubscriptionItem::class);
    }

//


}
