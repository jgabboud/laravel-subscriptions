<?php

namespace Jgabboud\Subscriptions\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class PlanSubscriptionUsage extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;
    public $translatable = [
        'item_name',
        'item_description',
    ];
    protected $fillable = [
        'subscription_id',
        'item_id',
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

// == QUERIES

    //-- check if subscription item has expired
    public function isExpired(): bool
    {
        if (is_null($this->valid_until)) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
//

}
