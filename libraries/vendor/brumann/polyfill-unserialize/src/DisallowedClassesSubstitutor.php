<?php
namespace Brumann\Polyfill;

/**
 * Worker implementation for identifying and skipping false-positives
 * not to be substituted - like nested serializations in string literals.
 *
 * @internal This class should only be used by \Brumann\Polyfill\Unserialize
 */
final class DisallowedClassesSubstitutor
{
    const PATTERN_STRING = '#s:(\d+):(")#';
    const PATTERN_OBJECT = '#(^|;)O:\d+:"([^"]*)":(\d+):\{#';

    /**
     * @var string
     */
    private $serialized;

    /**
     * @var string[]
     */
    private $allowedClasses;

    /**
     * Each array item consists of `[<offset-start>, <offset-end>]` and
     * marks start and end positions of items to be ignored.
     *
     * @var array[]
     */
    private $ignoreItems = array();

    /**
     * @param string $serialized
     * @param string[] $allowedClasses
     */
    public function __construct($serialized, array $allowedClasses)
    {
        $this->serialized = $serialized;
        $this->allowedClasses = $allowedClasses;

        $this->buildIgnoreItems();
        $this->substituteObjects();
    }

    /**
     * @return string
     */
    public function getSubstitutedSerialized()
    {
        return $this->serialized;
    }

    /**
     * Identifies items to be ignored - like nested serializations in string literals.
     */
    private function buildIgnoreItems()
    {
        $offset = 0;
        while (preg_match(self::PATTERN_STRING, $this->serialized, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $length = (int)$matches[1][0]; // given length in serialized data (e.g. `s:123:"` --> 123)
            $start = $matches[2][1]; // offset position of quote character
            $end = $start + $length + 1;
            $offset = $end + 1;

            // serialized string nested in outer serialized string
            if ($this->ignore($start, $end)) {
                continue;
            }

            $this->ignoreItems[] = array($start, $end);
        }
    }

    /**
     * Substitutes disallowed object class names and respects items to be ignored.
     */
    private function substituteObjects()
    {
        $offset = 0;
        while (preg_match(self::PATTERN_OBJECT, $this->serialized, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $completeMatch = (string)$matches[0][0];
            $completeLength = strlen($completeMatch);
            $start = $matches[0][1];
            $end = $start + $completeLength;
            $leftBorder = (string)$matches[1][0];
            $className = (string)$matches[2][0];
            $objectSize = (int)$matches[3][0];
            $offset = $end + 1;

            // class name is actually allowed - skip this item
            if (in_array($className, $this->allowedClasses, true)) {
                continue;
            }
            // serialized object nested in outer serialized string
            if ($this->ignore($start, $end)) {
                continue;
            }

            $incompleteItem = $this->sanitizeItem($className, $leftBorder, $objectSize);
            $incompleteItemLength = strlen($incompleteItem);
            $offset = $start + $incompleteItemLength + 1;

            $this->replace($incompleteItem, $start, $end);
            $this->shift($end, $incompleteItemLength - $completeLength);
        }
    }

    /**
     * Replaces sanitized object class names in serialized data.
     *
     * @param string $replacement Sanitized object data
     * @param int $start Start offset in serialized data
     * @param int $end End offset in serialized data
     */
    private function replace($replacement, $start, $end)
    {
        $this->serialized = substr($this->serialized, 0, $start)
            . $replacement . substr($this->serialized, $end);
    }

    /**
     * Whether given offset positions should be ignored.
     *
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function ignore($start, $end)
    {
        foreach ($this->ignoreItems as $ignoreItem) {
            if ($ignoreItem[0] <= $start && $ignoreItem[1] >= $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * Shifts offset positions of ignore items by `$size`.
     * This is necessary whenever object class names have been
     * substituted which have a different length than before.
     *
     * @param int $offset
     * @param int $size
     */
    private function shift($offset, $size)
    {
        foreach ($this->ignoreItems as &$ignoreItem) {
            // only focus on items starting after given offset
            if ($ignoreItem[0] < $offset) {
                continue;
            }
            $ignoreItem[0] += $size;
            $ignoreItem[1] += $size;
        }
    }

    /**
     * Sanitizes object class item.
     *
     * @param string $className
     * @param int $leftBorder
     * @param int $objectSize
     * @return string
     */
    private function sanitizeItem($className, $leftBorder, $objectSize)
    {
        return sprintf(
            '%sO:22:"__PHP_Incomplete_Class":%d:{s:27:"__PHP_Incomplete_Class_Name";%s',
            $leftBorder,
            $objectSize + 1, // size of object + 1 for added string
            \serialize($className)
        );
    }
}
