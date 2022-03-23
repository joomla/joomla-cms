<?php

namespace Tuf\Metadata;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Tuf\Constraints\Collection as TufCollection;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Tuf\DelegatedRole;
use Tuf\Exception\NotFoundException;
use Tuf\Key;

class TargetsMetadata extends MetadataBase
{

    /**
     * {@inheritdoc}
     */
    public const TYPE = 'targets';

    /**
     * The role name if different from the type.
     *
     * @var string
     */
    private $role;

    /**
     * {@inheritdoc}
     *
     * @param string|null $roleName
     *   The role name if not the same as the type.
     */
    public static function createFromJson(string $json, string $roleName = null): MetadataBase
    {
        $newMetadata = parent::createFromJson($json);
        $newMetadata->role = $roleName;
        return $newMetadata;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getSignedCollectionOptions(): array
    {
        $options = parent::getSignedCollectionOptions();
        $options['fields']['delegations'] = new Optional([
            new Collection([
                'keys' => new Required([
                    new Type('\ArrayObject'),
                    new All([
                        static::getKeyConstraints(),
                    ]),
                ]),
                'roles' => new All([
                    new Type('\ArrayObject'),
                    new TufCollection([
                        'fields' => [
                            'name' => [
                                new NotBlank(),
                                new Type(['type' => 'string']),
                            ],
                            'paths' => [
                                new Type(['type' => 'array']),
                                new All([
                                    new Type(['type' => 'string']),
                                    new NotBlank(),
                                ]),
                            ],
                            'terminating' => [
                                new Type(['type' => 'boolean']),
                            ],
                        ] + static::getKeyidsConstraints() + static::getThresholdConstraints(),
                        // @todo Support 'path_hash_prefixes' in
                        //    https://github.com/php-tuf/php-tuf/issues/191
                        'unsupportedFields' => ['path_hash_prefixes'],
                    ]),
                ]),
            ]),
        ]);
        $options['fields']['targets'] = new Required([
            new All([
                new Collection([
                    'length' => [
                        new Type(['type' => 'integer']),
                        new GreaterThanOrEqual(1),
                    ],
                    'custom' => new Optional([
                        new Type('\ArrayObject'),
                    ]),
                ] + static::getHashesConstraints()),
            ]),

        ]);
        return $options;
    }

    /**
     * Returns the length, in bytes, of a specific target.
     *
     * @param string $target
     *   The target path.
     *
     * @return integer
     *   The length (size) of the target, in bytes.
     */
    public function getLength(string $target): int
    {
        return $this->getInfo($target)['length'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRole(): string
    {
        return $this->role ?? $this->getType();
    }

    /**
     * Returns the known hashes for a specific target.
     *
     * @param string $target
     *   The target path.
     *
     * @return \ArrayObject
     *   The known hashes for the object. The keys are the hash algorithm (e.g.
     *   'sha256') and the values are the hash digest.
     */
    public function getHashes(string $target): \ArrayObject
    {
        return $this->getInfo($target)['hashes'];
    }

    /**
     * Determines if a target is specified in the current metadata.
     *
     * @param string $target
     *   The target path.
     *
     * @return bool
     *   True if the target is specified, or false otherwise.
     */
    public function hasTarget(string $target): bool
    {
        try {
            $this->getInfo($target);
            return true;
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * Gets info about a specific target.
     *
     * @param string $target
     *   The target path.
     *
     * @return \ArrayObject
     *   The target's info.
     *
     * @throws \Tuf\Exception\NotFoundException
     *   Thrown if the target is not mentioned in this metadata.
     */
    protected function getInfo(string $target): \ArrayObject
    {
        $signed = $this->getSigned();
        if (isset($signed['targets'][$target])) {
            return $signed['targets'][$target];
        }
        throw new NotFoundException($target, 'Target');
    }

    /**
     * Gets the delegated keys if any.
     *
     * @return \Tuf\Key[]
     *   The delegated keys.
     */
    public function getDelegatedKeys(): array
    {
        $keys = [];
        foreach ($this->getSigned()['delegations']['keys'] as $keyId => $keyInfo) {
            $keys[$keyId] = Key::createFromMetadata($keyInfo);
        }
        return $keys;
    }

    /**
     * Gets the delegated roles if any.
     *
     * @return \Tuf\DelegatedRole[]
     *   The delegated roles.
     */
    public function getDelegatedRoles(): array
    {
        $roles = [];
        foreach ($this->getSigned()['delegations']['roles'] as $roleInfo) {
            $role = DelegatedRole::createFromMetadata($roleInfo);
            $roles[$role->getName()] = $role;
        }
        return $roles;
    }
}
