<?php

namespace Tuf\Metadata;

/**
 * Base class for metadata objects that store information about other metadata files.
 */
abstract class FileInfoMetadataBase extends MetadataBase
{
    /**
     * Gets file information value under the 'meta' key.
     *
     * @param string $key
     *   The array key under 'meta'.
     * @param boolean $allowUntrustedAccess
     *   Whether this method should access even if the metadata is not trusted.
     *
     * @return \ArrayObject|null
     *   The file information if available or null if not set.
     */
    public function getFileMetaInfo(string $key, bool $allowUntrustedAccess = false): ?\ArrayObject
    {
        $this->ensureIsTrusted($allowUntrustedAccess);
        $signed = $this->getSigned();
        return $signed['meta'][$key] ?? null;
    }
}
