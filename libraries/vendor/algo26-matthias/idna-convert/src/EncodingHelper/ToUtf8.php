<?php

namespace Algo26\IdnaConvert\EncodingHelper;

class ToUtf8 implements EncodingHelperInterface
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
            return utf8_encode($sourceString);
        }

        if ($this->encoding === 'WINDOWS-1252') {
            return utf8_encode($this->mapWindows1252ToIso8859_1($sourceString));
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
     * @param  string $string Your input in Win1252
     *
     * @return string  The resulting ISO-8859-1 string
     * @since 0.0.1
     */
    private function mapWindows1252ToIso8859_1($string = '')
    {
        $return = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $codePoint = ord($string[$i]);
            switch ($codePoint) {
                case 129:
                    $return .= chr(252);
                    break;
                case 132:
                    $return .= chr(228);
                    break;
                case 142:
                    $return .= chr(196);
                    break;
                case 148:
                    $return .= chr(246);
                    break;
                case 153:
                    $return .= chr(214);
                    break;
                case 154:
                    $return .= chr(220);
                    break;
                case 225:
                    $return .= chr(223);
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
            $converted = @mb_convert_encoding($string, 'UTF-8', $this->encoding);
            if (false !== $converted) {
                return $converted;
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv($this->encoding, 'UTF-8', $string);
            if (false !== $converted) {
                return $converted;
            }
        }

        if (function_exists('libiconv')) {
            $converted = @libiconv($this->encoding, 'UTF-8', $string);
            if (false !== $converted) {
                return $converted;
            }
        }

        return false;
    }
}
