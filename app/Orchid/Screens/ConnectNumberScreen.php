<?php

namespace App\Orchid\Screens;

use Orchid\Screen\TD;
use App\Models\Number;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Http;

class ConnectNumberScreen extends Screen
{
    public $base64;
    public $number;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(string $instance): iterable
    {
        /**
         * Search instance if not found redirect
         */
        $this->number = Number::where('instance', $instance)->first();

        return [
            'instance' => $instance,
            'number' =>  $this->number
        ];
    }
    public function goBack()
    {
        return redirect()->route('number.index');
    }
    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Conected o seu número';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::split([
                Layout::view('user.qrcode'),
                Layout::view('user.qrcode-instructions'),

            ])->ratio('40/60')->reverseOnPhone(),
        ];
    }

//     public function getQrCode(Request $request)
//     {
//         $instance = $request->instance;
//         $number = Number::withoutGlobalScopes()->where('instance', $instance)->first();
//         if(!$number)
//         {
//             return ['error' => 'instance not found'];
//         }

//         $response = Http::withHeaders([
//             'apikey' => $number->token,
//             'Accept'=> 'application/json'
//         ])
//         ->get(env('EVOLUTION_URL'). "/instance/connect/$number->instance");
        
//         if(!$response->ok()) 
//         {
//             return ['error' => 'instance not found'];
//         }

//         return ['base64' => $response->json()['base64'] ];
//     }
}
