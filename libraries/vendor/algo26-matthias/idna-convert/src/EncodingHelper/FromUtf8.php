<?php

namespace Algo26\IdnaConvert\EncodingHelper;

class FromUtf8 implements EncodingHelperInterface
{
    private const DEFAULT_ENCODING = 'ISO-8859-1';

    private $encoding = self::DEFAULT_ENCODING;

    public function convert(
        string $sourceString,
        ?string $encoding = self::DEFAULT_ENCODING,
        ?bool $safeMode = false
    ) {
        $safe = ($safeMode) ? $sourceString : false;

        if ($encoding !== null) {
            $this->encoding = strtoupper($encoding);
        } else {
            $this->encoding = 'ISO-8859-1';
        }

        if ($this->encoding === 'UTF-8' || $this->encoding === 'UTF8') {
            return $sourceString;
        }

        if ($this->encoding === 'ISO-8859-1') {
            return utf8_decode($sourceString);
        }

        if ($this->encoding === 'WINDOWS-1252') {
            return self::mapIso8859_1ToWindows1252(utf8_decode($sourceString));
        }

        if ($this->encoding === 'UNICODE-1-1-UTF-7') {
            $this->encoding = 'UTF-7';
        }

        $converted = $this->convertWithLibraries($sourceString);
        if (false !== $converted) {
            return $converted;
        }

        return $safe;
    }

    /**
     * Special treatment for our guys in Redmond
     * Windows-1252 is basically ISO-8859-1 -- with some exceptions, which get dealt with here
     *
     * @param  string $string Your input in ISO-8859-1
     *
     * @return  string  The resulting Win1252 string
     * @since 0.0.1
     */
    private function mapIso8859_1ToWindows1252($string = '')
    {
        $return = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $codePoint = ord($string[$i]);
            switch ($codePoint) {
                case 196:
                    $return .= chr(142);
                    break;
                case 214:
                    $return .= chr(153);
                    break;
                case 220:
                    $return .= chr(154);
                    break;
                case 223:
                    $return .= chr(225);
                    break;
                case 228:
                    $return .= chr(132);
                    break;
                case 246:
                    $return .= chr(148);
                    break;
                case 252:
                    $return .= chr(129);
                    break;
                default:
                    $return .= chr($codePoint);
            }
        }

        return $return;
    }

    private function convertWithLibraries(string $string): ?string
    {
        if (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($string, $this->encoding, 'UTF-8');
            if (false !== $converted) {
                return $converted;
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', $this->encoding, $string);
            if (false !== $converted) {
                return $converted;
            }
        }

        if (function_exists('libiconv')) {
            $converted = @libiconv('UTF-8', $this->encoding, $string);
            if (false !== $converted) {
                return $converted;
            }
        }

        return false;
    }
}
