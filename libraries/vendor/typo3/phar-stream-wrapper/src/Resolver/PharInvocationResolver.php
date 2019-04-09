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

use TYPO3\PharStreamWrapper\Helper;
use TYPO3\PharStreamWrapper\Manager;
use TYPO3\PharStreamWrapper\Phar\Reader;
use TYPO3\PharStreamWrapper\Resolvable;

class PharInvocationResolver implements Resolvable
{
    const RESOLVE_REALPATH = 1;
    const RESOLVE_ALIAS = 2;
    const ASSERT_INTERNAL_INVOCATION = 32;

    /**
     * @var string[]
     */
    private $invocationFunctionNames = array(
        'include',
        'include_once',
        'require',
        'require_once'
    );


    /**
     * Resolves PharInvocation value object (baseName and optional alias).
     *
     * Phar aliases are intended to be used only inside Phar archives, however
     * PharStreamWrapper needs this information exposed outside of Phar as well
     * It is possible that same alias is used for different $baseName values.
     * That's why PharInvocationCollection behaves like a stack when resolving
     * base-name for a given alias. On the other hand it is not possible that
     * one $baseName is referring to multiple aliases.
     * @see https://secure.php.net/manual/en/phar.setalias.php
     * @see https://secure.php.net/manual/en/phar.mapphar.php
     *
     * @param string $path
     * @param int|null $flags
     * @return null|PharInvocation
     */
    public function resolve($path, $flags = null)
    {
        $hasPharPrefix = Helper::hasPharPrefix($path);
        if ($flags === null) {
            $flags = static::RESOLVE_REALPATH | static::RESOLVE_ALIAS | static::ASSERT_INTERNAL_INVOCATION;
        }

        if ($hasPharPrefix && $flags & static::RESOLVE_ALIAS) {
            $invocation = $this->findByAlias($path);
            if ($invocation !== null && $this->assertInternalInvocation($invocation, $flags)) {
                return $invocation;
            } elseif ($invocation !== null) {
                return null;
            }
        }

        $baseName = Helper::determineBaseFile($path);
        if ($baseName === null) {
            return null;
        }

        if ($flags & static::RESOLVE_REALPATH) {
            $baseName = realpath($baseName);
        }
        if ($flags & static::RESOLVE_ALIAS) {
            $reader = new Reader($baseName);
            $alias = $reader->resolveContainer()->getAlias();
        } else {
            $alias = '';
        }

        return new PharInvocation($baseName, $alias);
    }

    /**
     * @param string $path
     * @return null|PharInvocation
     */
    private function findByAlias($path)
    {
        $normalizedPath = Helper::normalizePath($path);
        $possibleAlias = strstr($normalizedPath, '/', true);
        if (empty($possibleAlias)) {
            return null;
        }
        return Manager::instance()->getCollection()->findByCallback(
            function (PharInvocation $candidate) use ($possibleAlias) {
                return $candidate->getAlias() === $possibleAlias;
            },
            true
        );
    }

    /**
     * @param PharInvocation $invocation
     * @param int $flags
     * @return bool
     * @experimental
     */
    private function assertInternalInvocation(PharInvocation $invocation, $flags)
    {
        if (!($flags & static::ASSERT_INTERNAL_INVOCATION)) {
            return true;
        }

        $trace = debug_backtrace(0);
        $firstIndex = count($trace) - 1;
        // initial invocation, most probably a CLI tool
        if (isset($trace[$firstIndex]['file']) && $trace[$firstIndex]['file'] === $invocation->getBaseName()) {
            return true;
        }
        // otherwise search for include/require invocations
        foreach ($trace as $item) {
            if (!isset($item['function']) || !isset($item['args'][0])) {
                continue;
            }
            if ($item['args'][0] === $invocation->getBaseName()
                && in_array($item['function'], $this->invocationFunctionNames, true)
            ) {
                return true;
            }
        }

        return false;
    }
}
