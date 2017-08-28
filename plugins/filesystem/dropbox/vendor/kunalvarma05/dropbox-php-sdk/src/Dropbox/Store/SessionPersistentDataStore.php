<?php
namespace Kunnu\Dropbox\Store;

class SessionPersistentDataStore implements PersistentDataStoreInterface
{

    /**
     * Session Variable Prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new SessionPersistentDataStore instance
     *
     * @param string $prefix Session Variable Prefix
     */
    public function __construct($prefix = "DBAPI_")
    {
        $this->prefix = $prefix;
    }

    /**
     * Get a value from the store
     *
     * @param  string $key Data Key
     *
     * @return string|null
     */
    public function get($key)
    {
        if (isset($_SESSION[$this->prefix . $key])) {
            return $_SESSION[$this->prefix . $key];
        }

        return null;
    }

    /**
     * Set a value in the store
     * @param string $key   Data Key
     * @param string $value Data Value
     *
     * @return void
     */
    public function set($key, $value)
    {
        $_SESSION[$this->prefix . $key] = $value;
    }

    /**
     * Clear the key from the store
     *
     * @param $key Data Key
     *
     * @return void
     */
    public function clear($key)
    {
        if (isset($_SESSION[$this->prefix . $key])) {
            unset($_SESSION[$this->prefix . $key]);
        }
    }
}
