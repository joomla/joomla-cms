<?php
declare(strict_types=1);
namespace TYPO3\PharStreamWrapper\Interceptor;

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under the terms
 * of the MIT License (MIT). For the full copyright and license information,
 * please read the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\PharStreamWrapper\Assertable;
use TYPO3\PharStreamWrapper\Exception;
use TYPO3\PharStreamWrapper\Manager;

class PharExtensionInterceptor implements Assertable
{
    /**
     * Determines whether the base file name has a ".phar" suffix.
     *
     * @param string $path
     * @param string $command
     * @return bool
     * @throws Exception
     */
    public function assert(string $path, string $command): bool
    {
        if ($this->baseFileContainsPharExtension($path)) {
            return true;
        }
        throw new Exception(
            sprintf(
                'Unexpected file extension in "%s"',
                $path
            ),
            1535198703
        );
    }

    /**
     * @param string $path
     * @return bool
     */
    private function baseFileContainsPharExtension(string $path): bool
    {
        $invocation = Manager::instance()->resolve($path);
        if ($invocation === null) {
            return false;
        }
        $fileExtension = pathinfo($invocation->getBaseName(), PATHINFO_EXTENSION);
        return strtolower($fileExtension) === 'phar';
    }
}
