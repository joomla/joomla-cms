<?php
namespace Kunnu\Dropbox\Store;

interface PersistentDataStoreInterface
{
    /**
     * Get a value from the store
     *
     * @param  string $key Data Key
     *
     * @return string
     */
    public function get($key);

    /**
     * Set a value in the store
     * @param string $key   Data Key
     * @param string $value Data Value
     */
    public function set($key, $value);

    /**
     * Clear the key from the store
     *
     * @param $key Data Key
     *
     * @return void
     */
    public function clear($key);
}
