<?php

namespace App\Services\ticket;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;

class TicketService
{

    private MsClient $msClient;
    private getMainSettingBD $Setting;
    private KassClient $kassClient;

    public function __construct(){

    }


    public function createTicket($data): JsonResponse
    {
        $accountId = $data['accountId'];
        $id_entity = $data['id_entity'];
        $entity_type = $data['entity_type'];

        $money_card = $data['money_card'];
        $money_cash = $data['money_cash'];
        $money_mobile = 0;
        $payType = $data['pay_type'];
        $total = $data['total'];

        $positions = $data['positions'];



        $this->Setting = new getMainSettingBD($accountId);
        $this->kassClient = new KassClient($this->Setting->accountId);
        $this->msClient = new MsClient($this->Setting->tokenMs);

        try {
            $oldBody = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity);
        } catch (BadResponseException $e){
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => json_decode($e->getResponse()->getBody()->getContents(), true)
            ]);
        }


        $Body = $this->setBodyToPostClient($id_entity, $entity_type, $money_card, $money_cash, $money_mobile, $payType, $total, $positions);

        if (isset($Body['Status'])) { return response()->json($Body['Message']); }

        try {
            $postTicket = $this->kassClient->ticket($Body);
        } catch (BadResponseException $e){
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => 'Ошибка отправка в ККМ: '.json_decode($e->getResponse()->getBody()->getContents(), true),
                'Body'      => $Body,
            ]);
        }

        try {
            $putBody = $this->putBodyMS($entity_type, $Body, $postTicket, $oldBody, $positions);
            $newBodyMSDocument =  $this->msClient->put('https://api.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity, $putBody);
        } catch (BadResponseException $e){
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => 'Ошибка изменения документа в МС: '.json_decode($e->getResponse()->getBody()->getContents(), true),
                'Body'      => $Body,
                'postTicket'      => $postTicket,
            ]);
        }

        $put = null;

        try {
            if ($payType == 'return'){
                $this->createReturnDocument($newBodyMSDocument, $postTicket, $putBody, $entity_type);
                $put =  $this->msClient->put('https://api.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity, [
                    'description' => $this->descriptionToCreate($oldBody, $postTicket, 'Возврат, фискальный номер: '),
                ]);
            }
            if ($this->Setting->paymentDocument != null ){
                $put = $this->createPaymentDocument($this->Setting, $entity_type, $newBodyMSDocument, $Body['payments']);
            }
        } catch (BadResponseException  $e){
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => 'Ошибка создание платёжных документа в МС: '.json_decode($e->getResponse()->getBody()->getContents(), true),
                'Body'      => $Body,
            ]);
        }

        return response()->json([
            'status'    => 'Ticket created',
            'code'      => 200,
            'postTicket' => json_decode(json_encode([
                'data' => [
                    'id' => $postTicket->data->ticket->id,
                    'receipt_number' => $postTicket->data->ticket->receipt_number,
                    'link' => $postTicket->data->ticket->link,
                ],
                'check' => $postTicket->data->check
            ])),
            'Ticket' => $postTicket,
            'New_body_MS_Document' => $newBodyMSDocument,
            'Payment_document' => $put,
        ]);

    }


    private function setBodyToPostClient(mixed $id_entity, mixed $entity_type, mixed $money_card, mixed $money_cash, mixed $money_mobile, mixed $payType, mixed $total, mixed $positions): array
    {

        $type = $this->getOperation($payType);

        $payments = $this->getPayments($money_card, $money_cash, $money_mobile, $total);
        $items = $this->getItems($positions, $id_entity, $entity_type);
        $iin = $this->getCustomer($id_entity, $entity_type);

        if ($type == '') return ['Status' => false, 'Message' => 'Не выбран тип продажи'];
        if ($payments == null) return ['Status' => false, 'Message' => 'Не были введены суммы !'];
        if ($items == null) return ['Status' => false, 'Message' => 'Отсутствуют позиции товара!'];

        if ($this->Setting->sale_point_id == null) return ['Status' => false, 'Message' => 'Отсутствуют настройки точки продаж в настройках приложение  !'];



        $result = [
           "type" => $type,
           "sale_section_id" => (int) $this->Setting->sale_point_id,
           "items" => $items,
           "payments" => $payments,
           "iin" => $iin,
        ];

        if ($result['iin'] == null){
            unset($result['iin']);
        }

        return $result;
    }


    private function getOperation($payType): int|string
    {
        return match ($payType) {
            "sell" => 2,
            "return" => 3,
            default => "",
        };
    }

    private function getPayments($card, $cash, $mobile, $total): array
    {
        $result = null;
        $tmp= null;
        if ( $cash >= 0 ) {
            $tmp[] = [
                'payment_method' => 0,
                'sum' => (float) $cash,
            ];
        }
        if ( $card >= 0 ) {
            $tmp[] = [
                'payment_method' => 1,
                'sum' => (float) $card,
            ];
        }

        foreach ($tmp as $item){
            if ($item['sum'] > 0){
                $result[] = $item;
            }
        }

        return $result;
    }

    private function getItems($positions, $idObject, $typeObject): array
    {
        $result = null;

        if ($typeObject == 'demand') { $demandPos = $this->msClient->get(($this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $typeObject . '/' . $idObject))->positions->meta->href)->rows; }

        foreach ($positions as $id => $item) {
            $TaxPercent = (float) trim($item->is_nds, '%');
            $discount = trim($item->discount, '%');
            if ($TaxPercent == 'без НДС' or $TaxPercent == "0%" or $TaxPercent == 0 or $TaxPercent == "0"){ $TaxPercent = 0; $TaxType = 0; } else $TaxType = 1;
            if ($discount > 0) { $discount = round(($item->price * $item->quantity * ($discount / 100)), 2); }

            if ($typeObject == 'demand'){

                if (isset($demandPos[$id]->trackingCodes)){
                    foreach ($demandPos[$id]->trackingCodes as $code){ $result[] = $this->itemPosition($item->name, $item->price, 1, $discount, $item->UOM, $TaxPercent, $code->cis) ; }
                } else { $result[] = $this->itemPosition($item->name, $item->price, $item->quantity, $discount, $item->UOM, $TaxPercent, '') ; }

            } else { $result[] = $this->itemPosition($item->name, $item->price, $item->quantity, $discount, $item->UOM, $TaxPercent, '') ; }
        }

        return $result;
    }

    private function getCustomer($id_entity, $entity_type)
    {
        $body =  $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity);
        $agent =  $this->msClient->get($body->agent->meta->href);
        $result = null;
        if (property_exists($agent, 'inn')) { $result = $agent->inn; }

        return $result;
    }

    private function putBodyMS($entity_type, mixed $Body, mixed $postTicket, mixed $oldBody, mixed $positionsBody): array
    {
        $result = null;
        $check_attributes_in_value_name = false;

        $attributes =  $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/metadata/attributes/')->rows;
        if (property_exists($oldBody, 'attributes')) {
            foreach ($oldBody->attributes as $item){
                if ($item->name == 'Фискальный номер (ТИС Prosklad)'){
                    $check_attributes_in_value_name = false;
                    break;
                } else $check_attributes_in_value_name = true;
            }
        } else $check_attributes_in_value_name = true;

        $Result_attributes = $this->setAttributesToPutBody($Body, $postTicket, $check_attributes_in_value_name, $attributes);
        $positions = $this->msClient->get($oldBody->positions->meta->href)->rows;
        $Resul_positions = $this->setPositionsToPutBody($postTicket, $positions, $positionsBody);
        $result['description'] = $this->descriptionToCreate($oldBody, $postTicket, 'Продажа, Фискальный номер: ');

        if ($Result_attributes != null){ $result['attributes'] = $Result_attributes; }
        if ($Resul_positions != null){ $result['positions'] = $Resul_positions; }
        return $result;
    }

    private function setAttributesToPutBody(mixed $Body, mixed $postTicket, bool $check_attributes, $attributes): array
    {
        $Result_attributes = null;
        foreach ($attributes as $item) {
            if ($item->name == "фискальный номер (ТИС Prosklad)" and $check_attributes) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => $postTicket->data->ticket->receipt_number,
                ];
            }
            if ($item->name == "Ссылка для QR-кода (ТИС Prosklad)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => $postTicket->data->ticket->link,
                ];
            }
            if ($item->name == "Фискализация (ТИС Prosklad)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => true,
                ];
            }
            if ($item->name == "ID (ТИС Prosklad)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => (string) $postTicket->data->ticket->id,
                ];
            }
        }
        return $Result_attributes;
    }

    private function setPositionsToPutBody(mixed $postTicket, mixed $positions, mixed $positionsBody): array
    {   $result = null;
        $sort = null;
        foreach ($positionsBody as $id=>$one){
            foreach ($positions as $item_p){
                if ($item_p->id == $one->id){
                    $sort[$id] = $item_p;
                }
            }
        }
        foreach ($positionsBody as $id=>$item){
            $result[$id] = [
                "id" => $item->id,
                "quantity" => (int) $item->quantity,
                "price" => (float) $item->price * 100,
                "discount" => (int) $item->discount,
                "vat" => (int) $item->is_nds,
                "assortment" => ['meta'=>[
                    "href" => $sort[$id]->assortment->meta->href,
                    "type" => $sort[$id]->assortment->meta->type,
                    "mediaType" => $sort[$id]->assortment->meta->mediaType,
                ]],
            ];
        }
        return $result;

    }

    private function createPaymentDocument( mixed $document, string $entity_type, mixed $OldBody, mixed $payments)
    {
        switch ($document->paymentDocument){
            case "1": {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                if ($entity_type != 'salesreturn') {
                    $url = $url . 'cashin';
                } else {
                    //$url = $url . 'cashout';
                    break;
                }
                $body = [
                    'organization' => [  'meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ] ],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => $OldBody->sum
                        ], ]
                ];
                return $this->msClient->post($url, $body);
                break;
            }
            case "2": {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                if ($entity_type != 'salesreturn') {
                    $url = $url . 'paymentin';
                } else {
                    //$url = $url . 'paymentout';
                    break;
                }

                $rate_body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                $rate = null;
                foreach ($rate_body as $item){
                    if ($item->name == "тенге" or $item->fullName == "Казахстанский тенге"){
                        $rate =
                            ['meta'=> [
                                'href' => $item->meta->href,
                                'metadataHref' => $item->meta->metadataHref,
                                'type' => $item->meta->type,
                                'mediaType' => $item->meta->mediaType,
                                ],
                        ];
                    }
                }

                $body = [
                    'organization' => [  'meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ] ],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => $OldBody->sum
                        ], ],
                    'rate' => $rate
                ];
                if ($body['rate'] == null) unlink($body['rate']);
                return $this->msClient->post($url, $body);
                break;
            }
            case "3": {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                $url_to_body = null;
                foreach ($payments as $item){
                    $change = 0;
                    if ($item['payment_method'] == 0){
                        if ($entity_type != 'salesreturn') {
                            $url_to_body = $url . 'cashin';
                        } else {
                            //$url_to_body = $url . 'cashout';
                            break;
                        }
                        if (isset($item['change'])) $change = $item['change'];
                    } else {
                        if ($entity_type != 'salesreturn') {
                            $url_to_body = $url . 'paymentin';
                        } else {
                            //$url_to_body = $url . 'paymentout';
                            break;
                        }
                    }

                    $rate_body =  $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                    $rate = null;
                    foreach ($rate_body as $item_rate){
                        if ($item_rate->name == "тенге" or $item_rate->fullName == "Казахстанский тенге"){
                            $rate =
                                ['meta'=> [
                                    'href' => $item_rate->meta->href,
                                    'metadataHref' => $item_rate->meta->metadataHref,
                                    'type' => $item_rate->meta->type,
                                    'mediaType' => $item_rate->meta->mediaType,
                                ],
                                ];
                        }
                    }

                    $body = [
                        'organization' => [  'meta' => [
                            'href' => $OldBody->organization->meta->href,
                            'type' => $OldBody->organization->meta->type,
                            'mediaType' => $OldBody->organization->meta->mediaType,
                        ] ],
                        'agent' => [ 'meta'=> [
                            'href' => $OldBody->agent->meta->href,
                            'type' => $OldBody->agent->meta->type,
                            'mediaType' => $OldBody->agent->meta->mediaType,
                        ] ],
                        'sum' => ($item['sum']-$change) * 100,
                        'operations' => [
                            0 => [
                                'meta'=> [
                                    'href' => $OldBody->meta->href,
                                    'metadataHref' => $OldBody->meta->metadataHref,
                                    'type' => $OldBody->meta->type,
                                    'mediaType' => $OldBody->meta->mediaType,
                                    'uuidHref' => $OldBody->meta->uuidHref,
                                ],
                                'linkedSum' => $OldBody->sum
                            ], ],
                        'rate' => $rate
                    ];
                    if ($body['rate'] == null) unlink($body['rate']);
                    return $this->msClient->post($url_to_body, $body);
                }
                break;
            }
            case "4":{
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                $url_to_body = null;
                foreach ($payments as $item){
                    $change = 0;
                    if ($item['payment_method'] == 0){
                        if ($entity_type != 'salesreturn') {
                            if ($document->OperationCash == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($document->OperationCash == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($document->OperationCash == 0) {
                                continue;
                            }
                        }
                        if (isset($item['change'])) $change = $item['change'];
                    }
                    else {
                        if ($entity_type != 'salesreturn') {
                            if ($document->OperationCard == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($document->OperationCard == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($document->OperationCard == 0) {
                                continue;
                            }
                        }
                    }

                    $rate_body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                    $rate = null;
                    foreach ($rate_body as $item_rate){
                        if ($item_rate->name == "тенге" or $item_rate->fullName == "Казахстанский тенге"){
                            $rate =
                                ['meta'=> [
                                    'href' => $item_rate->meta->href,
                                    'metadataHref' => $item_rate->meta->metadataHref,
                                    'type' => $item_rate->meta->type,
                                    'mediaType' => $item_rate->meta->mediaType,
                                ],
                                ];
                        }
                    }

                    $body = [
                        'organization' => [  'meta' => [
                            'href' => $OldBody->organization->meta->href,
                            'type' => $OldBody->organization->meta->type,
                            'mediaType' => $OldBody->organization->meta->mediaType,
                        ] ],
                        'agent' => [ 'meta'=> [
                            'href' => $OldBody->agent->meta->href,
                            'type' => $OldBody->agent->meta->type,
                            'mediaType' => $OldBody->agent->meta->mediaType,
                        ] ],
                        'sum' => ($item['sum']-$change) * 100,
                        'operations' => [
                            0 => [
                                'meta'=> [
                                    'href' => $OldBody->meta->href,
                                    'metadataHref' => $OldBody->meta->metadataHref,
                                    'type' => $OldBody->meta->type,
                                    'mediaType' => $OldBody->meta->mediaType,
                                    'uuidHref' => $OldBody->meta->uuidHref,
                                ],
                                'linkedSum' => ($item['sum']-$change) * 100
                            ], ],
                        'rate' => $rate
                    ];
                    if ($body['rate'] == null) unset($body['rate']);
                    return $this->msClient->post($url_to_body, $body);
                }
                break;
            }
            default:  return null; break;
        }
        return null;

    }

    private function createReturnDocument(mixed $newBody, mixed $putBody, mixed $oldBody, mixed $entity_type): void
    {
        if ($entity_type != 'salesreturn') {
            $attributes_item =  $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes/')->rows;
            $attributes = null;
            $positions = null;
            foreach ($attributes_item as $item){
                if ($item->name == 'фискальный номер (ТИС Prosklad)'){
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $putBody->data->ticket->receipt_number,
                    ];
                }
                if ($item->name == 'Ссылка для QR-кода (ТИС Prosklad)'){
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $putBody->data->ticket->link,
                    ];
                }
                if ($item->name == 'Фискализация (ТИС Prosklad)'){
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => true,
                    ];
                }
            }

            foreach ($oldBody['positions'] as $item) {
                unset($item['id']);
                $positions[] = $item;
            }

            $url = 'https://api.moysklad.ru/api/remap/1.2/entity/salesreturn';

            $body = [
                'organization' => [
                    'meta' => [
                        'href' => $newBody->organization->meta->href,
                        'metadataHref' => $newBody->organization->meta->metadataHref,
                        'type' => $newBody->organization->meta->type,
                        'mediaType' => $newBody->organization->meta->mediaType,
                    ]
                ],
                'agent' =>[
                    'meta' => [
                        'href' => $newBody->agent->meta->href,
                        'metadataHref' => $newBody->agent->meta->metadataHref,
                        'type' => $newBody->agent->meta->type,
                        'mediaType' => $newBody->agent->meta->mediaType,
                    ]
                ],
                'attributes' => $attributes,
                'positions' => $positions,
                'description' => 'Созданный документ возврата с ',
                'organizationAccount' => null,
                'demand' => null,
                'store' => null,
            ];

            if (isset($newBody->organizationAccount)){
                $body['organizationAccount'] = [
                    'meta' => [
                        'href' => $newBody->organizationAccount->meta->href,
                        'type' => $newBody->organizationAccount->meta->type,
                        'mediaType' => $newBody->organizationAccount->meta->mediaType,
                    ]
                ];
            }

            if (isset($newBody->store)){
                $body['store'] = [
                    'meta' => [
                        'href' => $newBody->store->meta->href,
                        'metadataHref' => $newBody->store->meta->metadataHref,
                        'type' => $newBody->store->meta->type,
                        'mediaType' => $newBody->store->meta->mediaType,
                    ]
                ];
            } else { $store =  $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store')->rows[0];
                $body['store'] = [
                    'meta' => [
                        'href' => $store->meta->href,
                        'metadataHref' => $store->meta->metadataHref,
                        'type' => $store->meta->type,
                        'mediaType' => $store->meta->mediaType,
                    ]
                ];
            }



            if ($entity_type == 'customerorder'){
                $body['description'] = $body['description'].'заказа покупателя, его номер:'. $newBody->name;
                unset($body['demand']);
            }
            if ($entity_type == 'demand'){
                $body['description'] = $body['description'].'отгрузка, его номер:'. $newBody->name;
                $body['demand'] = [
                    'meta' => [
                        'href' => $newBody->meta->href,
                        'metadataHref' => $newBody->meta->metadataHref,
                        'type' => $newBody->meta->type,
                        'mediaType' => $newBody->meta->mediaType,
                    ]
                ];
            }

            try {
                $this->msClient->post($url, $body);
            } catch (BadResponseException){

            }
        }
    }

    private function descriptionToCreate(mixed $oldBody, mixed $postTicket, $message): string
    {
        $OldMessage = '';
        if (property_exists($oldBody, 'description')) {
            $OldMessage = $oldBody->description.PHP_EOL;
        }

        return $OldMessage .'['.( (int) date('H') + 6 ).date(':i:s').' '. date('Y-m-d') .'] '. $message.$postTicket->data->ticket->receipt_number ;
    }

    private function codeUOM($UOM): JsonResponse|int|null
    {
        try {
           return $this->kassClient->unit($UOM);
        } catch (BadResponseException){
            return null;
        }
    }


    private function itemPosition(string $name, float $price, float $quantity, float $discount, int $UOM, $TaxPercent, string $code): array
    {

        $item =  [
            'name' => (string) str_replace('+', ' ', $name),
            'quantity' => round($quantity, 3),
            'price' => round($price, 2),

            'discount' => round($discount, 2),
            'excise_stamp' => $code,
            'vat_type' => (int) $TaxPercent,

            'unit_id' => (int) $this->codeUOM($UOM),
            'sale_section_id' => (int) $this->Setting->sale_point_id,

        ];

        if ($code == "") {
            unset($item['excise_stamp']);
        }
        return $item;
    }

}
