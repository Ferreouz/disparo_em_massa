<?php

namespace App\Worker;
use App\Models\CampaignJob;
use App\Models\ContactList;
use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class CampaignWorker
{
   
    public function __invoke() 
    {

        $this->notRepetable();

        // $query = DB::table('campaigns')->join('campaign_jobs', 'campaigns.id', '=', 'campaign_jobs.campaign_id', 'full outer')
        //     ->whereNotNull('cron')
        //     ->orWhereRaw('campaign_jobs.campaign_id IS NULL AND campaigns.cron is null')
        //     ->select('campaigns.*', 'campaign_jobs.status')
        //     ->get();
    
     
    }

    /**
    * Not repetable campaigns
    */
    private function notRepetable()
    {
        $campaigns = DB::table('campaigns')
            ->join('campaign_jobs', 'campaigns.id', '=', 'campaign_jobs.campaign_id', 'full outer')
            ->join('numbers', 'campaigns.number_id', '=', 'numbers.id', 'left')
            ->join('contact_lists', 'campaigns.contact_list', '=', 'contact_lists.id', 'left')
            ->join('message_lists', 'campaigns.message_list', '=', 'message_lists.id', 'left')
            ->whereRaw('campaign_jobs.campaign_id IS NULL AND campaigns.cron is null and numbers.connected = true')
            ->whereDate('campaigns.last_runned_at', '<', now()->subMinutes(10))
            ->select(
                'campaigns.*', 
                'numbers.type as number_type', 'numbers.instance as number_instance', 'numbers.token as number_token', 
                'contact_lists.id as list_id',
                'message_lists.messages as messages'
                )->get();
            
        #TODO concurrent for each campaign
        foreach ($campaigns as $campaign) 
        {
            $contactsProcessed = [];            

            $messages = json_decode($campaign->messages);
            $contacts = $this->getContacts($campaign->list_id);
            
            #TODO send message in order
            $text = $messages[0]->text;
            $messageType = isset($message[0]->media)? 'media' : 'text'; 
            
            #TODO switch number_type
            foreach ($contacts as $contact) 
            {
                Sleep::for(rand(1, 5))->second();
                $result = "running";
                switch ($messageType) 
                {
                    case 'media':
                        $result = $this->sendMedia($campaign, $contact->number, $messages[0]->media, $text);
                        break;
                    case 'text':
                        $result = $this->sendText($campaign, $contact->number, $text);
                        break;
                    default:
                        break;
                }       
                $contactsProcessed[$contact->id] = $result;
                var_dump($contactsProcessed);          

            }

            CampaignJob::create([
                'user_id'            => $campaign->user_id,
                'campaign_id'        => $campaign->id,
                'status'             => 'running',
                'contacts_processed' => json_encode($contactsProcessed)  
            ]);
        }


    }
    private function getContacts(int $listId)
    {
        return ContactList::where('id', $listId)->withoutGlobalScopes()->first()?->contacts;
    }


    /**
     * Send text messages
     */
    private function sendText($campaign, string $contactNumber, string $text): int|string
    {
        try {
            $response = Http::withHeaders([
            'apikey' => $campaign->number_token,
                'Accept'=> 'application/json'
            ])
            ->post(env('EVOLUTION_URL') . "/message/sendText/$campaign->number_instance", [
                    "number"          =>  $contactNumber,
                    "options"         => [
                        "delay"       => rand(1000, 5000),
                        "presence"    => "composing",
                        "linkPreview" => false
                    ],
                    "textMessage"     => [
                        "text"        => $text . (string) rand(1, 5)
                    ]
            ]);
            return $response->status();
            //code...
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

        /**
     * Send text messages
     */
    private function sendMedia($campaign, string $contactNumber, string $mediaUrl, string $text = ""): int|string
    {
        // dd($campaign, $text, $contactNumber);
        $response = Http::withHeaders([
            'apikey' => $campaign->number_token,
            'Accept'=> 'application/json'
        ])
        ->post(env('EVOLUTION_URL') . "/message/sendText/$campaign->number_instance", [
                "number"          =>  $contactNumber,
                "options"         => [
                    "delay"       => rand(1000, 5000),
                    "presence"    => "composing",
                    "linkPreview" => false
                ],
                "mediaMessage"    => [
                    "mediatype"   => "image",
                    "caption"     => $text  ?? "",
                    "media"       => $mediaUrl
                ]
        ]);
        return $response->status();

    }


}