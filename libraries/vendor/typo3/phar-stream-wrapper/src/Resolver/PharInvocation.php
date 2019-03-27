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

use TYPO3\PharStreamWrapper\Exception;

class PharInvocation
{
    /**
     * @var string
     */
    private $baseName;

    /**
     * @var string
     */
    private $alias;

    /**
     * @param string $baseName
     * @param string $alias
     */
    public function __construct($baseName, $alias = '')
    {
        if ($baseName === '') {
            throw new Exception(
                'Base-name cannot be empty',
                1551283689
            );
        }
        $this->baseName = $baseName;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->baseName;
    }

    /**
     * @return string
     */
    public function getBaseName()
    {
        return $this->baseName;
    }

    /**
     * @return null|string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param PharInvocation $other
     * @return bool
     */
    public function equals(PharInvocation $other)
    {
        return $other->baseName === $this->baseName
            && $other->alias === $this->alias;
    }
}