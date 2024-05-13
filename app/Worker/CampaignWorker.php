<?php

namespace App\Worker;

use App\Models\Campaign;
use App\Models\CampaignJob;
use App\Models\ContactList;
use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\Http;


class CampaignWorker
{

    private $max = 4.5, $min = 5;

    private $contactsProcessed = [];

    public function __construct(private $campaign)
    {
        Campaign::withoutGlobalScopes()
            ->where('id', $this->campaign->id)
            ->update(['running' => true, 'last_runned_at' => now()->toDateTimeString()]);

        if($this->campaign->delay)
        {
            $this->max = $this->campaign->delay;
            $this->min = ($this->campaign->delay > 1)
            ? $this->campaign->delay - 0.5
            : 0.8 ;
        }

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
            Sleep::for(($this->min + lcg_value() * (abs($this->max - $this->min))))->second();
            $result = "running";
            switch ($messageType) {
                case 'media':
                    $result = $this->sendMedia($contact->number, $messages[0]->media, $text);
                    break;
                case 'text':
                    $result = $this->sendText($contact->number, $text);
                    break;
                default:
                    break;
            }
            $this->contactsProcessed[$contact->id] = $result;
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
                        "mediatype" => $this->checkMime($mediaUrl), 
                        "caption" => $text ?? "",
                        "media" => $mediaUrl
                    ]
                ]);
            return $response->status();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    private function checkMime(string $mediaUrl)
    {
        $extension = substr($mediaUrl, -4);
        return str_contains($extension, ".mp4") || str_contains($extension, ".mkv") || str_contains($extension, ".avi")
                ? "video" : "image";
    }

    function __destruct()
    {
        // print "Destroying " . __CLASS__ . "\n With" . (isset($this->error) ? $this->error : 'No errors');

        #TODO check errors
        // if($this->error)
        // {
        //     Campaign::where('id', $this->id)
        //     ->update(['running' => false]);
        //     return;
        // }
        CampaignJob::create([
            'user_id' => $this->campaign->user_id,
            'campaign_id' => $this->campaign->id,
            'status' => 'finished',
            #TODO insert finished_reason, finished_at
            'contacts_processed' => json_encode($this->contactsProcessed)
        ]);

        Campaign::withoutGlobalScopes()
            ->where('id', $this->campaign->id)
            ->update(['running' => false]);
    }


}