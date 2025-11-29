<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum HttpStatus: int
{
    case OK = 200;
    case BAD_REQUEST = 400;
}
