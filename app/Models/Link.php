<?php

namespace App\Models;

use App\Models\Campaign;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Link extends Model
{
    use HasFactory;

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact(): hasOne
    {
        return $this->belongsTo(Contact::class);
    }
}
