<?php

namespace App\Clients;

use App\Http\Controllers\BD\getMainSettingBD;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class KassClient
{
    private Client $client;
    private mixed $URL;
    private getMainSettingBD $Setting;

    public function __construct($accountId)
    {
        $this->URL = Config::get("Global");
        $this->Setting = new getMainSettingBD($accountId);

        $this->client = new Client([
            'base_uri' => $this->URL['kassa'].'api/v1',
            'headers' => [
                'Authorization' => "Bearer ".$this->Setting->authtoken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function login($login, $password): \Psr\Http\Message\ResponseInterface
    {
        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $body = json_encode([ "login" => $login, "password" => $password ]);
        $request = new Request('POST', $this->URL['kassa'].'api/v1/'.'login', $headers, $body);
        return $client->send($request);
    }


    public function getCheck(): \Psr\Http\Message\ResponseInterface
    {
        return $res = $this->client->get($this->URL['kassa'].'api/v1/profile');
    }

    public function profile(): \Psr\Http\Message\ResponseInterface
    {
        return $res = $this->client->get($this->URL['kassa'].'api/v1/profile');
    }

    public function cashbox($profile_id): \Psr\Http\Message\ResponseInterface
    {
        return $res = $this->client->get($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/common/cashbox');
    }

    public function moneyMovement($cashbox_id, $body): \Psr\Http\Message\ResponseInterface
    {
        $profile_id = $this->Setting->profile_id;

        return $res = $this->client->post($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/cashbox/'.$cashbox_id.'/money-movement',[
            'body' => json_encode($body),
        ]);
    }

    public function XReport($cashbox_id): \Illuminate\Http\JsonResponse|\Psr\Http\Message\ResponseInterface
    {
        $profile_id = $this->Setting->profile_id;

        try {
            $shift = json_decode($this->ShiftId()->getBody()->getContents())->data;
            foreach ($shift as $item) {
                if ($item->cashbox_id == $cashbox_id) {
                    $shift =  $item->id;
                    break;
                } else continue;
            }

        } catch (BadResponseException $e){
            $body = json_decode(($e->getResponse()->getBody()->getContents()));
            if (property_exists($body, 'message')){
                return response()->json([
                    'statusCode' => 500,
                    'message' => $body->message,
                ], 500);
            } else return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        return $res = $this->client->get($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/programming-mode/shift/'.$shift.'/z-report');

    }

    public function ShiftId(): \Psr\Http\Message\ResponseInterface
    {
        $profile_id = $this->Setting->profile_id;
        return $res = $this->client->get($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/programming-mode/shift');
    }

    public function ZReport($cashbox_id): \Psr\Http\Message\ResponseInterface
    {
        $profile_id = $this->Setting->profile_id;
        return $res = $this->client->patch($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/cashbox/'.$cashbox_id.'/shift/close');
    }

    public function ticket($body)
    {
        $profile_id = $this->Setting->profile_id;
        $cashbox_id = $this->Setting->cashbox_id;
        $res = $this->client->post($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/cashbox/'.$cashbox_id.'/ticket',[
            'body' => json_encode($body),
        ]);
        return json_decode($res->getBody()->getContents());
    }

    public function unit($UOM): \Illuminate\Http\JsonResponse|int
    {
        $profile_id = $this->Setting->profile_id;
        try {
            $Body = $this->client->get($this->URL['kassa'].'api/v1/profile/'.$profile_id.'/common/unit');

            $res = 1;

            foreach (json_decode($Body->getBody()->getContents())->data as $item){
                if ($item->kgd_code == $UOM) {
                    $res = $item->id;
                }
            }

            return $res;
        } catch (BadResponseException $e){
            $body = json_decode(($e->getResponse()->getBody()->getContents()));
            if (property_exists($body, 'message')){
                return response()->json([
                    'statusCode' => 500,
                    'message' => $body->message,
                ], 500);
            } else return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

}
