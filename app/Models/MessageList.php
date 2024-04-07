<?php

namespace App\Models;

use App\Models\Campaign;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class MessageList extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'number_id', 'name', 'messages'];

    /**
     * Scope a query to only include records of certain number
     */
    public function scopeOfNumber(Builder $query, int $number_id): void
    {
        $query->where('number_id', $number_id);
    }
    
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }


}
