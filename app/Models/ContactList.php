<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([UserScope::class])]
class ContactList extends Model
{
    use HasFactory;

    /**
    * Scope a query to only include records of certain number
    */
    public function scopeOfNumber(Builder $query, int $number_id): void
    {
        $query->where('number_id', $number_id);
    }

    public function contacts(): BelongsToMany {
        return $this->belongsToMany(Contact::class, 'contact_contact_lists', 'contact_list_id', 'contact_id');
    }
}
