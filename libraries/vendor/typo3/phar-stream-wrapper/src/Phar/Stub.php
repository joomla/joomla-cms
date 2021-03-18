<?php
declare(strict_types=1);
namespace TYPO3\PharStreamWrapper\Phar;

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
 * @internal Experimental implementation of Phar archive internals
 */
class Stub
{
    /**
     * @param string $content
     * @return self
     */
    public static function fromContent(string $content): self
    {
        $target = new static();
        $target->content = $content;

        if (
            stripos($content, 'Phar::mapPhar(') !== false
            && preg_match('#Phar\:\:mapPhar\(([^)]+)\)#', $content, $matches)
        ) {
            // remove spaces, single & double quotes
            // @todo `'my' . 'alias' . '.phar'` is not evaluated here
            $target->mappedAlias = trim($matches[1], ' \'"');
        }

        return $target;
    }

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $mappedAlias = '';

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getMappedAlias(): string
    {
        return $this->mappedAlias;
    }
}
