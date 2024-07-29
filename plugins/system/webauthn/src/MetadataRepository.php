<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\MetadataService\Statement\MetadataStatement;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Authenticator metadata repository.
 *
 * This repository contains the metadata of all FIDO authenticators as published by the FIDO
 * Alliance in their MDS version 3.0.
 *
 * @link  https://fidoalliance.org/metadata/
 * @since 4.2.0
 */
final class MetadataRepository implements MetadataStatementRepository
{
    /**
     * Cache of authenticator metadata statements
     *
     * @var   MetadataStatement[]
     * @since 4.2.0
     */
    private $mdsCache = [];

    /**
     * Map of AAGUID to $mdsCache index
     *
     * @var   array
     * @since 4.2.0
     */
    private $mdsMap = [];

    /**
     * Have I already tried to load the metadata cache?
     *
     * @var   bool
     * @since 4.2.2
     */
    private $loaded = false;

    /**
     * Find an authenticator metadata statement given an AAGUID
     *
     * @param   string  $aaguid  The AAGUID to find
     *
     * @return  MetadataStatement|null  The metadata statement; null if the AAGUID is unknown
     * @since   4.2.0
     */
    public function findOneByAAGUID(string $aaguid): ?MetadataStatement
    {
        $this->load();

        $idx = $this->mdsMap[$aaguid] ?? null;

        return $idx ? $this->mdsCache[$idx] : null;
    }

    /**
     * Get basic information of the known FIDO authenticators by AAGUID
     *
     * @return  object[]
     * @since   4.2.0
     */
    public function getKnownAuthenticators(): array
    {
        $this->load();

        $mapKeys = function (MetadataStatement $meta) {
            return $meta->getAaguid();
        };
        $mapvalues = function (MetadataStatement $meta) {
            return $meta->getAaguid() ? (object) [
                'description' => $meta->getDescription(),
                'icon'        => $meta->getIcon(),
            ] : null;
        };
        $keys    = array_map($mapKeys, $this->mdsCache);
        $values  = array_map($mapvalues, $this->mdsCache);
        $return  = array_combine($keys, $values) ?: [];

        $filter = function ($x) {
            return !empty($x);
        };

        return array_filter($return, $filter);
    }

    /**
     * Load the authenticator metadata cache
     *
     * @return  void
     * @since   4.2.0
     */
    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $this->mdsCache = [];
        $this->mdsMap   = [];

        $jwtFilename = JPATH_PLUGINS . '/system/webauthn/fido.jwt';
        $rawJwt      = file_get_contents($jwtFilename);

        if (!\is_string($rawJwt) || \strlen($rawJwt) < 1024) {
            return;
        }

        try {
            $jwtConfig = Configuration::forUnsecuredSigner();
            $token     = $jwtConfig->parser()->parse($rawJwt);
        } catch (\Exception $e) {
            return;
        }

        if (!($token instanceof Plain)) {
            return;
        }

        unset($rawJwt);

        $entriesMapper = function (object $entry) {
            try {
                $array = json_decode(json_encode($entry->metadataStatement), true);

                /**
                 * This prevents an error when we're asking for attestation on authenticators which
                 * don't allow it. We are really not interested in the attestation per se, but
                 * requiring an attestation is the only way we can get the AAGUID of the
                 * authenticator.
                 */
                if (isset($array['attestationTypes'])) {
                    unset($array['attestationTypes']);
                }

                return MetadataStatement::createFromArray($array);
            } catch (\Exception $e) {
                return null;
            }
        };
        $entries = array_map($entriesMapper, $token->claims()->get('entries', []));

        unset($token);

        $entriesFilter                = function ($x) {
            return !empty($x);
        };
        $this->mdsCache = array_filter($entries, $entriesFilter);

        foreach ($this->mdsCache as $idx => $meta) {
            $aaguid = $meta->getAaguid();

            if (empty($aaguid)) {
                continue;
            }

            $this->mdsMap[$aaguid] = $idx;
        }
    }
}
