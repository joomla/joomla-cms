<?php
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

/**
 * Helper provides low-level tools on file name resolving. However it does not
 * (and should not) maintain any runtime state information. In order to resolve
 * Phar archive paths according resolvers have to be used.
 *
 * @see \TYPO3\PharStreamWrapper\Resolvable::resolve()
 */
class Helper
{
    /*
     * Resets PHP's OPcache if enabled as work-around for issues in `include()`
     * or `require()` calls and OPcache delivering wrong results.
     *
     * @see https://bugs.php.net/bug.php?id=66569
     */
    public static function resetOpCache()
    {
        if (function_exists('opcache_reset')
            && function_exists('opcache_get_status')
        ) {
            $status = opcache_get_status();
            if (!empty($status['opcache_enabled'])) {
                opcache_reset();
            }
        }
    }

    /**
     * Determines base file that can be accessed using the regular file system.
     * For e.g. "phar:///home/user/bundle.phar/content.txt" that would result
     * into "/home/user/bundle.phar".
     *
     * @param string $path
     * @return string|null
     */
    public static function determineBaseFile($path)
    {
        $parts = explode('/', static::normalizePath($path));

        while (count($parts)) {
            $currentPath = implode('/', $parts);
            if (@is_file($currentPath) && realpath($currentPath) !== false) {
                return $currentPath;
            }
            array_pop($parts);
        }

        return null;
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function hasPharPrefix($path)
    {
        return stripos($path, 'phar://') === 0;
    }

    /**
     * @param string $path
     * @return string
     */
    public static function removePharPrefix($path)
    {
        $path = trim($path);
        if (!static::hasPharPrefix($path)) {
            return $path;
        }
        return substr($path, 7);
    }

    /**
     * Normalizes a path, removes phar:// prefix, fixes Windows directory
     * separators. Result is without trailing slash.
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        return rtrim(
            static::normalizeWindowsPath(
                static::removePharPrefix($path)
            ),
            '/'
        );
    }

    /**
     * Fixes a path for windows-backslashes and reduces double-slashes to single slashes
     *
     * @param string $path File path to process
     * @return string
     */
    public static function normalizeWindowsPath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Resolves all dots, slashes and removes spaces after or before a path...
     *
     * @param string $path Input string
     * @return string Canonical path, always without trailing slash
     */
    private static function getCanonicalPath($path)
    {
        $path = static::normalizeWindowsPath($path);

        $absolutePathPrefix = '';
        if (static::isAbsolutePath($path)) {
            if (static::isWindows() && strpos($path, ':/') === 1) {
                $absolutePathPrefix = substr($path, 0, 3);
                $path = substr($path, 3);
            } else {
                $path = ltrim($path, '/');
                $absolutePathPrefix = '/';
            }
        }

        $pathParts = explode('/', $path);
        $pathPartsLength = count($pathParts);
        for ($partCount = 0; $partCount < $pathPartsLength; $partCount++) {
            // double-slashes in path: remove element
            if ($pathParts[$partCount] === '') {
                array_splice($pathParts, $partCount, 1);
                $partCount--;
                $pathPartsLength--;
            }
            // "." in path: remove element
            if ((isset($pathParts[$partCount]) ? $pathParts[$partCount] : '') === '.') {
                array_splice($pathParts, $partCount, 1);
                $partCount--;
                $pathPartsLength--;
            }
            // ".." in path:
            if ((isset($pathParts[$partCount]) ? $pathParts[$partCount] : '') === '..') {
                if ($partCount === 0) {
                    array_splice($pathParts, $partCount, 1);
                    $partCount--;
                    $pathPartsLength--;
                } elseif ($partCount >= 1) {
                    // Rremove this and previous element
                    array_splice($pathParts, $partCount - 1, 2);
                    $partCount -= 2;
                    $pathPartsLength -= 2;
                } elseif ($absolutePathPrefix) {
                    // can't go higher than root dir
                    // simply remove this part and continue
                    array_splice($pathParts, $partCount, 1);
                    $partCount--;
                    $pathPartsLength--;
                }
            }
        }

        return $absolutePathPrefix . implode('/', $pathParts);
    }

    /**
     * Checks if the $path is absolute or relative (detecting either '/' or
     * 'x:/' as first part of string) and returns TRUE if so.
     *
     * @param string $path File path to evaluate
     * @return bool
     */
    private static function isAbsolutePath($path)
    {
        // Path starting with a / is always absolute, on every system
        // On Windows also a path starting with a drive letter is absolute: X:/
        return (isset($path[0]) ? $path[0] : null) === '/'
            || static::isWindows() && (
                strpos($path, ':/') === 1
                || strpos($path, ':\\') === 1
            );
    }

    /**
     * @return bool
     */
    private static function isWindows()
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}