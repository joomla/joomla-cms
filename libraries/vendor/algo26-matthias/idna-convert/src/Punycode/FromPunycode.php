<?php

namespace Algo26\IdnaConvert\Punycode;

class FromPunycode extends AbstractPunycode implements PunycodeInterface
{
    public function __construct(?string $idnVersion = null)
    {
        parent::__construct();
    }

    /**
     * The actual decoding algorithm
     * @param string
     * @return mixed
     */
    public function convert($encoded)
    {
        if (!$this->validate($encoded)) {
            return false;
        }

        $decoded = [];
        // Find last occurrence of the delimiter
        $delimiterPosition = strrpos($encoded, '-');
        if ($delimiterPosition > self::byteLength(self::punycodePrefix)) {
            for ($k = $this->byteLength(self::punycodePrefix); $k < $delimiterPosition; ++$k) {
                $decoded[] = ord($encoded[$k]);
            }
        }
        $decodedLength = count($decoded);
        $encodedLength = $this->byteLength($encoded);

        // Wandering through the strings; init
        $isFirst = true;
        $bias = self::initialBias;
        $currentIndex = 0;
        $char = self::initialN;

        for ($encodedIndex = ($delimiterPosition) ? ($delimiterPosition + 1) : 0; $encodedIndex < $encodedLength; ++$decodedLength) {
            for ($oldIndex = $currentIndex, $w = 1, $k = self::base; 1; $k += self::base) {
                $digit = $this->decodeDigit($encoded[$encodedIndex++]);
                $currentIndex += $digit * $w;
                $t = ($k <= $bias)
                    ? self::tMin
                    : (
                        ($k >= $bias + self::tMax)
                            ? self::tMax
                            : ($k - $bias)
                    );
                if ($digit < $t) {
                    break;
                }
                $w = (int) ($w * (self::base - $t));
            }
            $bias = $this->adapt($currentIndex - $oldIndex, $decodedLength + 1, $isFirst);
            $isFirst = false;
            $char += (int) ($currentIndex / ($decodedLength + 1));
            $currentIndex %= ($decodedLength + 1);
            if ($decodedLength > 0) {
                // Make room for the decoded char
                for ($i = $decodedLength; $i > $currentIndex; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }
            $decoded[$currentIndex++] = $char;
        }

        return $this->unicodeTransCoder->convert(
            $decoded,
            $this->unicodeTransCoder::FORMAT_UCS4_ARRAY,
            $this->unicodeTransCoder::FORMAT_UTF8
        );
    }


    /**
     * Checks, whether or not the provided string is a valid punycode string
     * @param string $encoded
     * @return boolean
     */
    private function validate($encoded): bool
    {
        // Check for existence of the prefix
        if (strpos($encoded, self::punycodePrefix) !== 0) {
            return false;
        }

        // If nothing is left after the prefix, it is hopeless
        if (strlen(trim($encoded)) <= strlen(self::punycodePrefix)) {
            return false;
        }

        return true;
    }

    private function decodeDigit(string $cp): int
    {
        $cp = ord($cp);
        if ($cp - 48 < 10) {
            return $cp - 22;
        }

        if ($cp - 65 < 26) {
            return $cp - 65;
        }

        if ($cp - 97 < 26) {
            return $cp - 97;
        }

        return self::base;
    }
}
