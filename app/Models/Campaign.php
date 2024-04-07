<?php

namespace App\Models;

use App\Models\Link;
use App\Models\CampaignJob;
use App\Models\ContactList;
use App\Models\MessageList;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Campaign extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'number_id', 'contact_list', 'message_list', 'cron'];

    /**
    * Scope a query to only include records of certain number
    */
    public function scopeOfNumber(Builder $query, int $number_id): void
    {
        $query->where('number_id', $number_id);
    }


    public function contact_list(): BelongsTo
    {
        return $this->belongsTo(ContactList::class);
    }

    public function message_list(): BelongsTo
    {
        return $this->belongsTo(MessageList::class);
    }
    
    public function jobs(): HasMany
    {
        return $this->hasMany(CampaignJob::class);
    }
    
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

}
