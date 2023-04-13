<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class getMainSettingBD extends Controller
{
    public mixed $accountId;
    public mixed $tokenMs;
    public mixed $authtoken;

    public mixed $profile_id;
    public mixed $cashbox_id;
    public mixed $sale_point_id;

    public mixed $paymentDocument;
    public mixed $payment_type;
    public mixed $OperationCash;
    public mixed $OperationCard;

    /**
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $BD = DataBaseService::showMainSetting($accountId);
        $this->accountId = $BD['accountId'];
        $this->tokenMs = $BD['tokenMs'];
        $this->authtoken = $BD['authtoken'];

        $this->profile_id = $BD['profile_id'];
        $this->cashbox_id = $BD['cashbox_id'];
        $this->sale_point_id = $BD['sale_point_id'];

        $json = DataBaseService::showDocumentSetting($accountId);
        $this->paymentDocument = $json['paymentDocument'];
        $this->payment_type = $json['payment_type'];
        $this->OperationCash = $json['OperationCash'];
        $this->OperationCard = $json['OperationCard'];
    }


}
