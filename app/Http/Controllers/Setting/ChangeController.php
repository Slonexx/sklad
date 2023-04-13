<?php

namespace App\Http\Controllers\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ChangeController extends Controller
{
    public function getChange(Request $request, $accountId){
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);
        $Config = Config::get("Global");

        try {
            $Client = new KassClient($accountId);
            if ($SettingBD->authtoken != null){
                $ArrayKassa = $Client->cashbox($SettingBD->profile_id);
            }
            else  return to_route('errorSetting', [
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                    'error' => "Токен приложения отсутствует, сообщите разработчикам приложения"]
            );
        } catch (BadResponseException $e){
            return to_route('errorSetting', [
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                    'error' => $e->getResponse()->getBody()->getContents()]
            );
        }

       // dd(json_decode($ArrayKassa->getBody()->getContents())->data);
        return view('main.change', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'ArrayKassa'=> json_decode($ArrayKassa->getBody()->getContents())->data,
            'kassa' => $SettingBD->cashbox_id,
        ]);

    }


    public function MoneyOperation(Request $request, $accountId): array
    {
        $Client = new KassClient($accountId);
        try {

            $Client->moneyMovement($request->cashbox_id, [
                'type' => $request->OperationType,
                'sum' => $request->Sum
            ]);

            $body = $Client->moneyMovement($request->cashbox_id, [
                'type' => 0,
                'sum' => 0
            ]);

            $message = "";
            if ($request->OperationType == 1){
                $message = "Изъятие из кассу наличных на сумму: ".$request->Sum.' '.PHP_EOL." Наличных осталось в кассе: ".json_decode($body->getBody()->getContents())->data->money_movement->cashbox->balance / 100 ;
            } elseif ($request->OperationType == 0) {
                $message = "Внесение в кассу наличных на сумму: ".$request->Sum.' '.PHP_EOL." Наличных осталось в кассе: ".json_decode($body->getBody()->getContents())->data->money_movement->cashbox->balance / 100;
            }

            return [
                'statusCode' => 200,
                'message' => $message,
            ];
        } catch (BadResponseException $e){
            $body = json_decode(($e->getResponse()->getBody()->getContents()));
            if (property_exists($body, 'message')){
                return [
                    'statusCode' => 500,
                    'message' => $body->message,
                ];
            } else return [
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function viewCash(Request $request, $accountId): array
    {
        $Client = new KassClient($accountId);
        try {
            $body = $Client->moneyMovement($request->cashbox_id, ['type'=>0, 'sum'=>0]);

            return [
                'statusCode' => 200,
                'message' => (float) json_decode($body->getBody()->getContents())->data->money_movement->cashbox->balance / 100,
            ];

        } catch (BadResponseException $e){
            $body = json_decode(($e->getResponse()->getBody()->getContents()));
            if (property_exists($body, 'message')){
                return [
                    'statusCode' => 500,
                    'message' => $body->message,
                ];
            } else return [
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }
}
