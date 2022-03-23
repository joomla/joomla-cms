<?php


namespace Tuf\Metadata;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use Tuf\Exception\MetadataException;

/**
 * Trait with methods to provide common constraints.
 */
trait ConstraintsTrait
{

    /**
     * Validates the structure of the metadata.
     *
     * @param \ArrayObject $data
     *   The data to validate.
     * @param \Symfony\Component\Validator\Constraints\Collection $constraints
     *   Th constraints collection for validation.
     *
     * @return void
     *
     * @throws \Tuf\Exception\MetadataException
     *    Thrown if validation fails.
     */
    protected static function validate(\ArrayObject $data, Collection $constraints): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($data, $constraints);
        if (count($violations)) {
            $exceptionMessages = [];
            foreach ($violations as $violation) {
                $exceptionMessages[] = (string) $violation;
            }
            throw new MetadataException(implode(",  \n", $exceptionMessages));
        }
    }

    /**
     * Gets the common hash constraints.
     *
     * @return \Symfony\Component\Validator\Constraint[][]
     *   The hash constraints.
     */
    protected static function getHashesConstraints(): array
    {
        return [
            'hashes' => [
                new Count(['min' => 1]),
                new Type('\ArrayObject'),
              // The keys for 'hashes is not know but they all must be strings.
                new All([
                    new Type(['type' => 'string']),
                    new NotBlank(),
                ]),
            ],
        ];
    }

    /**
     * Gets the common version constraints.
     *
     * @return \Symfony\Component\Validator\Constraint[][]
     *   The version constraints.
     */
    protected static function getVersionConstraints(): array
    {
        return [
            'version' => [
                new Type(['type' => 'integer']),
                new GreaterThanOrEqual(1),
            ],
        ];
    }

    /**
     * Gets the common threshold constraints.
     *
     * @return \Symfony\Component\Validator\Constraint[][]
     *   The threshold constraints.
     */
    protected static function getThresholdConstraints(): array
    {
        return [
            'threshold' => [
                new Type(['type' => 'integer']),
                new GreaterThanOrEqual(1),
            ],
        ];
    }
    /**
     * Gets the common keyids constraints.
     *
     * @return \Symfony\Component\Validator\Constraint[][]
     *   The keysids constraints.
     */
    protected static function getKeyidsConstraints(): array
    {
        return [
            'keyids' => [
                new Count(['min' => 1]),
                new Type(['type' => 'array']),
                // The keys for 'hashes is not know but they all must be strings.
                new All([
                    new Type(['type' => 'string']),
                    new NotBlank(),
                ]),
            ],
        ];
    }

    /**
     * Gets the common key Collection constraints.
     *
     * @return Collection
     *   The 'key' Collection constraint.
     */
    protected static function getKeyConstraints(): Collection
    {
        return new Collection([
            // This field is not part of the TUF specification and is being
            // removed from the Python TUF reference implementation in
            // https://github.com/theupdateframework/tuf/issues/848.
            // If it is provided though we only support the default value which
            // is passed on from a setting in the Python `securesystemslib`
            // library.
            'keyid_hash_algorithms' => new Optional([
                new EqualTo(['value' => ["sha256", "sha512"]]),
            ]),
            'keytype' => [
                new Type(['type' => 'string']),
                new NotBlank(),
            ],
            'keyval' => [
                new Type('\ArrayObject'),
                new Collection([
                    'public' => [
                        new Type(['type' => 'string']),
                        new NotBlank(),
                    ],
                ]),
            ],
            'scheme' => [
                new Type(['type' => 'string']),
                new NotBlank(),
            ],
        ]);
    }

    /**
     * Gets the role constraints.
     *
     * @return \Symfony\Component\Validator\Constraints\Collection
     *   The role constraints collection.
     */
    protected static function getRoleConstraints(): Collection
    {
        return new Collection(
            static::getKeyidsConstraints() +
            static::getThresholdConstraints()
        );
    }
}
