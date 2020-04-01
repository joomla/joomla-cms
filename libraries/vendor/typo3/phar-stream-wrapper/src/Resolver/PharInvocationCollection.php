<?php
namespace TYPO3\PharStreamWrapper\Resolver;

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under the terms
 * of the MIT License (MIT). For the full copyright and license information,
 * please read the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\PharStreamWrapper\Collectable;

class PharInvocationCollection implements Collectable
{
    const UNIQUE_INVOCATION = 1;
    const UNIQUE_BASE_NAME = 2;
    const DUPLICATE_ALIAS_WARNING = 32;

    /**
     * @var PharInvocation[]
     */
    private $invocations = array();

    /**
     * @param PharInvocation $invocation
     * @return bool
     */
    public function has(PharInvocation $invocation)
    {
        return in_array($invocation, $this->invocations, true);
    }

    /**
     * @param PharInvocation $invocation
     * @param null|int $flags
     * @return bool
     */
    public function collect(PharInvocation $invocation, $flags = null)
    {
        if ($flags === null) {
            $flags = static::UNIQUE_INVOCATION | static::DUPLICATE_ALIAS_WARNING;
        }
        if ($invocation->getBaseName() === ''
            || $invocation->getAlias() === ''
            || !$this->assertUniqueBaseName($invocation, $flags)
            || !$this->assertUniqueInvocation($invocation, $flags)
        ) {
            return false;
        }
        if ($flags & static::DUPLICATE_ALIAS_WARNING) {
            $this->triggerDuplicateAliasWarning($invocation);
        }

        $this->invocations[] = $invocation;
        return true;
    }

    /**
     * @param callable $callback
     * @param bool $reverse
     * @return null|PharInvocation
     */
    public function findByCallback($callback, $reverse = false)
    {
        foreach ($this->getInvocations($reverse) as $invocation) {
            if (call_user_func($callback, $invocation) === true) {
                return $invocation;
            }
        }
        return null;
    }

    /**
     * Asserts that base-name is unique. This disallows having multiple invocations for
     * same base-name but having different alias names.
     *
     * @param PharInvocation $invocation
     * @param int $flags
     * @return bool
     */
    private function assertUniqueBaseName(PharInvocation $invocation, $flags)
    {
        if (!($flags & static::UNIQUE_BASE_NAME)) {
            return true;
        }
        return $this->findByCallback(
                function (PharInvocation $candidate) use ($invocation) {
                    return $candidate->getBaseName() === $invocation->getBaseName();
                }
            ) === null;
    }

    /**
     * Asserts that combination of base-name and alias is unique. This allows having multiple
     * invocations for same base-name but having different alias names (for whatever reason).
     *
     * @param PharInvocation $invocation
     * @param int $flags
     * @return bool
     */
    private function assertUniqueInvocation(PharInvocation $invocation, $flags)
    {
        if (!($flags & static::UNIQUE_INVOCATION)) {
            return true;
        }
        return $this->findByCallback(
                function (PharInvocation $candidate) use ($invocation) {
                    return $candidate->equals($invocation);
                }
            ) === null;
    }

    /**
     * Triggers warning for invocations with same alias and same confirmation state.
     *
     * @param PharInvocation $invocation
     * @see \TYPO3\PharStreamWrapper\PharStreamWrapper::collectInvocation()
     */
    private function triggerDuplicateAliasWarning(PharInvocation $invocation)
    {
        $sameAliasInvocation = $this->findByCallback(
            function (PharInvocation $candidate) use ($invocation) {
                return $candidate->isConfirmed() === $invocation->isConfirmed()
                    && $candidate->getAlias() === $invocation->getAlias();
            },
            true
        );
        if ($sameAliasInvocation === null) {
            return;
        }
        trigger_error(
            sprintf(
                'Alias %s cannot be used by %s, already used by %s',
                $invocation->getAlias(),
                $invocation->getBaseName(),
                $sameAliasInvocation->getBaseName()
            ),
            E_USER_WARNING
        );
    }

    /**
     * @param bool $reverse
     * @return PharInvocation[]
     */
    private function getInvocations($reverse = false)
    {
        if ($reverse) {
            return array_reverse($this->invocations);
        }
        return $this->invocations;
    }
}