<?php
namespace Algo26\IdnaConvert\Punycode;

interface PunycodeInterface 
{
    public function __construct(string $idnVersion = null);

    public function getPunycodePrefix();
}
