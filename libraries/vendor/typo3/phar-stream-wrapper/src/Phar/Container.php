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

class Container
{
    /**
     * @var Stub
     */
    private $stub;

    /**
     * @var Manifest
     */
    private $manifest;

    /**
     * @param Stub $stub
     * @param Manifest $manifest
     */
    public function __construct(Stub $stub, Manifest $manifest)
    {
        $this->stub = $stub;
        $this->manifest = $manifest;
    }

    /**
     * @return Stub
     */
    public function getStub(): Stub
    {
        return $this->stub;
    }

    /**
     * @return Manifest
     */
    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->manifest->getAlias() ?: $this->stub->getMappedAlias();
    }
}
