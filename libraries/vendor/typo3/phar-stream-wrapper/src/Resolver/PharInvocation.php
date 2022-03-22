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
     * @var bool
     * @see \TYPO3\PharStreamWrapper\PharStreamWrapper::collectInvocation()
     */
    private $confirmed = false;

    /**
     * Arbitrary variables to be used by interceptors as registry
     * (e.g. in order to avoid duplicate processing and assertions)
     *
     * @var array
     */
    private $variables;

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
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    public function confirm()
    {
        $this->confirmed = true;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getVariable($name)
    {
        if (!isset($this->variables[$name])) {
            return null;
        }
        return $this->variables[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setVariable($name, $value)
    {
        $this->variables[$name] = $value;
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