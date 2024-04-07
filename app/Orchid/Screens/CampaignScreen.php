<?php

namespace App\Orchid\Screens;

use App\Models\Number;
use App\Models\Campaign;
use Orchid\Screen\Screen;
use App\Models\ContactList;
use App\Models\MessageList;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;

class CampaignScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $campaigns = Campaign::orderBy('updated_at', 'desc')->paginate(10);
        return [
            'campaigns' => $campaigns,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Campanhas';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('NOVO')
            ->modal('createCampaignModal')
            ->method('create')
            ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $numbersArray = [];

        $numbers = Number::all();
        foreach ($numbers as $number) {
            $numbersArray[$number->id]  = $number->name . " (". $number->number. ")";
        }

        $selectNumbers = Select::make('simple.number')
            ->title('Número que irá enviar')
            ->options($numbersArray);
        if(count($numbersArray) > 1) 
        {            
            $selectNumbers->empty('Escolha um dos números para realizar o envio');
        } 
        if(count($numbersArray) === 0) 
        {
            $selectNumbers->empty('Nenhum número para fazer envios encontrado!');
        }

        $listsArray = [];

        $lists = ContactList::all();
        foreach ($lists as $list) {
            $listsArray[$list->id]  = $list->name ;
        }

        $selectLists = Select::make('simple.contact_list')
            ->title('Lista de contatos')
            ->options($listsArray);
        if(count($listsArray) > 1) 
        {            
            $selectLists->empty('Escolha uma lista cadastrada');
        }
        if(count($listsArray) === 0) 
        {
            $selectLists->empty('Nenhuma lista de contatos encontrada!');
        }



        return [
          
            Layout::modal('createCampaignModal', 
            Layout::tabs([
                'Simples' => [
                    Layout::rows([
                        Picture::make('simple.image')->storage('public')
                        ->formnovalidate()
                            ->maxFiles(1)
                            ->title('IMAGEM')
                            ->help('Selecione a imagem da mensagem'),
                        TextArea::make('simple.text')
                            ->title('TEXTO')
                            ->placeholder('Digite aqui')
                            ->help('Digite o texto da mensagem')
                            ->rows(5),
                        $selectNumbers,
                        $selectLists,
                        CheckBox::make('simple.everyday')
                                ->placeholder('Repetir todo dia?')
                                ->help('Todo dia no mesmo horário'),
                        Select::make('simple.delay')
                            ->options([
                                5 => '5+ min (melhor)',
                                2 => '3 min (risco considerável)',
                                1 => '<1 min (risco alto)',
                            ])
                            ->title('DELAY')
                            ->help('Delay entre um contato e outro'),
                    ])
                ],
                'Avançado' => [
                    Layout::rows([
                   
                        TextArea::make('task.text')
                            ->title('TEXTO')
                            ->placeholder('Digite aqui')
                            ->help('Digite o texto da mensagem')
                            ->rows(5),
                    ])
                ],
            ]))
                ->title('Digite a mensagem')
                ->applyButton('Enviar')
                ->closeButton('Cancelar'),
        ];
    }

    /**
     * @param Request $request
     *
     * @return void
    */
    public function create(Request $request)
    {
        // dd($request);
        // Validate form data, save task to database, etc.
        $request->validate([
            'simple.contact_list' => 'required|integer',
            'simple.text' => 'required_without:simple.image|min:5',
            'simple.image' => 'required_without:simple.text',
            'simple.number' => 'required|integer'
        ]);
        $this->createSimple($request->input('simple'));
        
    }

    private function createSimple(array $input)
    {
        $user_id = auth()->user()->id;
        $number_id = $input['number'];
        $json[0] = 
        [
            'text'  => (array_key_exists('text', $input) && $input['text'])   ? $input['text'] : null,
            'order' => 0,
            # TODO remover server path
            'media' => (array_key_exists('image', $input) && $input['image']) ? $input['image'] : null,
        ];
        array_filter($json, fn($value) => !is_null($value) );
        $message = MessageList::create([
            'user_id'  => $user_id,
            'number_id'=> $number_id,
            # TODO
            'name'     => "Simple Test",
            'messages' => json_encode($json)
        ]);
        $campaign = new Campaign([
            'user_id'      => $user_id,
            'number_id'    => $number_id,
            'contact_list' => $input['contact_list'],
            'message_list' => $message->id,
            'cron'         =>array_key_exists('everyday', $input) && $input['everyday'] === "on"
                                ? date('i') . " " .  date('h') . " * * *" #TODO date i está "01"
                                : null,
            #TODO $campaign->name = "??";
        ]);
        
        $campaign->save();
    }
}
