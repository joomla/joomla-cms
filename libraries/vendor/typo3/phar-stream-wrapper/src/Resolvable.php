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

interface Resolvable
{
    /**
     * @param string $path
     * @param null|int $flags
     * @return null|string
     */
    public function resolveBaseName($path, $flags = null);
}
