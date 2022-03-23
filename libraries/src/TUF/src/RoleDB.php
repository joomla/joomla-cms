<?php

namespace Tuf;

use Tuf\Exception\NotFoundException;
use Tuf\Exception\RoleExistsException;
use Tuf\Metadata\RootMetadata;

/**
 * Represent a collection of roles and their organization.
 *
 * @see https://github.com/theupdateframework/tuf/blob/6ae3ea6d7d2aa80ba0571503a5e6c3808c44ff64/tuf/roledb.py
 */
class RoleDB
{
    /**
     * Key roles indexed by role name.
     *
     * @var \array[]
     */
    protected $roles;

    /**
     *  Creates a role database from all of the unique roles in the metadata.
     *
     * @param \Tuf\Metadata\RootMetadata $rootMetadata
     *    The root metadata.
     * @param boolean $allowUntrustedAccess
     *   Whether this method should access even if the metadata is not trusted.
     *
     * @return \Tuf\RoleDB
     *    The created RoleDB.
     *
     * @throws \Exception
     *     Thrown if a threshold value in the metadata is not valid.
     *
     * @see https://theupdateframework.github.io/specification/v1.0.18#document-formats
     */
    public static function createFromRootMetadata(RootMetadata $rootMetadata, bool $allowUntrustedAccess = false): RoleDB
    {
        $db = new self();
        foreach ($rootMetadata->getRoles($allowUntrustedAccess) as $roleName => $roleInfo) {
            $db->addRole($roleInfo);
        }

        return $db;
    }

    /**
     * Constructs a new RoleDB object.
     */
    public function __construct()
    {
        $this->roles = [];
    }

    /**
     * Adds role metadata to the database.
     *
     * @param string $roleName
     *     The role name.
     * @param \Tuf\Role $role
     *     The role to add.
     *
     * @return void
     *
     * @throws \Exception Thrown if the role already exists.
     */
    public function addRole(Role $role): void
    {
        if ($this->roleExists($role->getName())) {
            throw new RoleExistsException('Role already exists: ' . $role->getName());
        }

        $this->roles[$role->getName()] = $role;
    }

    /**
     * Verifies whether a given role name is stored in the role database.
     *
     * @param string $roleName
     *     The role name.
     *
     * @return boolean
     *     True if the role is found in the role database; false otherwise.
     */
    public function roleExists(string $roleName): bool
    {
        return !empty($this->roles[$roleName]);
    }

    /**
     * Gets the role information.
     *
     * @param string $roleName
     *    The role name.
     *
     * @return \Tuf\Role
     *    The role.
     *
     * @throws \Tuf\Exception\NotFoundException
     *     Thrown if the role does not exist.
     *
     * @see https://theupdateframework.github.io/specification/v1.0.18#document-formats
     */
    public function getRole(string $roleName): Role
    {
        if (! $this->roleExists($roleName)) {
            throw new NotFoundException($roleName, 'role');
        }

        /** @var \Tuf\Role $role */
        $role = $this->roles[$roleName];
        return $role;
    }
}
