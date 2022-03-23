<?php

namespace Tuf\Metadata;

/**
 * Provides methods to load value objects for trusted metadata.
 */
class Factory
{
	/**
	 * The persistent storage backend for trusted metadata.
	 *
	 * @var \ArrayAccess
	 */
	private $storage;

	/**
	 * Factory constructor.
	 *
	 * @param   \ArrayAccess $storage
	 *   The persistent storage backend.
	 */
	public function __construct(\ArrayAccess $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * Loads a value object for trusted metadata.
	 *
	 * @param string $role
	 *   The role to be loaded.
	 *
	 * @return \Tuf\Metadata\MetadataBase|null
	 *   The trusted metadata for the role, or NULL if none was found.
	 * @throws \Tuf\Exception\MetadataException
	 */
	public function load(string $role): ?MetadataBase
	{
		// TODO: this is changed from $role . ".json" to $role . "_json"
		$fileName = $role . "_json";

		if (isset($this->storage[$fileName]))
		{
			$json = $this->storage[$fileName];

			switch ($role)
			{
				case RootMetadata::TYPE:
					$currentMetadata = RootMetadata::createFromJson($json);
					break;
				case SnapshotMetadata::TYPE:
					$currentMetadata = SnapshotMetadata::createFromJson($json);
					break;
				case TimestampMetadata::TYPE:
					$currentMetadata = TimestampMetadata::createFromJson($json);
					break;
				default:
					$currentMetadata = TargetsMetadata::createFromJson($json, $role);
			}

			$currentMetadata->trust();

			return $currentMetadata;
		}
		else
		{
			return null;
		}
	}
}
