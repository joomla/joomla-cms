<?php

namespace Tuf\Metadata;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Collection;

class SnapshotMetadata extends FileInfoMetadataBase
{
	/**
	 * {@inheritdoc}
	 */
	public const TYPE = 'snapshot';

	/**
	 * {@inheritdoc}
	 */
	protected static function getSignedCollectionOptions(): array
	{
		$options = parent::getSignedCollectionOptions();
		$options['fields']['meta'] = new Required(
			[
			new Type('\ArrayObject'),
			new Count(['min' => 1]),
			new All(
				[
				new Collection(
					[
						'fields' => static::getSnapshotMetaConstraints(),
						'allowExtraFields' => true,
					]
				),
				]
			),
			]
		);

		return $options;
	}

	/**
	 * Returns the fields required or optional for a snapshot meta file
	 *
	 * @return array
	 */
	private static function getSnapshotMetaConstraints()
	{
		return [
			'version' => [
				new Type(['type' => 'integer']),
				new GreaterThanOrEqual(1),
			],
			new Optional(
				[
					new Collection(
						[
							'length' => [
								new Type(['type' => 'integer']),
								new GreaterThanOrEqual(1),
							],
						] + static::getHashesConstraints()
					),
				]
			),
		];
	}
}
