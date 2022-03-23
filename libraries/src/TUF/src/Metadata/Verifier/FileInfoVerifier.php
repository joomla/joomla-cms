<?php

namespace Tuf\Metadata\Verifier;

use Tuf\Exception\Attack\RollbackAttackException;
use Tuf\Metadata\FileInfoMetadataBase;

/**
 * Verifier for metadata classes that have information about other files.
 */
abstract class FileInfoVerifier extends VerifierBase
{
    /**
     * The trusted metadata, if any.
     *
     * @var \Tuf\Metadata\FileInfoMetadataBase
     */
    protected $trustedMetadata;


    /**
     * Checks for rollback of files referenced in $untrustedMetadata.
     *
     * @param \Tuf\Metadata\FileInfoMetadataBase $untrustedMetadata
     *     The untrusted metadata.
     *
     * @throws \Tuf\Exception\Attack\RollbackAttackException
     */
    protected function checkFileInfoVersions(FileInfoMetadataBase $untrustedMetadata): void
    {
        // Check that all files in the trusted/local metadata info under the 'meta' section are less or equal to
        // the same files in the new metadata info.
        // For 'snapshot' type this is § 5.5.5.
        // For 'timestamp' type this is § 5.4.3.?.
        $localMetaFileInfos = $this->trustedMetadata->getSigned()['meta'];
        $type = $this->trustedMetadata->getType();
        foreach ($localMetaFileInfos as $fileName => $localFileInfo) {
            /** @var \Tuf\Metadata\SnapshotMetadata|\Tuf\Metadata\TimestampMetadata $untrustedMetadata */
            if ($remoteFileInfo = $untrustedMetadata->getFileMetaInfo($fileName, true)) {
                if ($remoteFileInfo['version'] < $localFileInfo['version']) {
                    $message = "Remote $type metadata file '$fileName' version \"${$remoteFileInfo['version']}\" " .
                      "is less than previously seen  version \"${$localFileInfo['version']}\"";
                    throw new RollbackAttackException($message);
                }
            }
        }
    }
}
