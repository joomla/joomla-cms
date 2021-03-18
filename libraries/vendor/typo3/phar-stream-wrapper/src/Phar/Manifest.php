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

class Manifest
{
    /**
     * @param string $content
     * @return self
     * @see http://php.net/manual/en/phar.fileformat.phar.php
     */
    public static function fromContent(string $content): self
    {
        $target = new static();
        $target->manifestLength = Reader::resolveFourByteLittleEndian($content, 0);
        $target->amountOfFiles = Reader::resolveFourByteLittleEndian($content, 4);
        $target->flags = Reader::resolveFourByteLittleEndian($content, 10);
        $target->aliasLength = Reader::resolveFourByteLittleEndian($content, 14);
        $target->alias = substr($content, 18, $target->aliasLength);
        $target->metaDataLength = Reader::resolveFourByteLittleEndian($content, 18 + $target->aliasLength);
        $target->metaData = substr($content, 22 + $target->aliasLength, $target->metaDataLength);

        $apiVersionNibbles = Reader::resolveTwoByteBigEndian($content, 8);
        $target->apiVersion = implode('.', [
            ($apiVersionNibbles & 0xf000) >> 12,
            ($apiVersionNibbles & 0x0f00) >> 8,
            ($apiVersionNibbles & 0x00f0) >> 4,
        ]);

        return $target;
    }

    /**
     * @var int
     */
    private $manifestLength;

    /**
     * @var int
     */
    private $amountOfFiles;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var int
     */
    private $aliasLength;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var int
     */
    private $metaDataLength;

    /**
     * @var string
     */
    private $metaData;

    /**
     * Avoid direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * @return int
     */
    public function getManifestLength(): int
    {
        return $this->manifestLength;
    }

    /**
     * @return int
     */
    public function getAmountOfFiles(): int
    {
        return $this->amountOfFiles;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @return int
     */
    public function getAliasLength(): int
    {
        return $this->aliasLength;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return int
     */
    public function getMetaDataLength(): int
    {
        return $this->metaDataLength;
    }

    /**
     * @return string
     */
    public function getMetaData(): string
    {
        return $this->metaData;
    }

    /**
     * @return mixed|null
     */
    public function deserializeMetaData()
    {
        if (empty($this->metaData)) {
            return null;
        }

        $result = unserialize($this->metaData, ['allowed_classes' => false]);

        $serialized = json_encode($result);
        if (strpos($serialized, '__PHP_Incomplete_Class_Name') !== false) {
            throw new DeserializationException(
                'Meta-data contains serialized object',
                1539623382
            );
        }

        return $result;
    }
}
