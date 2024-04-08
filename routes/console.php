<?php

use App\Worker\CampaignWorker;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

$nonRepetableCampaigns = DB::table('campaigns')
    ->join('campaign_jobs', 'campaigns.id', '=', 'campaign_jobs.campaign_id', 'full outer')
    ->join('numbers', 'campaigns.number_id', '=', 'numbers.id', 'left')
    ->join('contact_lists', 'campaigns.contact_list', '=', 'contact_lists.id', 'left')
    ->join('message_lists', 'campaigns.message_list', '=', 'message_lists.id', 'left')
    ->whereRaw('campaigns.running = false and campaign_jobs.campaign_id IS NULL AND campaigns.cron is null and numbers.connected = true')
    ->select(
        'campaigns.*',
        'numbers.type as number_type', 'numbers.instance as number_instance', 'numbers.token as number_token',
        'contact_lists.id as list_id',
        'message_lists.messages as messages'
    )->get();


$repetableCampaigns = DB::table('campaigns')
    ->join('numbers', 'campaigns.number_id', '=', 'numbers.id', 'left')
    ->join('contact_lists', 'campaigns.contact_list', '=', 'contact_lists.id', 'left')
    ->join('message_lists', 'campaigns.message_list', '=', 'message_lists.id', 'left')
    ->whereRaw('campaigns.running = false and campaigns.cron is not null and numbers.connected = true')
    ->select(
        'campaigns.*',
        'numbers.type as number_type', 'numbers.instance as number_instance', 'numbers.token as number_token',
        'contact_lists.id as list_id',
        'message_lists.messages as messages'
    )
    ->get();

$repetableCampaigns->each(function ($campaign) {
    Schedule::call(function() use($campaign) { new CampaignWorker($campaign); })->cron($campaign->cron);
});

$nonRepetableCampaigns->each(function ($campaign) {
    new CampaignWorker($campaign);
});