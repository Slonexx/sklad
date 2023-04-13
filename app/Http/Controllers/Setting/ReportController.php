<?php

namespace App\Http\Controllers\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function XReport(Request $request, $accountId): array
    {
        $Client = new KassClient($accountId);
        try {
            $body = $Client->XReport($request->CashboxUniqueNumber);
            if (property_exists($body, "Errors")) {
                return [
                    'statusCode' => 500,
                    'message' => $body->Errors[0]->Text,
                ];
            }

            return ['Data'=> $body->Data, "statusCode"=>200];
        } catch (BadResponseException $e){
            return [
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function ZReport(Request $request, $accountId): array
    {
        $Client = new KassClient($accountId);
        $Setting = new getMainSettingBD($accountId);
        $CashboxUniqueNumber = $request->CashboxUniqueNumber;
        if ($CashboxUniqueNumber == null) {
            $CashboxUniqueNumber = $Setting->CashboxUniqueNumber;
        }
        try {
            $body = $Client->XReport($CashboxUniqueNumber);
            if (property_exists($body, "Errors")) {
                return [
                    'statusCode' => 500,
                    'message' => $body->Errors[0]->Text,
                ];
            }

            return ['Data'=> $body->Data, "statusCode"=>200];
        } catch (BadResponseException $e){
            return [
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }
}
