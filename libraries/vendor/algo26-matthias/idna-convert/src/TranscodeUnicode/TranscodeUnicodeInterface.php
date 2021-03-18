<?php
namespace Algo26\IdnaConvert\TranscodeUnicode;

interface TranscodeUnicodeInterface
{
    public function convert(
        $data,
        string $fromEncoding,
        string $toEncoding,
        bool $safeMode = false,
        int $safeCodepoint = 0xFFFC
    );
}
