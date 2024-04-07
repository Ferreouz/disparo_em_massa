<?php

namespace App\Models;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignJob extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'campaign_id', 'status', 'contacts_processed'];


    //belongs to campaign
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
