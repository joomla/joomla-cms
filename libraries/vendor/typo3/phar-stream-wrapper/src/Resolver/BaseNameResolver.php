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
use TYPO3\PharStreamWrapper\Phar\Reader;
use TYPO3\PharStreamWrapper\Resolvable;

class BaseNameResolver implements Resolvable
{
    const RESOLVE_REALPATH = 1;
    const RESOLVE_ALIAS = 2;

    /**
     * @var string[]
     */
    private $aliasMap = array();

    /**
     * @param string $path
     * @param int|null $flags
     * @return null|string
     */
    public function resolveBaseName($path, $flags = null)
    {
        $hasPharPrefix = Helper::hasPharPrefix($path);
        if ($flags === null) {
            $flags = static::RESOLVE_REALPATH | static::RESOLVE_ALIAS;
        }

        if ($hasPharPrefix && $flags & static::RESOLVE_ALIAS) {
            $baseNameFromAliasMap = $this->resolveBaseNameFromAliasMap($path);
            if ($baseNameFromAliasMap !== null) {
                return $baseNameFromAliasMap;
            }
        }

        $baseName = Helper::determineBaseFile($path);
        if ($baseName !== null && $flags & static::RESOLVE_REALPATH) {
            $baseName = realpath($baseName);
        }
        if ($baseName !== null && $hasPharPrefix && $flags & static::RESOLVE_ALIAS) {
            $this->learnAlias($baseName);
        }

        return $baseName;
    }

    /**
     * @param string $path
     * @return null|string
     */
    private function resolveBaseNameFromAliasMap($path)
    {
        $normalizedBaseName = Helper::normalizePath($path);
        $possibleAlias = strstr($normalizedBaseName, '/', true);
        return $this->getAlias($possibleAlias ?: '');
    }


    /**
     * @param string $basePath
     */
    private function learnAlias($basePath)
    {
        $reader = new Reader($basePath);
        $alias = $reader->resolveContainer()->getAlias();
        if ($alias !== '' && $alias !== $basePath) {
            $this->setAlias($alias, $basePath);
        }
    }

    /**
     * @param string $alias
     * @return null|string
     */
    private function getAlias($alias)
    {
        if (!isset($this->aliasMap[$alias])) {
            return null;
        }
        return $this->aliasMap[$alias];
    }

    /**
     * Updates alias map for current Phar archive.
     *
     * Since Phar aliases are intended to be used only inside Phar archives,
     * there is no further check for duplicates in our alias map.
     *
     * @param string $alias
     * @param string $basePath
     * @see https://secure.php.net/manual/en/phar.setalias.php
     * @see https://secure.php.net/manual/en/phar.mapphar.php
     */
    private function setAlias($alias, $basePath)
    {
        $this->aliasMap[$alias] = $basePath;
    }
}
