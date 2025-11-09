<?php

declare(strict_types=1);

namespace Joomla\CMS\User;

use Joomla\CMS\User\User;

/**
 * Caching decorator for the User factory.
 *
 * IMPORTANT:
 * - No interface or signature changes (BC-safe).
 * - Per-request in-memory caching for loadById / loadByUsername / loadByEmail.
 * - Everything else delegates to the wrapped factory.
 *
 * Register via DI to replace the default UserFactoryInterface binding:
 * $container->share(UserFactoryInterface::class, function (Container $c) {
 *     $inner = $c->get('core.user.factory'); // whatever concrete is registered as
 *     return new CachingUserFactory($inner);
 * });
 */
final class CachingUserFactory implements UserFactoryInterface
{
    public function __construct(private UserFactoryInterface $inner)
    {
    }

    /** @var array<int, User> */
    private array $byId = [];
/** @var array<string, User> */
    private array $byUsername = [];
/** @var array<string, User> */
    private array $byEmail = [];
/**
     * Returns a cached User instance by id when available; otherwise loads once and caches.
     */
    public function loadById(int $id): User
    {
        if (isset($this->byId[$id])) {
            return $this->byId[$id];
        }

        $user = $this->inner->loadById($id);
// Keep identity maps in sync if we can infer username/email
        $this->byId[$user->id] = $user;
        if (isset($user->username) && is_string($user->username) && $user->username !== '') {
            $this->byUsername[$user->username] = $user;
        }

        if (isset($user->email) && is_string($user->email) && $user->email !== '') {
            $this->byEmail[strtolower($user->email)] = $user;
        }

        return $user;
    }

    /**
     * Cache by username if the interface provides this loader.
     * If your concrete inner factory does not implement it, remove this method.
     */
    public function loadByUsername(string $username): User
    {
        if (isset($this->byUsername[$username])) {
            return $this->byUsername[$username];
        }

        $user = $this->inner->loadByUsername($username);
// Sync maps
        if (is_int($user->id)) {
            $this->byId[$user->id] = $user;
        }

        $this->byUsername[$username] = $user;
        if (isset($user->email) && is_string($user->email) && $user->email !== '') {
            $this->byEmail[strtolower($user->email)] = $user;
        }

        return $user;
    }

    /**
     * Cache by email if the interface provides this loader.
     * If your concrete inner factory does not implement it, remove this method.
     */
    public function loadByEmail(string $email): User
    {
        $key = strtolower($email);
        if (isset($this->byEmail[$key])) {
            return $this->byEmail[$key];
        }

        $user = $this->inner->loadByEmail($email);
// Sync maps
        if (is_int($user->id)) {
            $this->byId[$user->id] = $user;
        }

        if (isset($user->username) && is_string($user->username) && $user->username !== '') {
            $this->byUsername[$user->username] = $user;
        }

        $this->byEmail[$key] = $user;
        return $user;
    }

    /**
     * Pass-through for creating a fresh (uncached) User instance (e.g., id=0).
     * Keeping semantics identical to the inner factory.
     */
    public function createUser(int $id = 0): User
    {
        // Do NOT cache here: callers expect a fresh instance for new users.
        return $this->inner->createUser($id);
    }

    /**
     * Optional helpers (not part of the interface) to keep caches consistent
     * when user data changes during the request.
     * They are safe to leave unused; they don't affect BC.
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

        if (isset($user->email)) {
            unset($this->byEmail[strtolower((string) $user->email)]);
        }
    }

    public function invalidateAll(): void
    {
        $this->byId = [];
        $this->byUsername = [];
        $this->byEmail = [];
    }

    /**
     * If the interface grows new methods in the future, they'll be part of the contract.
     * Anything not implemented here should be added explicitly rather than using __call,
     * to keep strict typing and static analysis happy.
     */
}
