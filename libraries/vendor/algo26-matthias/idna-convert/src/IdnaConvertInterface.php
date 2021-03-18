<?php
namespace Algo26\IdnaConvert;

interface IdnaConvertInterface
{
    public function convert(string $host): string;

    public function convertEmailAddress(string $emailAddress): string;

    public function convertUrl(string $url): string;
}
