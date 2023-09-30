<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class cfg extends Controller
{
    public $appId;
    public $appUid;
    public $secretKey;
    public $appBaseUrl;
    public $moyskladVendorApiEndpointUrl;
    public $moyskladJsonApiEndpointUrl;


    public function __construct()
    {
        $this->appId = '6921af1e-4a3d-4449-bd1c-eb04d8afdd30';
        $this->appUid = 'prosklad.smartinnovations';
        $this->secretKey = "94t405PWPJDQdwwPXIyg5DQomiReULzdQFcsqxOwJO1hSOKlG8f8joVBoHMXSXfb39xSUkMdY1yMVSfKHNnL6i7CKDJ7HvANRGdYjlqZKi7T5AN6uKOV3jV2EHi7GwPs";
        $this->appBaseUrl = 'https://smartwiponkassa.kz/';
        $this->moyskladVendorApiEndpointUrl = 'https://apps-api.moysklad.ru/api/vendor/1.0';
        $this->moyskladJsonApiEndpointUrl = 'https://api.moysklad.ru/api/remap/1.2';
    }


}
