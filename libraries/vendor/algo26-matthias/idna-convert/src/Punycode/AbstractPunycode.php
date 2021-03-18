<?php

namespace Algo26\IdnaConvert\Punycode;

use Algo26\IdnaConvert\TranscodeUnicode\TranscodeUnicode;

abstract class AbstractPunycode
{
    const punycodePrefix = 'xn--';
    const invalidUcs = 0x80000000;
    const maxUcs = 0x10FFFF;
    const base = 36;
    const tMin = 1;
    const tMax = 26;
    const skew = 38;
    const damp = 700;
    const initialBias = 72;
    const initialN = 0x80;

    protected static $isMbStringOverload;
    protected static $prefixAsArray;
    protected static $prefixLength;

    /** @var TranscodeUnicode */
    protected $unicodeTransCoder;

    public function __construct()
    {
        $this->unicodeTransCoder = new TranscodeUnicode();

        // populate mbstring overloading cache if not set
        if (self::$isMbStringOverload === null) {
            self::$isMbStringOverload = (extension_loaded('mbstring')
                                         && (ini_get('mbstring.func_overload') & 0x02) === 0x02);
        }

        if (self::$prefixAsArray === null) {
            self::$prefixAsArray = $this->unicodeTransCoder->convert(
                self::punycodePrefix,
                $this->unicodeTransCoder::FORMAT_UTF8,
                $this->unicodeTransCoder::FORMAT_UCS4_ARRAY
            );
            self::$prefixLength = $this->byteLength(self::punycodePrefix);
        }
    }

    public function getPunycodePrefix(): string
    {
        return self::punycodePrefix;
    }

    /**
     * Gets the length of a string in bytes even if mbstring function
     * overloading is turned on
     *
     * @param string $string the string for which to get the length.
     * @return integer the length of the string in bytes.
     */
    protected function byteLength($string): int
    {
        if (self::$isMbStringOverload) {
            return mb_strlen($string, '8bit');
        }

        return strlen((binary) $string);
    }


    /**
     * Adapt the bias according to the current code point and position
     * @param int $delta
     * @param int $nPoints
     * @param int $isFirst
     * @return int
     */
    protected function adapt($delta, $nPoints, $isFirst): int
    {
        $delta = intval($isFirst ? ($delta / self::damp) : ($delta / 2));
        $delta += intval($delta / $nPoints);
        for ($k = 0; $delta > ((self::base - self::tMin) * self::tMax) / 2; $k += self::base) {
            $delta = intval($delta / (self::base - self::tMin));
        }

        return intval($k + (self::base - self::tMin + 1) * $delta / ($delta + self::skew));
    }
}
