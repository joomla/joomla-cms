<?php

namespace Tuf\Metadata;

use DeepCopy\DeepCopy;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Tuf\JsonNormalizer;

/**
 * Base class for metadata.
 */
abstract class MetadataBase
{
	use ConstraintsTrait;

	/**
	 * The metadata.
	 *
	 * @var array
	 */
	protected $metadata;

	/**
	 * Metadata type.
	 *
	 * @var string
	 */
	protected const TYPE = '';

	/**
	 * @var string
	 */
	private $sourceJson;

	/**
	 * Whether the metadata has been verified and should be considered trusted.
	 *
	 * @var boolean
	 */
	private $isTrusted = false;

	/**
	 * MetadataBase constructor.
	 *
	 * @param   \ArrayObject $metadata
	 *   The data.
	 * @param   string $sourceJson
	 *   The source JSON.
	 */
	public function __construct(\ArrayObject $metadata, string $sourceJson)
	{
		$this->metadata = $metadata;
		$this->sourceJson = $sourceJson;
	}

	/**
	 * Gets the original JSON source.
	 *
	 * @return string
	 *   The JSON source.
	 */
	public function getSource():string
	{
		return $this->sourceJson;
	}

	/**
	 * Create an instance and also validate the decoded JSON.
	 *
	 * @param   string $json
	 *   A JSON string representing TUF metadata.
	 *
	 * @return static
	 *   The new instance.
	 *
	 * @throws \Tuf\Exception\MetadataException
	 *   Thrown if validation fails.
	 */
	public static function createFromJson(string $json): self
	{
		$data = JsonNormalizer::decode($json);
		static::validate($data, new Collection(static::getConstraints()));

		return new static($data, $json);
	}

	/**
	 * Gets the constraints for top-level metadata.
	 *
	 * @return \Symfony\Component\Validator\Constraint[]
	 *   Array of constraints.
	 */
	protected static function getConstraints(): array
	{
		return [
			'signatures' => new Required(
				[
				new Type('array'),
				new Count(['min' => 1]),
				new All(
					[
					new Collection(
						[
						'keyid' => [
							new NotBlank,
							new Type(['type' => 'string']),
						],
						'sig' => [
							new NotBlank,
							new Type(['type' => 'string']),
						],
						]
					),
					]
				),
				]
			),
			'signed' => new Required(
				[
				new Collection(static::getSignedCollectionOptions()),
				]
			),
		];
	}

	/**
	 * Gets options for the 'signed' metadata property.
	 *
	 * @return array
	 *   An options array as expected by
	 *   \Symfony\Component\Validator\Constraints\Collection::__construct().
	 */
	protected static function getSignedCollectionOptions(): array
	{
		return [
			'fields' => [
				'_type' => [
					new EqualTo(['value' => static::TYPE]),
					new Type(['type' => 'string']),
				],
				'expires' => new DateTime(['value' => \DateTimeInterface::ISO8601]),
				// We only expect to work with major version 1.
				'spec_version' => [
					new NotBlank,
					new Type(['type' => 'string']),
					//new Regex(['pattern' => '/^1\.[0-9]+\.[0-9]+$/']),
				],
			] + static::getVersionConstraints(),
			'allowExtraFields' => true,
		];
	}

	/**
	 * Get signed.
	 *
	 * @return \ArrayObject
	 *   The "signed" section of the data.
	 */
	public function getSigned(): \ArrayObject
	{
		return (new DeepCopy)->copy($this->metadata['signed']);
	}

	/**
	 * Get version.
	 *
	 * @return integer
	 *   The version.
	 */
	public function getVersion(): int
	{
		return $this->getSigned()['version'];
	}

	/**
	 * Get the expires date string.
	 *
	 * @return string
	 *   The date string.
	 */
	public function getExpires(): string
	{
		return $this->getSigned()['expires'];
	}

	/**
	 * Get signatures.
	 *
	 * @return array
	 *   The "signatures" section of the data.
	 */
	public function getSignatures(): array
	{
		return (new DeepCopy)->copy($this->metadata['signatures']);
	}

	/**
	 * Get the metadata type.
	 *
	 * @return string
	 *   The type.
	 */
	public function getType(): string
	{
		return $this->getSigned()['_type'];
	}

	/**
	 * Gets the role for the metadata.
	 *
	 * @return string
	 *   The type.
	 */
	public function getRole(): string
	{
		// For most metadata types the 'type' and the 'role' are the same.
		// Metadata types that need to specify a different role should override
		// this method.
		return $this->getType();
	}

	/**
	 * Sets the metadata as trusted.
	 *
	 * @return void
	 */
	public function trust(): void
	{
		$this->isTrusted = true;
	}

	/**
	 * Ensures that the metadata is trusted or the caller explicitly expects untrusted metadata.
	 *
	 * @param   boolean $allowUntrustedAccess
	 *   Whether this method should access even if the metadata is not trusted.
	 *
	 * @return void
	 */
	public function ensureIsTrusted(bool $allowUntrustedAccess = false): void
	{
		if (!$allowUntrustedAccess && !$this->isTrusted)
		{
			throw new \RuntimeException("Cannot use untrusted '{$this->getRole()}'. metadata.");
		}
	}
}
