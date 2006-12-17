<?php

/**
 * A PostgreSQL store.
 *
 * @package OpenID
 */

/**
 * Require the base class file.
 */
require_once "Auth/OpenID/SQLStore.php";

/**
 * An SQL store that uses PostgreSQL as its backend.
 *
 * @package OpenID
 */
class Auth_OpenID_PostgreSQLStore extends Auth_OpenID_SQLStore {
    /**
     * @access private
     */
    function setSQL()
    {
        $this->sql['nonce_table'] =
            "CREATE TABLE %s (nonce CHAR(8) UNIQUE PRIMARY KEY, ".
            "expires INTEGER)";

        $this->sql['assoc_table'] =
            "CREATE TABLE %s (server_url VARCHAR(2047), handle VARCHAR(255), ".
            "secret BYTEA, issued INTEGER, lifetime INTEGER, ".
            "assoc_type VARCHAR(64), PRIMARY KEY (server_url, handle), ".
            "CONSTRAINT secret_length_constraint CHECK ".
            "(LENGTH(secret) <= 128))";

        $this->sql['settings_table'] =
            "CREATE TABLE %s (setting VARCHAR(128) UNIQUE PRIMARY KEY, ".
            "value BYTEA, ".
            "CONSTRAINT value_length_constraint CHECK (LENGTH(value) <= 20))";

        $this->sql['create_auth'] =
            "INSERT INTO %s VALUES ('auth_key', '!')";

        $this->sql['get_auth'] =
            "SELECT value FROM %s WHERE setting = 'auth_key'";

        $this->sql['set_assoc'] =
            array(
                  'insert_assoc' => "INSERT INTO %s (server_url, handle, ".
                  "secret, issued, lifetime, assoc_type) VALUES ".
                  "(?, ?, '!', ?, ?, ?)",
                  'update_assoc' => "UPDATE %s SET secret = '!', issued = ?, ".
                  "lifetime = ?, assoc_type = ? WHERE server_url = ? AND ".
                  "handle = ?"
                  );

        $this->sql['get_assocs'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ?";

        $this->sql['get_assoc'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ? AND handle = ?";

        $this->sql['remove_assoc'] =
            "DELETE FROM %s WHERE server_url = ? AND handle = ?";

        $this->sql['add_nonce'] =
            array(
                  'insert_nonce' => "INSERT INTO %s (nonce, expires) VALUES ".
                  "(?, ?)",
                  'update_nonce' => "UPDATE %s SET expires = ? WHERE nonce = ?"
                  );

        $this->sql['get_nonce'] =
            "SELECT * FROM %s WHERE nonce = ?";

        $this->sql['remove_nonce'] =
            "DELETE FROM %s WHERE nonce = ?";
    }

    /**
     * @access private
     */
    function _set_assoc($server_url, $handle, $secret, $issued, $lifetime,
                        $assoc_type)
    {
        $result = $this->_get_assoc($server_url, $handle);
        if ($result) {
            // Update the table since this associations already exists.
            $this->connection->query($this->sql['set_assoc']['update_assoc'],
                                     array($secret, $issued, $lifetime,
                                           $assoc_type, $server_url, $handle));
        } else {
            // Insert a new record because this association wasn't
            // found.
            $this->connection->query($this->sql['set_assoc']['insert_assoc'],
                                     array($server_url, $handle, $secret,
                                           $issued, $lifetime, $assoc_type));
        }
    }

    /**
     * @access private
     */
    function _add_nonce($nonce, $expires)
    {
        if ($this->_get_nonce($nonce)) {
            return $this->resultToBool($this->connection->query(
                                      $this->sql['add_nonce']['update_nonce'],
                                      array($expires, $nonce)));
        } else {
            return $this->resultToBool($this->connection->query(
                                      $this->sql['add_nonce']['insert_nonce'],
                                      array($nonce, $expires)));
        }
    }

    /**
     * @access private
     */
    function blobEncode($blob)
    {
        return $this->_octify($blob);
    }

    /**
     * @access private
     */
    function blobDecode($blob)
    {
        return $this->_unoctify($blob);
    }
}

?>