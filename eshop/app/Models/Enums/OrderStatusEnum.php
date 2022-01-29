<?php

namespace App\Models\Enums;

enum OrderStatusEnum: string
{
    case Processing = 'processing';
    case Processed = 'processed';
    case Shipped = 'shipped';
}
