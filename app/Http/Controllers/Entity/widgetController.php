<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\BD\getWorkerID;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class widgetController extends Controller
{
    public function widgetObject(Request $request, $object): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        try {
            $vendorAPI = new VendorApiController();
            $employee = $vendorAPI->context($request->contextKey);
            $accountId = $employee->accountId;

            //$accountId = "1dd5bd55-d141-11ec-0a80-055600047495";
            //$Workers = new getWorkerID("e793faeb-e63a-11ec-0a80-0b4800079eb3");

            $Workers = new getWorkerID($employee->id);
            $Setting = new getMainSettingBD($accountId);
            $Client = new MsClient($Setting->tokenMs);

            $body = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/employee");

            if ($Workers->access == 0 or $Workers->access = null){ return view( 'widget.noAccess', ['accountId' => $accountId, ] ); }

            return view( 'widget.object', [
                'accountId' => $accountId,
                'entity' => $object,

                'cashbox_id' => $Setting->cashbox_id,
            ] );

        } catch (BadResponseException $e){

            $error = json_decode($e->getResponse()->getBody()->getContents());
            if (property_exists($error, 'errors')) {
                foreach ($error->errors as $item){
                    $message[] = $item->error;
                }
            } else {
                $message[] = $error;
            }

            return view( 'widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => $message,
            ] );
        }
    }


    public function widgetInfoAttributes(Request $request)
    {
        $ticket_id = null;

        $accountId = $request->accountId;
        $entity_type = $request->entity_type;
        $objectId = $request->objectId;

        $url = $this->getUrlEntity($entity_type, $objectId);
        $Setting = new getSettingVendorController($accountId);
        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $body = $Client->get($url);
        } catch (BadResponseException $e){
            return view( 'widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ] );
        }

        if (property_exists($body, 'attributes')){
            foreach ($body->attributes as $item){
                if ($item->name == 'фискальный номер (ТИС Prosklad)'){
                    if ($item->value != null) $ticket_id = $item->value;
                    break;
                }
            }
        }
        return response()->json(['ticket_id' => $ticket_id]);
    }





    private function getUrlEntity($enType,$enId): ?string
    {
        return match ($enType) {
            "customerorder" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/" . $enId,
            "demand" => "https://online.moysklad.ru/api/remap/1.2/entity/demand/" . $enId,
            "salesreturn" => "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/" . $enId,
            default => null,
        };
    }
}
