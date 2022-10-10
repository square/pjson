<?php declare(strict_types=1);
namespace Square\Pjson;

interface ToJsonData
{
    /**
     * Returns data that can then be serialized via json_encode.
     *
     * @return mixed
     */
    public function toJsonData();
}
