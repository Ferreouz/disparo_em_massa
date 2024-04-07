<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([UserScope::class])]
class Number extends Model
{
    use HasFactory, AsSource;
}
