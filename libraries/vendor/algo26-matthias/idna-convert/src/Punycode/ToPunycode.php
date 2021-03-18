<?php

namespace Algo26\IdnaConvert\Punycode;

use Algo26\IdnaConvert\Exception\AlreadyPunycodeException;
use Algo26\IdnaConvert\Exception\InvalidCharacterException;
use Algo26\IdnaConvert\Exception\InvalidIdnVersionException;
use Algo26\IdnaConvert\NamePrep\NamePrep;

class ToPunycode extends AbstractPunycode implements PunycodeInterface
{
    /** @var NamePrep */
    private $namePrep;

    /**
     * @throws InvalidIdnVersionException
     */
    public function __construct(?string $idnVersion = null)
    {
        $this->namePrep = new NamePrep($idnVersion);
        parent::__construct();
    }

    /**
     * @param array $decoded
     *
     * @return string
     * @throws AlreadyPunycodeException
     * @throws InvalidCharacterException
     */
    public function convert(array $decoded): string
    {
        // We cannot encode a domain name containing the Punycode prefix
        $checkForPrefix = array_slice($decoded, 0, self::$prefixLength);
        if (self::$prefixAsArray === $checkForPrefix) {
            throw new AlreadyPunycodeException('This is already a Punycode string', 100);
        }
        // We will not try to encode strings consisting of basic code points only
        $canEncode = false;
        foreach ($decoded as $k => $v) {
            if ($v > 0x7a) {
                $canEncode = true;
                break;
            }
        }
        if (!$canEncode) {
            return false;
        }

        // Do NAMEPREP
        $decoded = $this->namePrep->do($decoded);
        if (!$decoded || !is_array($decoded)) {
            return false; // NAMEPREP failed
        }

        $decodedLength = count($decoded);
        if (!$decodedLength) {
            return false; // Empty array
        }

        $codeCount = 0; // How many chars have been consumed
        $encoded = '';
        // Copy all basic code points to output
        for ($i = 0; $i < $decodedLength; ++$i) {
            $test = $decoded[$i];
            // Will match [-0-9a-zA-Z]
            if ((0x2F < $test && $test < 0x40)
                || (0x40 < $test && $test < 0x5B)
                || (0x60 < $test && $test <= 0x7B)
                || (0x2D == $test)
            ) {
                $encoded .= chr($decoded[$i]);
                $codeCount++;
            }
        }
        if ($codeCount === $decodedLength) {
            return $encoded; // All codepoints were basic ones
        }

        // Start with the prefix; copy it to output
        $encoded = self::punycodePrefix . $encoded;
        // If we have basic code points in output, add an hyphen to the end
        if ($codeCount) {
            $encoded .= '-';
        }
        // Now find and encode all non-basic code points
        $isFirst = true;
        $currentCode = self::initialN;
        $bias = self::initialBias;
        $delta = 0;
        while ($codeCount < $decodedLength) {
            // Find the smallest code point >= the current code point and
            // remember the last occurrence of it in the input
            for ($i = 0, $nextCode = self::maxUcs; $i < $decodedLength; $i++) {
                if ($decoded[$i] >= $currentCode && $decoded[$i] <= $nextCode) {
                    $nextCode = $decoded[$i];
                }
            }
            $delta += ($nextCode - $currentCode) * ($codeCount + 1);
            $currentCode = $nextCode;

            // Scan input again and encode all characters whose code point is $currentCode
            for ($i = 0; $i < $decodedLength; $i++) {
                if ($decoded[$i] < $currentCode) {
                    $delta++;
                } elseif ($decoded[$i] == $currentCode) {
                    for ($q = $delta, $k = self::base; 1; $k += self::base) {
                        $t = ($k <= $bias)
                            ? self::tMin
                            : (($k >= $bias + self::tMax)
                                ? self::tMax
                                : $k - $bias
                            );
                        if ($q < $t) {
                            break;
                        }

                        $encoded .= $this->encodeDigit(intval($t + (($q - $t) % (self::base - $t))));
                        $q = (int) (($q - $t) / (self::base - $t));
                    }
                    $encoded .= $this->encodeDigit($q);
                    $bias = $this->adapt($delta, $codeCount + 1, $isFirst);
                    $codeCount++;
                    $delta = 0;
                    $isFirst = false;
                }
            }
            $delta++;
            $currentCode++;
        }

        return $encoded;
    }

    /**
     * Encoding a certain digit
     * @param    int $d
     * @return string
     */
    private function encodeDigit($d): string
    {
        return chr($d + 22 + 75 * ($d < 26));
    }
}
