<?php declare(strict_types=1);
namespace Square\Pjson;

interface FromJsonData
{
    public static function fromJsonData($jd, array|string $path = []) : static;
}
