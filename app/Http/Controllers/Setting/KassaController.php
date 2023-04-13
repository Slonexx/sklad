<?php

namespace App\Http\Controllers\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class KassaController extends Controller
{
    public function getKassa(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);
        $tokenMs = $SettingBD->tokenMs;

        $profile_id = $SettingBD->profile_id;
        $cashbox_id = $SettingBD->cashbox_id;
        $sale_point_id = $SettingBD->sale_point_id;

        if ($tokenMs == null){
            return view('setting.no', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
            ]);
        }


        if (isset($request->message)) {
            return view('setting.kassa', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'message' => $request->message["message"],
            ]);
        }

        return view('setting.kassa', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

        ]);
    }


    public function profile_id(Request $request, $accountId): \Illuminate\Http\JsonResponse
    {
        $Client = new KassClient($accountId);


        try {
            return response()->json(['Code'=> 200, 'Data'=>json_decode($Client->profile()->getBody()->getContents())->data]);
        } catch (BadResponseException $e){
            return response()->json(['Code'=> 500, 'Data'=>$e->getMessage()]);
        }

    }


    public function cashbox_id(Request $request, $accountId): \Illuminate\Http\JsonResponse
    {
        $Client = new KassClient($accountId);
        $profile_id = $request->profile_id;

        try {
            return response()->json(['Code'=> 200, 'Data'=>json_decode($Client->cashbox($profile_id)->getBody()->getContents())->data]);
        } catch (BadResponseException $e){
            return response()->json(['Code'=> 500, 'Data'=>$e->getMessage()]);
        }
    }

    public function postKassa(Request $request, $accountId): \Illuminate\Http\RedirectResponse
    {
        //dd($request->all());
        $Setting = new getMainSettingBD($accountId);
        try {
            DataBaseService::updateMainSetting($accountId,$Setting->tokenMs, $Setting->authtoken, $request->profile_id, $request->cashbox_id, $request->sale_point_id);
        } catch (\Throwable $e){
            $message["getCode"] = "Ошибка " . $e->getCode();
            $message["message"] = "Ошибка " . $e->getMessage();
            return redirect()->route('getDocument', [ 'accountId' => $accountId, 'isAdmin' => $request->isAdmin, 'message'=>$message ]);
        }

        return redirect()->route('getDocument', [ 'accountId' => $accountId, 'isAdmin' => $request->isAdmin, 'message'=>"" ]);
    }
}
