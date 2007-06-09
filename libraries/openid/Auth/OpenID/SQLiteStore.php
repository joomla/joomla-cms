<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * An SQLite store.
 *
 * @package OpenID
 */

/**
 * Require the base class file.
 */
require_once "Auth/OpenID/SQLStore.php";

/**
 * An SQL store that uses SQLite as its backend.
 *
 * @package OpenID
 */
class Auth_OpenID_SQLiteStore extends Auth_OpenID_SQLStore {
    function setSQL()
    {
        $this->sql['nonce_table'] =
            "CREATE TABLE %s (nonce CHAR(8) UNIQUE PRIMARY KEY, ".
            "expires INTEGER)";

        $this->sql['assoc_table'] =
            "CREATE TABLE %s (server_url VARCHAR(2047), handle VARCHAR(255), ".
            "secret BLOB(128), issued INTEGER, lifetime INTEGER, ".
            "assoc_type VARCHAR(64), PRIMARY KEY (server_url, handle))";

        $this->sql['settings_table'] =
            "CREATE TABLE %s (setting VARCHAR(128) UNIQUE PRIMARY KEY, ".
            "value BLOB(20))";

        $this->sql['create_auth'] =
            "INSERT INTO %s VALUES ('auth_key', ?)";

        $this->sql['get_auth'] =
            "SELECT value FROM %s WHERE setting = 'auth_key'";

        $this->sql['set_assoc'] =
            "INSERT OR REPLACE INTO %s VALUES (?, ?, ?, ?, ?, ?)";

        $this->sql['get_assocs'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ?";

        $this->sql['get_assoc'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ? AND handle = ?";

        $this->sql['remove_assoc'] =
            "DELETE FROM %s WHERE server_url = ? AND handle = ?";

        $this->sql['add_nonce'] =
            "INSERT OR REPLACE INTO %s (nonce, expires) VALUES (?, ?)";

        $this->sql['get_nonce'] =
            "SELECT * FROM %s WHERE nonce = ?";

        $this->sql['remove_nonce'] =
            "DELETE FROM %s WHERE nonce = ?";
    }
}

?>