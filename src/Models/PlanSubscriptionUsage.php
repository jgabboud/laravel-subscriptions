<?php

namespace Jgabboud\Subscriptions\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanSubscriptionUsage extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    //-- scope by feature slug
    // public function scopeByFeatureSlug(Builder $builder, string $featureSlug): Builder
    // {
    //     $feature = app('plan_items')->where('slug', $featureSlug)->first();

    //     return $builder->where('feature_id', $feature ? $feature->getKey() : null);
    // }

    //-- check if expired
    public function expired(): bool
    {
        if (is_null($this->valid_until)) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
//

}
