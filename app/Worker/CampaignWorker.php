<?php

namespace App\Worker;

use App\Models\Campaign;
use App\Models\CampaignJob;
use App\Models\ContactList;
use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class CampaignWorker
{

    private $max = 4.5, $min = 5;

    private $contactsProcessed = [];
    private $campaignJob;

    public function __construct(private $campaign)
    {
        Campaign::withoutGlobalScopes()
            ->where('id', $this->campaign->id)
            ->update(['running' => true]);

        if($this->campaign->delay)
        {
            $this->max = $this->campaign->delay;
            $this->min = ($this->campaign->delay > 1)
            ? $this->campaign->delay - 0.5
            : 0.8 ;
        }
        $this->campaignJob = new CampaignJob([
            'user_id' => $this->campaign->user_id,
            'campaign_id' => $this->campaign->id,
            'status' => 'running',
            'contacts_processed' => json_encode($this->contactsProcessed)
        ]);
        $this->campaignJob->save();

        Sleep::for((2 + lcg_value() * (abs(6 - 2))))->minutes();
        $this->run();
    }


    /**
     * Repetable campaigns
     */
    private function run()
    {

        $messages = json_decode($this->campaign->messages);
        $contacts = $this->getContacts($this->campaign->list_id);

        #TODO send message in order
        $text = $messages[0]->text;
        $messageType = isset($messages[0]->media) ? 'media' : 'text';

        #TODO switch number_type
        foreach ($contacts as $contact)
        {

            #TODO check if contact didnt receive ($contact->id in $this->contactsProcessed)
            $result = "running";
            switch ($messageType)
            {
                case 'media':
                    $result = $this->sendMedia($contact->number, $messages[0]->media, $text);
                    break;
                case 'text':
                    $result = $this->sendText($contact->number, $text);
                    break;
                default:
                    break;
            }
            $this->contactsProcessed[$contact->id] = ["status" => $result, "time" => now()->toDateTimeString()];
            $this->campaignJob->contacts_processed = json_encode($this->contactsProcessed);#TODO update json in eloquent
            $this->campaignJob->save();
            Sleep::for(($this->min + lcg_value() * (abs($this->max - $this->min))))->minutes();
        }

    }

    private function getContacts(int $listId)
    {
        return ContactList::where('id', $listId)->withoutGlobalScopes()->first()?->contacts;
    }

    /**
     * Send text messages
     */
    private function sendText(string $contactNumber, string $text): int|string
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->campaign->number_token,
                'Accept' => 'application/json'
            ])
                ->post(env('EVOLUTION_URL') . "/message/sendText/" . $this->campaign->number_instance, [
                    "number" => $contactNumber,
                    "options" => [
                        "delay" => rand(1000, 5000),
                        "presence" => "composing",
                        "linkPreview" => false
                    ],
                    "textMessage" => [
                        "text" => $text
                    ]
                ]);
            return $response->status();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /**
     * Send text messages
     */
    private function sendMedia(string $contactNumber, string $mediaUrl, string $text = ""): int|string
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->campaign->number_token,
                'Accept' => 'application/json'
            ])
                ->post(env('EVOLUTION_URL') . "/message/sendMedia/" . $this->campaign->number_instance, [
                    "number" => $contactNumber,
                    "options" => [
                        "delay" => rand(1000, 5000),
                        "presence" => "composing",
                        "linkPreview" => false
                    ],
                    "mediaMessage" => [
                        "mediatype" => "image",#TODO compute mimetype
                        "caption" => $text ?? "",
                        "media" => $mediaUrl
                    ]
                ]);
            return $response->status();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


    function __destruct()
    {
        print "Destroying " . __CLASS__ . "\n With" . (isset($this->error) ? $this->error : 'No errors');

        #TODO check errors
        // if($this->error)
        // {
        //     Campaign::where('id', $this->id)
        //     ->update(['running' => false]);
        //     return;
        // }

        #TODO insert finished_reason, finished_at
        $this->campaignJob->status = 'finished'; 
        $this->campaignJob->contacts_processed = json_encode($this->contactsProcessed);
        $this->campaignJob->save();

        Campaign::withoutGlobalScopes()
            ->where('id', $this->campaign->id)
            ->update(['running' => false, 'last_runned_at' => now()->toDateTimeString()]);
    }


}