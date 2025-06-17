<?php

namespace App\Enums;

enum HttpCode: int
{
    case BAD_REQUEST = 400;
    case SUCCESS_REQUEST = 200;
}
