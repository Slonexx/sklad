<?php

namespace App\Services\workWithBD;


use App\Models\documentModel;
use App\Models\settingModel;
use App\Models\userLoadModel;
use App\Models\wordersModel;

class DataBaseService
{
    public static function createPersonal($accountId, $email, $name, $status){
        userLoadModel::create([
            'accountId' => $accountId,
            'email' => $email,
            'name' => $name,
            'status' => $status,
        ]);
    }
    public static function showPersonal($accountId): array
    {
        $find = userLoadModel::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'accountId' => $accountId,
                'email' => null,
                'name' => null,
                'status' => null,
            ];
        }
        return $result;
    }

    public static function updatePersonal($accountId, $email, $name, $status){
        $find = userLoadModel::query()->where('accountId', $accountId);
        $find->update([
            'email' => $email,
            'name' => $name,
            'status' => $status,
        ]);
    }

    public static function createMainSetting($accountId, $tokenMs, $authtoken){
        settingModel::create([
            'accountId' => $accountId,
            'tokenMs' => $tokenMs,
            'authtoken' => $authtoken,
        ]);
    }
    public static function showMainSetting($accountId): array
    {
        $find = settingModel::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'accountId' => $accountId,
                'tokenMs' => null,
                'authtoken' => null,

                'profile_id' => null,
                'cashbox_id' => null,
                'sale_point_id' => null,
            ];
        }
        return $result;
    }
    public static function updateMainSetting($accountId, $tokenMs, $authtoken, $profile_id, $cashbox_id, $sale_point_id){
        $find = settingModel::query()->where('accountId', $accountId);
        $find->update([
            'tokenMs' => $tokenMs,
            'authtoken' => $authtoken,

            'profile_id' => $profile_id,
            'cashbox_id' => $cashbox_id,
            'sale_point_id' => $sale_point_id,
        ]);
    }

    public static function createDocumentSetting($accountId, $paymentDocument, $payment_type, $OperationCash, $OperationCard): void
    {

        $existingRecords = documentModel::where('accountId', $accountId)->get();

        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $record->delete();
            }
        }

        documentModel::create([
            'accountId' => $accountId,
            'paymentDocument' => $paymentDocument,
            'payment_type' => $payment_type,
            'OperationCash' => $OperationCash,
            'OperationCard' => $OperationCard,
        ]);


    }
    public static function showDocumentSetting($accountId): array
    {
        $find = documentModel::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'accountId' => $accountId,
                'paymentDocument' => null,
                'payment_type' => null,
                'OperationCash' => null,
                'OperationCard' => null,
            ];
        }
        return $result;
    }

    public static function getAccessByAccountId($accountId): array
    {
        $Workers = [];
        $find = wordersModel::query()->where('accountId', $accountId)->get();

        foreach ($find as $item) {
            $json = json_encode($item->getAttributes());
            $Workers[] = json_decode($json);
        }

        return $Workers;
    }

    public static function showWorkerFirst(mixed $id): array
    {
        $find = wordersModel::query()->where('id', $id)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'id' => $id,
                'accountId' => null,
                'access' => null,
            ];
        }
        return $result;
    }

    public static function createWorker(mixed $id, mixed $accountId, mixed $access)
    {
        wordersModel::create([
            'id' => $id,
            'accountId' => $accountId,
            'access' => $access,
        ]);
    }

    public static function updateWorker(mixed $id, mixed $access)
    {
        $find = wordersModel::query()->where('id', $id);
        $find->update([
            'access' => $access,
        ]);
    }
}
