<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function getDocument(Request $request, $accountId){
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);
        $tokenMs = $SettingBD->tokenMs;
        $paymentDocument = $SettingBD->paymentDocument;
        $payment_type = $SettingBD->payment_type;
        $OperationCash = $SettingBD->OperationCash;
        $OperationCard = $SettingBD->OperationCard;

        if ($tokenMs == null){
            return view('setting.no', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
            ]);
        }
        if ($paymentDocument == null) {
            $paymentDocument = "0";
        }
        if ($payment_type == null) {
            $payment_type = "1";
        }
        if ($OperationCash == null) {
            $OperationCash = "0";
        }
        if ($OperationCard == null) {
            $OperationCard = "0";
        }

        if (isset($request->message)) {
            return view('setting.document', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'message' => $request->message["message"],
                'paymentDocument' => $paymentDocument,
                'payment_type' => $payment_type,
                'OperationCash' => $OperationCash,
                'OperationCard' => $OperationCard,
            ]);
        }

        return view('setting.document', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'paymentDocument' => $paymentDocument,
            'payment_type' => $payment_type,
            'OperationCash' => $OperationCash,
            'OperationCard' => $OperationCard,
        ]);
    }

    public function postDocument(Request $request, $accountId): \Illuminate\Http\RedirectResponse
    {
        try {
            DataBaseService::createDocumentSetting($accountId, $request->createDocument_asWay, $request->payment_type, $request->OperationCash, $request->OperationCard);
        } catch (\Throwable $e){
            $message["getCode"] = "Ошибка " . $e->getCode();
            $message["message"] = "Ошибка " . $e->getMessage();
            return redirect()->route('getDocument', [ 'accountId' => $accountId, 'isAdmin' => $request->isAdmin, 'message'=>$message ]);
        }

        return redirect()->route('getWorker', [ 'accountId' => $accountId, 'isAdmin' => $request->isAdmin, 'message'=>"" ]);

    }
}
