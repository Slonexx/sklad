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
    public function PopupPrint(Request $request, $accountId, $entity_type, $object): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $Setting  = new getMainSettingBD($accountId);
        $ClientMS = new MsClient($Setting->tokenMs);
        $ClientKassa = new KassClient($accountId);
        $ExternalCheckNumber = null;

        try {

            $MS = $ClientMS->get(Config::get("Global")['ms'].$entity_type.'/'.$object);
            foreach ($MS->attributes as $item){
                if ($item->name == "ID (WebKassa)"){
                    $ExternalCheckNumber = $item->value;
                } else continue;
            }
            if ($ExternalCheckNumber != null) $Body = $ClientKassa->TicketPrint($ExternalCheckNumber);
            else  return view('popup.Print', [
                'StatusCode' => 500,
                'Message' => "Отсутствует ID (WebKassa)",
                'PrintFormat' => [],
            ]);

            return view('popup.Print', [
                'StatusCode' => 200,
                'Message' => "",
                'PrintFormat' => $Body->Data->Lines,
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
