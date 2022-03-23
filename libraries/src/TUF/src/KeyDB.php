<?php


namespace Tuf;

use Tuf\Exception\InvalidKeyException;
use Tuf\Exception\NotFoundException;
use Tuf\Metadata\RootMetadata;

/**
 * Represent a collection of keys and their organization.
 *
 * This class ensures the layout of the collection remains consistent and
 * easily verifiable. Keys are set/get in this class primarily by their key ID.
 * Key IDs are used as identifiers for keys and are hexadecimal representations
 * of the hash of key objects.  See computeKeyIds() to learn precisely how
 * key IDs are generated.  One may get the key ID of a key object by accessing
 * the array's 'keyid' key (i.e., $keyMeta['keyid']).
 *
 * @see https://github.com/theupdateframework/tuf/blob/292b18926b45106b27f582dc3cb1433363d03a9a/tuf/keydb.py
 */
class KeyDB
{
    /**
     * Keys indexed by key ID.
     *
     * @var \array[]
     */
    protected $keys;

    /**
     * Creates a key database with the unique keys found in root metadata.
     *
     * @param \Tuf\Metadata\RootMetadata $rootMetadata
     *    The root metadata.
     * @param boolean $allowUntrustedAccess
     *   Whether this method should access even if the metadata is not trusted.
     *
     * @return \Tuf\KeyDB
     *     The constructed key database object.
     *
     * @throws \Tuf\Exception\InvalidKeyException
     *   Thrown if an unsupported or invalid key exists in the metadata.
     *
     * @see https://theupdateframework.github.io/specification/v1.0.18#document-formats
     */
    public static function createFromRootMetadata(RootMetadata $rootMetadata, bool $allowUntrustedAccess = false): KeyDB
    {
        $db = new self();

        foreach ($rootMetadata->getKeys($allowUntrustedAccess) as $keyId => $key) {
            $db->addKey($keyId, $key);
        }

        return $db;
    }

    /**
     * Gets the supported encryption key types.
     *
     * @return string[]
     *     An array of supported encryption key type names (e.g. 'ed25519').
     */
    public static function getSupportedKeyTypes(): array
    {
        return ['ed25519'];
    }

    /**
     * Constructs a new KeyDB.
     */
    public function __construct()
    {
        $this->keys = [];
    }

    /**
     * Adds key metadata to the key database while avoiding duplicates.
     *
     * @param string $keyId
     *   The key ID given as the object key in root.json or another keys list.
     * @param \Tuf\Key
     *   The key.
     *
     * @return void
     *
     * @see https://theupdateframework.github.io/specification/v1.0.18#document-formats
     */
    public function addKey(string $keyId, Key $key): void
    {
        if (! in_array($key->getType(), self::getSupportedKeyTypes(), true)) {
            // @todo Convert this to a log line as per Python.
            // https://github.com/php-tuf/php-tuf/issues/160
            throw new InvalidKeyException("Root metadata file contains an unsupported key type: \"${keyMeta['keytype']}\"");
        }
        // Per TUF specification 4.3, Clients MUST calculate each KEYID to
        // verify this is correct for the associated key.
        if ($keyId !== $key->getComputedKeyId()) {
            throw new InvalidKeyException('The calculated KEYID does not match the value provided.');
        }
        $this->keys[$keyId] = $key;
    }

    /**
     * Returns the key metadata for a given key ID.
     *
     * @param string $keyId
     *     The key ID.
     *
     * @return \Tuf\Key
     *     The key.
     *
     * @throws \Tuf\Exception\NotFoundException
     *     Thrown if the key ID is not found in the keydb database.
     *
     * @see https://theupdateframework.github.io/specification/v1.0.18#document-formats
     */
    public function getKey(string $keyId): Key
    {
        if (empty($this->keys[$keyId])) {
            throw new NotFoundException($keyId, 'key');
        }
        return $this->keys[$keyId];
    }
}
