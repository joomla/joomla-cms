<?php
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

use Brumann\Polyfill\Unserialize;

class Manifest
{
    /**
     * @param string $content
     * @return self
     * @see http://php.net/manual/en/phar.fileformat.phar.php
     */
    public static function fromContent($content)
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
        $target->apiVersion = implode('.', array(
            ($apiVersionNibbles & 0xf000) >> 12,
            ($apiVersionNibbles & 0x0f00) >> 8,
            ($apiVersionNibbles & 0x00f0) >> 4,
        ));

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
    public function getManifestLength()
    {
        return $this->manifestLength;
    }

    /**
     * @return int
     */
    public function getAmountOfFiles()
    {
        return $this->amountOfFiles;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @return int
     */
    public function getAliasLength()
    {
        return $this->aliasLength;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return int
     */
    public function getMetaDataLength()
    {
        return $this->metaDataLength;
    }

    /**
     * @return string
     */
    public function getMetaData()
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

        $result = Unserialize::unserialize($this->metaData, array('allowed_classes' => false));

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
