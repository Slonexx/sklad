<?php

namespace App\Http\Controllers\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CreateAuthTokenController extends Controller
{
    public function getCreateAuthToken(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);

        return view('setting.authToken', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'token' => $SettingBD->authtoken,
        ]);
    }

    public function postCreateAuthToken(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $Setting = new getSettingVendorController($accountId);
        $SettingBD = new getMainSettingBD($accountId);
        $Client = new KassClient($accountId);

        if ($SettingBD->tokenMs == null) {
            DataBaseService::createMainSetting($accountId, $Setting->TokenMoySklad, $request->token);
        } else {
            DataBaseService::updateMainSetting($accountId, $Setting->TokenMoySklad, $request->token, $SettingBD->profile_id,  $SettingBD->cashbox_id,  $SettingBD->sale_point_id);
        }


        try {
            $body = $Client->getCheck();
        } catch (BadResponseException $e){
            if ($e->getCode() == 401){
                return view('setting.authToken', [
                    'accountId' => $accountId,
                    'isAdmin' => $request->isAdmin,

                    'message' => "Токен не действительный, пожалуйста введите данные заново " ,
                    'token' => null,
                ]);
            }

            return view('setting.authToken', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,

                'message' => (string) ($e->getResponse()->getBody()->getContents()) ,
                'token' => $request->token,
            ]);
        }

        $cfg = new cfg();
        $app = AppInstanceContoller::loadApp($cfg->appId, $accountId);
        $app->status = AppInstanceContoller::ACTIVATED;
        $vendorAPI = new VendorApiController();
        $vendorAPI->updateAppStatus($cfg->appId, $accountId, $app->getStatusName());
        $app->persist();

        return to_route('getKassa', ['accountId' => $accountId, 'isAdmin' => $request->isAdmin]);
    }


    public function createAuthToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $login = str_replace('+', '', str_replace(" ", '', $request->email)) ;
        $password = str_replace(" ", '', $request->password) ;

        $client = new KassClient("");
        try {
            $post = $client->login($login, $password);
            $result = [
                'status' => $post->getStatusCode(),
                'auth_token' => mb_substr(json_decode($post->getBody())->data->access_token, -40),
            ];
        } catch (BadResponseException $e){
            $result = [
                'status' => $e->getCode(),
                'auth_token' => null,
            ];
        }

        return response()->json($result);
}
}
