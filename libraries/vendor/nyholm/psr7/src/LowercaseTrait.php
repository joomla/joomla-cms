<?php

declare(strict_types=1);

namespace Nyholm\Psr7;

/**
 * Trait implementing a locale-independent lowercasing logic.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal should not be used outside of Nyholm/Psr7 as it does not fall under our BC promise
 */
trait LowercaseTrait
{
    private static function lowercase(string $value): string
    {
        return \strtr($value, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }
}
