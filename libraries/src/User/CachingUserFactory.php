<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\User;

/**
 * Caching decorator for the User factory.
 *
 * - BC-safe: implements UserFactoryInterface without changing signatures.
 * - Adds per-request identity maps for id and username.
 */
final class CachingUserFactory implements UserFactoryInterface
{
    /** @var array<int, User> */
    private array $byId = [];

    /** @var array<string, User> */
    private array $byUsername = [];

    public function __construct(private UserFactoryInterface $inner)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserById(int $id): User
    {
        if (isset($this->byId[$id])) {
            return $this->byId[$id];
        }

        $user = $this->inner->loadUserById($id);

        // Keep maps in sync
        $this->byId[$user->id] = $user;

        if (isset($user->username) && \is_string($user->username) && $user->username !== '') {
            $this->byUsername[$user->username] = $user;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername(string $username): User
    {
        if (isset($this->byUsername[$username])) {
            return $this->byUsername[$username];
        }

        $user = $this->inner->loadUserByUsername($username);

        if (\is_int($user->id)) {
            $this->byId[$user->id] = $user;
        }

        $this->byUsername[$username] = $user;

        return $user;
    }

    /**
     * Invalidate a single cached user by id, if needed.
     */
    public function invalidateById(int $id): void
    {
        if (!isset($this->byId[$id])) {
            return;
        }

        $user = $this->byId[$id];
        unset($this->byId[$id]);

        if (isset($user->username)) {
            unset($this->byUsername[(string) $user->username]);
        }
    }

    /**
     * Clear all cached User instances (per-request scope).
     */
    public function invalidateAll(): void
    {
        $this->byId       = [];
        $this->byUsername = [];
    }
}
