<?php

namespace Tuf\Metadata;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Tuf\Key;
use Tuf\Role;

class RootMetadata extends MetadataBase
{

    /**
     * {@inheritdoc}
     */
    public const TYPE = 'root';

    /**
     * {@inheritdoc}
     */
    protected static function getSignedCollectionOptions(): array
    {
        $options = parent::getSignedCollectionOptions();
        $options['fields']['keys'] = new Required([
            new Type('\ArrayObject'),
            new Count(['min' => 1]),
            new All([
                static::getKeyConstraints(),
            ]),
        ]);
        $roleConstraints = static::getRoleConstraints();
        $options['fields']['roles'] = new Collection([
            'targets' => new Required($roleConstraints),
            'timestamp' => new Required($roleConstraints),
            'snapshot' => new Required($roleConstraints),
            'root' => new Required($roleConstraints),
            'mirror' => new Optional($roleConstraints),
        ]);
        $options['fields']['consistent_snapshot'] = new Required([
            new Type('boolean'),
            new EqualTo(true),
        ]);
        return $options;
    }

    /**
     * Gets the roles from the metadata.
     *
     * @param boolean $allowUntrustedAccess
     *   Whether this method should access even if the metadata is not trusted.
     *
     * @return \Tuf\Role[]
     *   The roles.
     */
    public function getRoles(bool $allowUntrustedAccess = false): array
    {
        $this->ensureIsTrusted($allowUntrustedAccess);
        $roles = [];
        foreach ($this->getSigned()['roles'] as $roleName => $roleInfo) {
            $roles[$roleName] = Role::createFromMetadata($roleInfo, $roleName);
        }
        return $roles;
    }

    /**
     * Gets the keys for the root metadata.
     *
     * @param boolean $allowUntrustedAccess
     *   Whether this method should access even if the metadata is not trusted.
     *
     * @return \Tuf\Key[]
     *   The keys for the metadata.
     */
    public function getKeys(bool $allowUntrustedAccess = false): array
    {
        $this->ensureIsTrusted($allowUntrustedAccess);
        $keys = [];
        foreach ($this->getSigned()['keys'] as $keyId => $keyInfo) {
            $keys[$keyId] = Key::createFromMetadata($keyInfo);
        }
        return $keys;
    }

    /**
     * Determines whether consistent snapshots are supported.
     *
     * @return boolean
     *   Whether consistent snapshots are supported.
     */
    public function supportsConsistentSnapshots(): bool
    {
        $this->ensureIsTrusted();
        return $this->getSigned()['consistent_snapshot'];
    }
}
