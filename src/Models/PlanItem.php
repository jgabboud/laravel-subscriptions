<?php

namespace Jgabboud\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Jgabboud\Subscriptions\Models\Plan;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanItem extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $guarded = [];
    protected $table = 'plan_items';
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
            'plan_id' => 'required|integer|exists:plans,id',
            'slug' => 'required|alpha_dash|max:150|unique:plans,slug',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'value' => 'required|string',
            'currency' => 'required|alpha|size:3',
            'resettable_period' => 'nullable|integer',
            'resettable_interval' => 'nullable|in:hour,day,week,month',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);
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

}
