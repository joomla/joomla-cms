<?php

namespace Algo26\IdnaConvert\NamePrep;

use Algo26\IdnaConvert\Exception\InvalidCharacterException;
use Algo26\IdnaConvert\Exception\InvalidIdnVersionException;

class NamePrep implements NamePrepInterface
{
    const sBase = 0xAC00;
    const lBase = 0x1100;
    const vBase = 0x1161;
    const tBase = 0x11A7;
    const lCount = 19;
    const vCount = 21;
    const tCount = 28;
    const nCount = 588;   // vCount * tCount
    const sCount = 11172; // lCount * tCount * vCount
    const sLast = self::sBase + self::lCount * self::vCount * self::tCount;

    /** @var NamePrepDataInterface */
    private $namePrepData;

    /**
     * @param string|null $idnVersion
     *
     * @throws InvalidIdnVersionException
     */
    public function __construct(?string $idnVersion = null)
    {
        if ($idnVersion === null || $idnVersion == 2008) {
            $this->namePrepData = new NamePrepData2008();

            return;
        }

        if ($idnVersion == 2003) {
            $this->namePrepData = new NamePrepData2003();

            return;
        }

        throw new InvalidIdnVersionException('IDN version must bei either 2003 or 2008');
    }

    /**
     * @param array $inputArray
     *
     * @return array
     * @throws InvalidCharacterException
     */
    public function do(array $inputArray): array
    {
        $outputArray = $this->applyCharacterMaps($inputArray);
        $outputArray = $this->hangulCompose($outputArray);
        $outputArray = $this->combineCodePoints($outputArray);

        return $outputArray;
    }

    /**
     * @param array $inputArray
     *
     * @return array
     * @throws InvalidCharacterException
     */
    private function applyCharacterMaps(array $inputArray): array
    {
        $outputArray = [];
        foreach ($inputArray as $codePoint) {
            // Map to nothing == skip that code point
            if (in_array($codePoint, $this->namePrepData->mapToNothing)) {
                continue;
            }
            // Try to find prohibited input
            if (in_array($codePoint, $this->namePrepData->prohibit)
                || in_array($codePoint, $this->namePrepData->generalProhibited)
            ) {
                throw new InvalidCharacterException(sprintf('Prohibited input U+%08X', $codePoint), 101);
            }
            foreach ($this->namePrepData->prohibitRanges as $range) {
                if ($range[0] <= $codePoint && $codePoint <= $range[1]) {
                    throw new InvalidCharacterException(sprintf('Prohibited input U+%08X', $codePoint), 102);
                }
            }

            if (0xAC00 <= $codePoint && $codePoint <= 0xD7AF) {
                // Hangul syllable decomposition
                foreach ($this->hangulDecompose($codePoint) as $decomposed) {
                    $outputArray[] = (int) $decomposed;
                }
            } elseif (isset($this->namePrepData->replaceMaps[$codePoint])) {
                foreach ($this->applyCanonicalOrdering($this->namePrepData->replaceMaps[$codePoint]) as $reordered) {
                    $outputArray[] = (int) $reordered;
                }
            } else {
                $outputArray[] = (int) $codePoint;
            }
        }

        return $outputArray;
    }

    private function combineCodePoints(array $codePoints): array
    {
        $previousClass = 0;
        $previousStarter = 0;
        $outputLength = count($codePoints);
        for ($outerIndex = 0; $outerIndex < $outputLength; ++$outerIndex) {
            $combiningClass = $this->getCombiningClass($codePoints[$outerIndex]);
            if (
                ($previousClass === 0 || $previousClass > $combiningClass)
                && $combiningClass !== 0
            ) {
                // Try to match
                $sequenceLength = $outerIndex - $previousStarter;
                $combined = $this->combine(array_slice($codePoints, $previousStarter, $sequenceLength));
                // On match: Replace the last starter with the composed character and remove
                // the now redundant non-starter(s)
                if (false !== $combined) {
                    $codePoints[$previousStarter] = $combined;
                    if ($sequenceLength > 1) {
                        for ($innerIndex = $outerIndex + 1; $innerIndex < $outputLength; ++$innerIndex) {
                            $codePoints[$innerIndex - 1] = $codePoints[$innerIndex];
                        }
                        unset($codePoints[$outputLength]);
                    }
                    // Rewind the for loop by one, since there can be more possible compositions
                    $outerIndex--;
                    $outputLength--;
                    $previousClass = 0;
                    if ($outerIndex !== $previousStarter) {
                        $this->getCombiningClass($codePoints[$outerIndex - 1]);
                    }

                    continue;
                }
            }

            if ($combiningClass === 0) {
                $previousStarter = $outerIndex;
            }
            $previousClass = $combiningClass;
        }

        return $codePoints;
    }

    /**
     * Decomposes a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     * @param    integer  32bit UCS4 code point
     * @return   array    Either Hangul Syllable decomposed or original 32bit value as one value array
     */
    private function hangulDecompose(int $codePoint): array
    {
        $sIndex = (int) $codePoint - self::sBase;
        if ($sIndex < 0 || $sIndex >= self::sCount) {
            return [$codePoint];
        }

        $result = [
            (int) self::lBase + $sIndex / self::nCount,
            (int) self::vBase + ($sIndex % self::nCount) / self::tCount,
        ];
        $T = intval(self::tBase + $sIndex % self::tCount);
        if ($T != self::tBase) {
            $result[] = $T;
        }

        return $result;
    }

    /**
     * Compose a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     *
     * @param  array $input   Decomposed UCS4 sequence
     * @return array UCS4 sequence with syllables composed
     */
    private function hangulCompose(array $input): array
    {
        $inputLength = count($input);
        if ($inputLength === 0) {
            return [];
        }

        $previousCharCode = (int) $input[0];

        // copy first codepoint from input to output
        $result = [
            $previousCharCode,
        ];

        for ($i = 1; $i < $inputLength; ++$i) {
            $charCode = (int) $input[$i];
            $sIndex = $previousCharCode - self::sBase;
            $lIndex = $previousCharCode - self::lBase;
            $vIndex = $charCode - self::vBase;
            $tIndex = $charCode - self::tBase;

            // Find out, whether two current characters are LV and T
            if (0 <= $sIndex
                && $sIndex < self::sCount
                && ($sIndex % self::tCount == 0)
                && 0 <= $tIndex
                && $tIndex <= self::tCount
            ) {
                // create syllable of form LVT
                $previousCharCode += $tIndex;
                $result[(count($result) - 1)] = $previousCharCode; // reset last

                continue; // discard char
            }

            // Find out, whether two current characters form L and V
            if (0 <= $lIndex
                && $lIndex < self::lCount
                && 0 <= $vIndex
                && $vIndex < self::vCount
            ) {
                // create syllable of form LV
                $previousCharCode = (int) self::sBase + ($lIndex * self::vCount + $vIndex) * self::tCount;
                $result[(count($result) - 1)] = $previousCharCode; // reset last

                continue; // discard char
            }
            // if neither case was true, just add the character
            $previousCharCode = $charCode;
            $result[] = $charCode;
        }

        return $result;
    }

    /**
     * Returns the combining class of a certain wide char
     * @param integer  $char  Wide char to check (32bit integer)
     * @return integer Combining class if found, else 0
     */
    private function getCombiningClass(int $char): int
    {
        return isset($this->namePrepData->normalizeCombiningClasses[$char])
            ? $this->namePrepData->normalizeCombiningClasses[$char]
            : 0;
    }

    /**
     * Applies the canonical ordering of a decomposed UCS4 sequence
     * @param array  $input Decomposed UCS4 sequence
     * @return array Ordered USC4 sequence
     */
    private function applyCanonicalOrdering(array $input): array
    {
        $needsSwapping = true;
        $inputLength = count($input);
        while ($needsSwapping) {
            $needsSwapping = false;
            $previousClass = $this->getCombiningClass(intval($input[0]));
            for ($outerIndex = 0; $outerIndex < $inputLength - 1; ++$outerIndex) {
                $nextClass = $this->getCombiningClass(intval($input[$outerIndex + 1]));
                if ($nextClass !== 0 && $previousClass > $nextClass) {
                    // Move item leftward until it fits
                    for ($innerIndex = $outerIndex + 1; $innerIndex > 0; --$innerIndex) {
                        if ($this->getCombiningClass(intval($input[$innerIndex - 1])) <= $nextClass) {
                            break;
                        }
                        $charToMove = intval($input[$innerIndex]);
                        $input[$innerIndex] = intval($input[$innerIndex - 1]);
                        $input[$innerIndex - 1] = $charToMove;
                        $needsSwapping = true;
                    }
                    // Reentering the loop looking at the old character again
                    $nextClass = $previousClass;
                }
                $previousClass = $nextClass;
            }
        }

        return $input;
    }

    /**
     * Do composition of a sequence of starter and non-starter
     * @param   array $input UCS4 Decomposed sequence
     * @return  array|false  Ordered USC4 sequence
     */
    private function combine(array $input)
    {
        $inputLength = count($input);
        if (0 === $inputLength) {
            return false;
        }

        foreach ($this->namePrepData->replaceMaps as $namePrepSource => $namePrepTarget) {
            if ($namePrepTarget[0] !== $input[0]) {
                continue;
            }
            if (count($namePrepTarget) !== $inputLength) {
                continue;
            }
            $hit = false;
            foreach ($input as $k2 => $v2) {
                if ($v2 === $namePrepTarget[$k2]) {
                    $hit = true;
                } else {
                    $hit = false;
                    break;
                }
            }
            if ($hit) {
                return $namePrepSource;
            }
        }

        return false;
    }
}
