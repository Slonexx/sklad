<?php

namespace App\Http\Controllers\Entity;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PrintController extends Controller
{
    public function PopupPrint(Request $request, $accountId, $entity_type, $object)
    {

        $Setting  = new getMainSettingBD($accountId);
        $ClientMS = new MsClient($Setting->tokenMs);
        $ClientKassa = new KassClient($accountId);
        $ExternalCheckNumber = null;

        try {

            $MS = $ClientMS->get(Config::get("Global")['ms'].$entity_type.'/'.$object);
            foreach ($MS->attributes as $item){
                if ($item->name == "Ссылка для QR-кода (ТИС Prosklad)"){
                    return redirect($item->value);
                } else continue;
            }

            return view('popup.Print', [
                'StatusCode' => 200,
                'Message' => "Не удалось загрузить чек, пожалуйста повторите позже",
                'PrintFormat' => "",
            ]);

        } catch (BadResponseException $e){
            return view('popup.Print', [
                'StatusCode' => 500,
                'Message' => $e->getMessage(),
                'PrintFormat' => [],
            ]);
        }




    }
}
