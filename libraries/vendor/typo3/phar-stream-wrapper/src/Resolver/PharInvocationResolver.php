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
     * Contains resolved base names in order to reduce file IO.
     *
     * @var string[]
     */
    private $baseNames = array();

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
            if ($invocation !== null) {
                return $invocation;
            }
        }

        $baseName = $this->resolveBaseName($path, $flags);
        if ($baseName === null) {
            return null;
        }

        if ($flags & static::RESOLVE_REALPATH) {
            $baseName = $this->baseNames[$baseName];
        }

        return $this->retrieveInvocation($baseName, $flags);
    }

    /**
     * Retrieves PharInvocation, either existing in collection or created on demand
     * with resolving a potential alias name used in the according Phar archive.
     *
     * @param string $baseName
     * @param int $flags
     * @return PharInvocation
     */
    private function retrieveInvocation($baseName, $flags)
    {
        $invocation = $this->findByBaseName($baseName);
        if ($invocation !== null) {
            return $invocation;
        }

        if ($flags & static::RESOLVE_ALIAS) {
            $reader = new Reader($baseName);
            $alias = $reader->resolveContainer()->getAlias();
        } else {
            $alias = '';
        }
        // add unconfirmed(!) new invocation to collection
        $invocation = new PharInvocation($baseName, $alias);
        Manager::instance()->getCollection()->collect($invocation);
        return $invocation;
    }

    /**
     * @param string $path
     * @param int $flags
     * @return null|string
     */
    private function resolveBaseName($path, $flags)
    {
        $baseName = $this->findInBaseNames($path);
        if ($baseName !== null) {
            return $baseName;
        }

        $baseName = Helper::determineBaseFile($path);
        if ($baseName !== null) {
            $this->addBaseName($baseName);
            return $baseName;
        }

        $possibleAlias = $this->resolvePossibleAlias($path);
        if (!($flags & static::RESOLVE_ALIAS) || $possibleAlias === null) {
            return null;
        }

        $trace = debug_backtrace();
        foreach ($trace as $item) {
            if (!isset($item['function']) || !isset($item['args'][0])
                || !in_array($item['function'], $this->invocationFunctionNames, true)) {
                continue;
            }
            $currentPath = $item['args'][0];
            if (Helper::hasPharPrefix($currentPath)) {
                continue;
            }
            $currentBaseName = Helper::determineBaseFile($currentPath);
            if ($currentBaseName === null) {
                continue;
            }
            // ensure the possible alias name (how we have been called initially) matches
            // the resolved alias name that was retrieved by the current possible base name
            $reader = new Reader($currentBaseName);
            $currentAlias = $reader->resolveContainer()->getAlias();
            if ($currentAlias !== $possibleAlias) {
                continue;
            }
            $this->addBaseName($currentBaseName);
            return $currentBaseName;
        }

        return null;
    }

    /**
     * @param string $path
     * @return null|string
     */
    private function resolvePossibleAlias($path)
    {
        $normalizedPath = Helper::normalizePath($path);
        return strstr($normalizedPath, '/', true) ?: null;
    }

    /**
     * @param string $baseName
     * @return null|PharInvocation
     */
    private function findByBaseName($baseName)
    {
        return Manager::instance()->getCollection()->findByCallback(
            function (PharInvocation $candidate) use ($baseName) {
                return $candidate->getBaseName() === $baseName;
            },
            true
        );
    }

    /**
     * @param string $path
     * @return null|string
     */
    private function findInBaseNames($path)
    {
        // return directly if the resolved base name was submitted
        if (in_array($path, $this->baseNames, true)) {
            return $path;
        }

        $parts = explode('/', Helper::normalizePath($path));

        while (count($parts)) {
            $currentPath = implode('/', $parts);
            if (isset($this->baseNames[$currentPath])) {
                return $currentPath;
            }
            array_pop($parts);
        }

        return null;
    }

    /**
     * @param string $baseName
     */
    private function addBaseName($baseName)
    {
        if (isset($this->baseNames[$baseName])) {
            return;
        }
        $this->baseNames[$baseName] = realpath($baseName);
    }

    /**
     * Finds confirmed(!) invocations by alias.
     *
     * @param string $path
     * @return null|PharInvocation
     * @see \TYPO3\PharStreamWrapper\PharStreamWrapper::collectInvocation()
     */
    private function findByAlias($path)
    {
        $possibleAlias = $this->resolvePossibleAlias($path);
        if ($possibleAlias === null) {
            return null;
        }
        return Manager::instance()->getCollection()->findByCallback(
            function (PharInvocation $candidate) use ($possibleAlias) {
                return $candidate->isConfirmed() && $candidate->getAlias() === $possibleAlias;
            },
            true
        );
    }
}
