<?php

namespace Tuf\Client;

use Tuf\Exception\Attack\SignatureThresholdException;
use Tuf\JsonNormalizer;
use Tuf\Key;
use Tuf\KeyDB;
use Tuf\Metadata\MetadataBase;
use Tuf\Metadata\RootMetadata;
use Tuf\Role;
use Tuf\RoleDB;

/**
 * A class that verifies metadata signatures.
 */
final class SignatureVerifier
{
	/**
	 * @var \Tuf\RoleDB
	 */
	private $roleDb;

	/**
	 * @var \Tuf\KeyDB
	 */
	private $keyDb;

	/**
	 * SignatureVerifier constructor.
	 */
	private function __construct(RoleDB $roleDb, KeyDB $keyDb)
	{
		$this->roleDb = $roleDb;
		$this->keyDb = $keyDb;
	}

	/**
	 * Creates a SignatureVerifier object from a RootMetadata object.
	 *
	 * @param RootMetadata $rootMetadata
	 * @param   bool $allowUntrustedAccess
	 *
	 * @return static
	 */
	public static function createFromRootMetadata(RootMetadata $rootMetadata, bool $allowUntrustedAccess = false): self
	{
		return new static(
			RoleDB::createFromRootMetadata($rootMetadata, $allowUntrustedAccess),
			KeyDB::createFromRootMetadata($rootMetadata, $allowUntrustedAccess)
		);
	}

	/**
	 * Checks signatures on a verifiable structure.
	 *
	 * @param   \Tuf\Metadata\MetadataBase $metadata
	 *     The metadata to check signatures on.
	 *
	 * @return void
	 *
	 * @throws \Tuf\Exception\Attack\SignatureThresholdException
	 *   Thrown if the signature threshold has not be reached.
	 */
	public function checkSignatures(MetadataBase $metadata): void
	{
		$signatures = $metadata->getSignatures();

		$role = $this->roleDb->getRole($metadata->getRole());
		$needVerified = $role->getThreshold();
		$verifiedKeySignatures = [];

		$canonicalBytes = JsonNormalizer::asNormalizedJson($metadata->getSigned());

		foreach ($signatures as $signature)
		{
			// Don't allow the same key to be counted twice.
			if ($role->isKeyIdAcceptable($signature['keyid']) && $this->verifySingleSignature($canonicalBytes, $signature))
			{
				$verifiedKeySignatures[$signature['keyid']] = true;
			}

			// @todo Determine if we should check all signatures and warn for
			//     bad signatures even if this method returns TRUE because the
			//     threshold has been met.
			//     https://github.com/php-tuf/php-tuf/issues/172
			if (count($verifiedKeySignatures) >= $needVerified)
			{
				break;
			}
		}

		if (count($verifiedKeySignatures) < $needVerified)
		{
			throw new SignatureThresholdException("Signature threshold not met on " . $metadata->getRole());
		}
	}

	/**
	 * Verifies a single signature.
	 *
	 * @param   string $bytes
	 *     The canonical JSON string of the 'signed' section of the given file.
	 * @param   \ArrayAccess $signatureMeta
	 *     The ArrayAccess object of metadata for the signature. Each signature
	 *     metadata contains two elements:
	 *     - keyid: The identifier of the key signing the role data.
	 *     - sig: The hex-encoded signature of the canonical form of the
	 *       metadata for the role.
	 *
	 * @return boolean
	 *     TRUE if the signature is valid for $bytes.
	 */
	private function verifySingleSignature(string $bytes, \ArrayAccess $signatureMeta): bool
	{
		// Get the pubkey from the key database.
		$pubkey = $this->keyDb->getKey($signatureMeta['keyid'])->getPublic();

		// Encode the pubkey and signature, and check that the signature is
		// valid for the given data and pubkey.
		$pubkeyBytes = hex2bin($pubkey);
		$sigBytes = hex2bin($signatureMeta['sig']);

		// @todo Check that the key type in $signatureMeta is ed25519; return
		//     false if not.
		//     https://github.com/php-tuf/php-tuf/issues/168
		return \sodium_crypto_sign_verify_detached($sigBytes, $bytes, $pubkeyBytes);
	}

	/**
	 * Adds a role to the signature verifier.
	 *
	 * @param   \Tuf\Role $role
	 */
	public function addRole(Role $role): void
	{
		if (!$this->roleDb->roleExists($role->getName()))
		{
			$this->roleDb->addRole($role);
		}
	}

	/**
	 * Adds a key to the signature verifier.
	 *
	 * @param   string $keyId
	 * @param   \Tuf\Key $key
	 */
	public function addKey(string $keyId, Key $key): void
	{
		$this->keyDb->addKey($keyId, $key);
	}
}
