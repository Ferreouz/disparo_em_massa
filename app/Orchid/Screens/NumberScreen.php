<?php

namespace App\Orchid\Screens;

use Orchid\Screen\TD;
use App\Models\Number;
use Orchid\Screen\Screen;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Http;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Boolean;

class NumberScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'numbers' => Number::latest()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Números';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Adicionar número')
            ->modal('createNumberModal')
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
        $open_modal_on_open = false;
        return [
            Layout::table('numbers', [
                TD::make('name', 'Nome'),
                TD::make('number', 'Número'),
                TD::make('connected', 'Conectado')->usingComponent(Boolean::class),
            ]),

            Layout::modal('createNumberModal', Layout::rows([
                Input::make('number.name')
                    ->title('Nome')
                    ->placeholder('Dê um nome a seu número')
                    ->help('O nome para sua identificação'),
            ]))
                ->title('Adicionar número')
                ->applyButton('Adicionar número')->open($open_modal_on_open),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        // Validate form data, save task to database, etc.
        $request->validate([
            'number.name' => 'required|min:3|max:255',
        ]);
        
        $number = new Number();
        $number->name = $request->input('number.name');
        $number->user_id = auth()->user()->id;
        $number->instance = $this->generateUuid();
        $number->type = 'evolution';
        $number->token = $this->generateUuid();

        $response = Http::withHeaders([
            'apikey' => env('EVOLUTION_MASTER_KEY'),
            'Accept'=> 'application/json'
        ])
        ->post(env('EVOLUTION_URL'). "/instance/create", [
                "instanceName" =>  $number->instance,
                "token" => $number->token,
                "qrcode" => true,
                "integration" => $number->type === 'meta' ? "WHATSAPP-BUSINESS" : "WHATSAPP-BAILEYS" 
        ]);

        if(!$response->created())
        {
            Toast::error("Erro ao criar novo número. Tente dentre alguns instantes. Código de erro: " . $response->status())->delay(3000);
            return redirect()->route('number.index');
        }

        $number->save();
        return redirect()->route('number.connect', ['instance' => $number->instance]);

    }

    /**
     * 
     * @return string
     */
    private function generateUuid()
    {
        return (string) Str::uuid();
    }

    // private function error(string $message)
    // {   
        
    // }
}
