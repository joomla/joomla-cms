<?php

/**
 * @package         Joomla.Plugin
 * @subpackage      System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn;

use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Http\HttpFactory;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Webauthn\MetadataService\MetadataStatement;
use Webauthn\MetadataService\MetadataStatementRepository;

use function defined;

/**
 * Authenticator metadata repository.
 *
 * This repository contains the metadata of all FIDO authenticators as published by the FIDO
 * Alliance in their MDS version 3.0.
 *
 * @see   https://fidoalliance.org/metadata/
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
    private array $mdsCache = [];

    /**
     * Map of AAGUID to $mdsCache index
     *
     * @since 4.2.0
     */
    private array $mdsMap = [];

    /**
     * Public constructor.
     *
     * @since 4.2.0
     */
    public function __construct()
    {
        $this->load();
    }

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
        $mapKeys = fn(MetadataStatement $meta) => $meta->getAaguid();
        $mapvalues = fn(MetadataStatement $meta) => $meta->getAaguid() ? (object) [
            'description' => $meta->getDescription(),
            'icon'        => $meta->getIcon(),
        ] : null;
        $keys    = array_map($mapKeys, $this->mdsCache);
        $values  = array_map($mapvalues, $this->mdsCache);
        $return  = array_combine($keys, $values) ?: [];

        $filter = fn($x) => !empty($x);

        return array_filter($return, $filter);
    }

    /**
     * Load the authenticator metadata cache
     *
     * @param   bool  $force  Force reload from the web service
     *
     * @since   4.2.0
     */
    private function load(bool $force = false): void
    {
        $this->mdsCache = [];
        $this->mdsMap   = [];
        $jwtFilename    = JPATH_CACHE . '/fido.jwt';

        // If the file exists and it's over one month old do retry loading it.
        if (file_exists($jwtFilename) && filemtime($jwtFilename) < (time() - 2_592_000)) {
            $force = true;
        }

        /**
         * Try to load the MDS source from the FIDO Alliance and cache it.
         *
         * We use a short timeout limit to avoid delaying the page load for way too long. If we fail
         * to download the file in a reasonable amount of time we write an empty string in the
         * file which causes this method to not proceed any further.
         */
        if (!file_exists($jwtFilename) || $force) {
            // Only try to download anything if we can actually cache it!
            if ((file_exists($jwtFilename) && is_writable($jwtFilename)) || (!file_exists($jwtFilename) && is_writable(JPATH_CACHE))) {
                $http     = HttpFactory::getHttp();
                try {
                    $response = $http->get('https://mds.fidoalliance.org/', [], 5);
                    $content  = ($response->code < 200 || $response->code > 299) ? '' : $response->body;
                } catch (\Throwable) {
                    $content = '';
                }
            }

            /**
             * If we could not download anything BUT a non-empty file already exists we must NOT
             * overwrite it.
             *
             * This allows, for example, the site owner to manually place the FIDO MDS cache file
             * in administrator/cache/fido.jwt. This would be useful for high security sites which
             * require attestation BUT are behind a firewall (or disconnected from the Internet),
             * therefore cannot download the MDS cache!
             */
            if (!empty($content) || !file_exists($jwtFilename) || filesize($jwtFilename) <= 1024) {
                file_put_contents($jwtFilename, $content);
            }
        }

        $rawJwt = file_get_contents($jwtFilename);

        if (!is_string($rawJwt) || strlen($rawJwt) < 1024) {
            return;
        }

        try {
            $jwtConfig = Configuration::forUnsecuredSigner();
            $token     = $jwtConfig->parser()->parse($rawJwt);
        } catch (Exception) {
            return;
        }

        if (!($token instanceof Plain)) {
            return;
        }

        unset($rawJwt);

        // Do I need to forcibly update the cache? The JWT has the nextUpdate claim to tell us when to do that.
        try {
            $nextUpdate = new Date($token->claims()->get('nextUpdate', '2020-01-01'));

            if (!$force && !$nextUpdate->diff(new Date())->invert) {
                $this->load(true);

                return;
            }
        } catch (Exception) {
            // OK, don't worry if don't know when the next update is.
        }

        $entriesMapper = function (object $entry) {
            try {
                $array = json_decode(json_encode($entry->metadataStatement, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

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
            } catch (Exception) {
                return null;
            }
        };
        $entries = array_map($entriesMapper, $token->claims()->get('entries', []));

        unset($token);

        $entriesFilter                = fn($x) => !empty($x);
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
