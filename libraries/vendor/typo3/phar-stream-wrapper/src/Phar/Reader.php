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

class Reader
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileType;

    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        if (strpos($fileName, '://') !== false) {
            throw new ReaderException(
                'File name must not contain stream prefix',
                1539623708
            );
        }

        $this->fileName = $fileName;
        $this->fileType = $this->determineFileType();
    }

    /**
     * @return Container
     */
    public function resolveContainer()
    {
        $data = $this->extractData($this->resolveStream() . $this->fileName);

        if ($data['stubContent'] === null) {
            throw new ReaderException(
                'Cannot resolve stub',
                1547807881
            );
        }
        if ($data['manifestContent'] === null || $data['manifestLength'] === null) {
            throw new ReaderException(
                'Cannot resolve manifest',
                1547807882
            );
        }
        if (strlen($data['manifestContent']) < $data['manifestLength']) {
            throw new ReaderException(
                sprintf(
                    'Exected manifest length %d, got %d',
                    strlen($data['manifestContent']),
                    $data['manifestLength']
                ),
                1547807883
            );
        }

        return new Container(
            Stub::fromContent($data['stubContent']),
            Manifest::fromContent($data['manifestContent'])
        );
    }

    /**
     * @param string $fileName e.g. '/path/file.phar' or 'compress.zlib:///path/file.phar'
     * @return array
     */
    private function extractData($fileName)
    {
        $stubContent = null;
        $manifestContent = null;
        $manifestLength = null;

        $resource = fopen($fileName, 'r');
        if (!is_resource($resource)) {
            throw new ReaderException(
                sprintf('Resource %s could not be opened', $fileName),
                1547902055
            );
        }

        while (!feof($resource)) {
            $line = fgets($resource);
            // stop reading file when manifest can be extracted
            if ($manifestLength !== null && $manifestContent !== null && strlen($manifestContent) >= $manifestLength) {
                break;
            }

            $stubPosition = strpos($line, '<?php');
            $manifestPosition = strpos($line, '__HALT_COMPILER()');

            // line contains both, start of (empty) stub and start of manifest
            if ($stubContent === null && $stubPosition !== false
                && $manifestContent === null && $manifestPosition !== false) {
                $stubContent = substr($line, $stubPosition, $manifestPosition - $stubPosition - 1);
                $manifestContent = preg_replace('#^.*__HALT_COMPILER\(\)[^>]*\?>(\r|\n)*#', '', $line);
                $manifestLength = $this->resolveManifestLength($manifestContent);
            // line contains start of stub
            } elseif ($stubContent === null && $stubPosition !== false) {
                $stubContent = substr($line, $stubPosition);
            // line contains start of manifest
            } elseif ($manifestContent === null && $manifestPosition !== false) {
                $manifestContent = preg_replace('#^.*__HALT_COMPILER\(\)[^>]*\?>(\r|\n)*#', '', $line);
                $manifestLength = $this->resolveManifestLength($manifestContent);
            // manifest has been started (thus is cannot be stub anymore), add content
            } elseif ($manifestContent !== null) {
                $manifestContent .= $line;
                $manifestLength = $this->resolveManifestLength($manifestContent);
            // stub has been started (thus cannot be manifest here, yet), add content
            } elseif ($stubContent !== null) {
                $stubContent .= $line;
            }
        }
        fclose($resource);

        return array(
            'stubContent' => $stubContent,
            'manifestContent' => $manifestContent,
            'manifestLength' => $manifestLength,
        );
    }

    /**
     * Resolves stream in order to handle compressed Phar archives.
     *
     * @return string
     */
    private function resolveStream()
    {
        if ($this->fileType === 'application/x-gzip') {
            return 'compress.zlib://';
        } elseif ($this->fileType === 'application/x-bzip2') {
            return 'compress.bzip2://';
        }
        return '';
    }

    /**
     * @return string
     */
    private function determineFileType()
    {
        $fileInfo = new \finfo();
        return $fileInfo->file($this->fileName, FILEINFO_MIME_TYPE);
    }

    /**
     * @param string $content
     * @return int|null
     */
    private function resolveManifestLength($content)
    {
        if (strlen($content) < 4) {
            return null;
        }
        return static::resolveFourByteLittleEndian($content, 0);
    }

    /**
     * @param string $content
     * @param int $start
     * @return int
     */
    public static function resolveFourByteLittleEndian($content, $start)
    {
        $payload = substr($content, $start, 4);
        if (!is_string($payload)) {
            throw new ReaderException(
                sprintf('Cannot resolve value at offset %d', $start),
                1539614260
            );
        }

        $value = unpack('V', $payload);
        if (!isset($value[1])) {
            throw new ReaderException(
                sprintf('Cannot resolve value at offset %d', $start),
                1539614261
            );
        }
        return $value[1];
    }

    /**
     * @param string $content
     * @param int $start
     * @return int
     */
    public static function resolveTwoByteBigEndian($content, $start)
    {
        $payload = substr($content, $start, 2);
        if (!is_string($payload)) {
            throw new ReaderException(
                sprintf('Cannot resolve value at offset %d', $start),
                1539614263
            );
        }

        $value = unpack('n', $payload);
        if (!isset($value[1])) {
            throw new ReaderException(
                sprintf('Cannot resolve value at offset %d', $start),
                1539614264
            );
        }
        return $value[1];
    }
}
