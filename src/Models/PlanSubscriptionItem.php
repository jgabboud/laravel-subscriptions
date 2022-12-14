<?php

namespace Jgabboud\Subscriptions\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class PlanSubscriptionItem extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;
    public $translatable = [
        'item_name',
        'item_description',
    ];
    protected $fillable = [
        'plan_subscription_id',
        'plan_item_id',
        'item_slug',
        'item_name',
        'item_description',
        'value',
        'used',
        'valid_until',
        'timezone',
    ];

// == RELATIONS

    //-- item
    public function item()
    {
        return $this->belongsTo(PlanItem::class);
    }

    //-- subscription
    public function subscription()
    {
        return $this->belongsTo(PlanSubscription::class);
    }

//

// == FUNCTIONS

    //-- check if subscription item has expired
    public function isExpired(): bool
    {
        return is_null($this->valid_until);
    }

//

}
