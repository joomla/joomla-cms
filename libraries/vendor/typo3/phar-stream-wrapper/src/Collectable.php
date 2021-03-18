<?php
declare(strict_types=1);
namespace TYPO3\PharStreamWrapper;

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under the terms
 * of the MIT License (MIT). For the full copyright and license information,
 * please read the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\PharStreamWrapper\Resolver\PharInvocation;

interface Collectable
{
    /**
     * @param PharInvocation $invocation
     * @return bool
     */
    public function has(PharInvocation $invocation): bool;

    /**
     * @param PharInvocation $invocation
     * @param int|null $flags
     * @return bool
     */
    public function collect(PharInvocation $invocation, int $flags = null): bool;

    /**
     * @param callable $callback
     * @param bool $reverse
     * @return null|PharInvocation
     */
    public function findByCallback(callable $callback, $reverse = false);
}
