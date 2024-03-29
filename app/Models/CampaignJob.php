<?php

namespace App\Models;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignJob extends Model
{
    use HasFactory;

    //belongs to campaign
    public function campaign(): belongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
