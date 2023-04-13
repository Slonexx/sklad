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
}
