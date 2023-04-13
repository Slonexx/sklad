<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class documentModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',
        'paymentDocument',
        'payment_type',
        'OperationCash',
        'OperationCard',
    ];

}
