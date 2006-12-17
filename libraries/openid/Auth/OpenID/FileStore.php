<?php

/**
 * This file supplies a Memcached store backend for OpenID servers and
 * consumers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

/**
 * Require base class for creating a new interface.
 */
require_once 'Auth/OpenID.php';
require_once 'Auth/OpenID/Interface.php';
require_once 'Auth/OpenID/HMACSHA1.php';

/**
 * This is a filesystem-based store for OpenID associations and
 * nonces.  This store should be safe for use in concurrent systems on
 * both windows and unix (excluding NFS filesystems).  There are a
 * couple race conditions in the system, but those failure cases have
 * been set up in such a way that the worst-case behavior is someone
 * having to try to log in a second time.
 *
 * Most of the methods of this class are implementation details.
 * People wishing to just use this store need only pay attention to
 * the constructor.
 *
 * @package OpenID
 */
class Auth_OpenID_FileStore extends Auth_OpenID_OpenIDStore {

    /**
     * Initializes a new {@link Auth_OpenID_FileStore}.  This
     * initializes the nonce and association directories, which are
     * subdirectories of the directory passed in.
     *
     * @param string $directory This is the directory to put the store
     * directories in.
     */
    function Auth_OpenID_FileStore($directory)
    {
        if (!Auth_OpenID::ensureDir($directory)) {
            trigger_error('Not a directory and failed to create: '
                          . $directory, E_USER_ERROR);
        }
        $directory = realpath($directory);

        $this->directory = $directory;
        $this->active = true;

        $this->nonce_dir = $directory . DIRECTORY_SEPARATOR . 'nonces';

        $this->association_dir = $directory . DIRECTORY_SEPARATOR .
            'associations';

        // Temp dir must be on the same filesystem as the assciations
        // $directory and the $directory containing the auth key file.
        $this->temp_dir = $directory . DIRECTORY_SEPARATOR . 'temp';

        $this->auth_key_name = $directory . DIRECTORY_SEPARATOR . 'auth_key';

        $this->max_nonce_age = 6 * 60 * 60; // Six hours, in seconds

        if (!$this->_setup()) {
            trigger_error('Failed to initialize OpenID file store in ' .
                          $directory, E_USER_ERROR);
        }
    }

    function destroy()
    {
        Auth_OpenID_FileStore::_rmtree($this->directory);
        $this->active = false;
    }

    /**
     * Make sure that the directories in which we store our data
     * exist.
     *
     * @access private
     */
    function _setup()
    {
        return (Auth_OpenID::ensureDir(dirname($this->auth_key_name)) &&
                Auth_OpenID::ensureDir($this->nonce_dir) &&
                Auth_OpenID::ensureDir($this->association_dir) &&
                Auth_OpenID::ensureDir($this->temp_dir));
    }

    /**
     * Create a temporary file on the same filesystem as
     * $this->auth_key_name and $this->association_dir.
     *
     * The temporary directory should not be cleaned if there are any
     * processes using the store. If there is no active process using
     * the store, it is safe to remove all of the files in the
     * temporary directory.
     *
     * @return array ($fd, $filename)
     * @access private
     */
    function _mktemp()
    {
        $name = Auth_OpenID_FileStore::_mkstemp($dir = $this->temp_dir);
        $file_obj = @fopen($name, 'wb');
        if ($file_obj !== false) {
            return array($file_obj, $name);
        } else {
            Auth_OpenID_FileStore::_removeIfPresent($name);
        }
    }

    /**
     * Read the auth key from the auth key file. Will return None if
     * there is currently no key.
     *
     * @return mixed
     */
    function readAuthKey()
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $auth_key_file = @fopen($this->auth_key_name, 'rb');
        if ($auth_key_file === false) {
            return null;
        }

        $key = fread($auth_key_file, filesize($this->auth_key_name));
        fclose($auth_key_file);

        return $key;
    }

    /**
     * Generate a new random auth key and safely store it in the
     * location specified by $this->auth_key_name.
     *
     * @return string $key
     */
    function createAuthKey()
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $auth_key = Auth_OpenID_CryptUtil::randomString($this->AUTH_KEY_LEN);

        list($file_obj, $tmp) = $this->_mktemp();

        fwrite($file_obj, $auth_key);
        fflush($file_obj);
        fclose($file_obj);

        if (function_exists('link')) {
            // Posix filesystem
            $saved = link($tmp, $this->auth_key_name);
            Auth_OpenID_FileStore::_removeIfPresent($tmp);
        } else {
            // Windows filesystem
            $saved = rename($tmp, $this->auth_key_name);
        }

        if (!$saved) {
            // The link failed, either because we lack the permission,
            // or because the file already exists; try to read the key
            // in case the file already existed.
            $auth_key = $this->readAuthKey();
        }

        return $auth_key;
    }

    /**
     * Retrieve the auth key from the file specified by
     * $this->auth_key_name, creating it if it does not exist.
     *
     * @return string $key
     */
    function getAuthKey()
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $auth_key = $this->readAuthKey();
        if ($auth_key === null) {
            $auth_key = $this->createAuthKey();

            if (strlen($auth_key) != $this->AUTH_KEY_LEN) {
                $fmt = 'Got an invalid auth key from %s. Expected '.
                    '%d-byte string. Got: %s';
                $msg = sprintf($fmt, $this->auth_key_name, $this->AUTH_KEY_LEN,
                               $auth_key);
                trigger_error($msg, E_USER_WARNING);
                return null;
            }
        }
        return $auth_key;
    }

    /**
     * Create a unique filename for a given server url and
     * handle. This implementation does not assume anything about the
     * format of the handle. The filename that is returned will
     * contain the domain name from the server URL for ease of human
     * inspection of the data directory.
     *
     * @return string $filename
     */
    function getAssociationFilename($server_url, $handle)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        if (strpos($server_url, '://') === false) {
            trigger_error(sprintf("Bad server URL: %s", $server_url),
                          E_USER_WARNING);
            return null;
        }

        list($proto, $rest) = explode('://', $server_url, 2);
        $parts = explode('/', $rest);
        $domain = Auth_OpenID_FileStore::_filenameEscape($parts[0]);
        $url_hash = Auth_OpenID_FileStore::_safe64($server_url);
        if ($handle) {
            $handle_hash = Auth_OpenID_FileStore::_safe64($handle);
        } else {
            $handle_hash = '';
        }

        $filename = sprintf('%s-%s-%s-%s', $proto, $domain, $url_hash,
                            $handle_hash);

        return $this->association_dir. DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Store an association in the association directory.
     */
    function storeAssociation($server_url, $association)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return false;
        }

        $association_s = $association->serialize();
        $filename = $this->getAssociationFilename($server_url,
                                                  $association->handle);
        list($tmp_file, $tmp) = $this->_mktemp();

        if (!$tmp_file) {
            trigger_error("_mktemp didn't return a valid file descriptor",
                          E_USER_WARNING);
            return false;
        }

        fwrite($tmp_file, $association_s);

        fflush($tmp_file);

        fclose($tmp_file);

        if (@rename($tmp, $filename)) {
            return true;
        } else {
            // In case we are running on Windows, try unlinking the
            // file in case it exists.
            @unlink($filename);

            // Now the target should not exist. Try renaming again,
            // giving up if it fails.
            if (@rename($tmp, $filename)) {
                return true;
            }
        }

        // If there was an error, don't leave the temporary file
        // around.
        Auth_OpenID_FileStore::_removeIfPresent($tmp);
        return false;
    }

    /**
     * Retrieve an association. If no handle is specified, return the
     * association with the most recent issue time.
     *
     * @return mixed $association
     */
    function getAssociation($server_url, $handle = null)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        if ($handle === null) {
            $handle = '';
        }

        // The filename with the empty handle is a prefix of all other
        // associations for the given server URL.
        $filename = $this->getAssociationFilename($server_url, $handle);

        if ($handle) {
            return $this->_getAssociation($filename);
        } else {
            $association_files =
                Auth_OpenID_FileStore::_listdir($this->association_dir);
            $matching_files = array();

            // strip off the path to do the comparison
            $name = basename($filename);
            foreach ($association_files as $association_file) {
                if (strpos($association_file, $name) === 0) {
                    $matching_files[] = $association_file;
                }
            }

            $matching_associations = array();
            // read the matching files and sort by time issued
            foreach ($matching_files as $name) {
                $full_name = $this->association_dir . DIRECTORY_SEPARATOR .
                    $name;
                $association = $this->_getAssociation($full_name);
                if ($association !== null) {
                    $matching_associations[] = array($association->issued,
                                                     $association);
                }
            }

            $issued = array();
            $assocs = array();
            foreach ($matching_associations as $key => $assoc) {
                $issued[$key] = $assoc[0];
                $assocs[$key] = $assoc[1];
            }

            array_multisort($issued, SORT_DESC, $assocs, SORT_DESC,
                            $matching_associations);

            // return the most recently issued one.
            if ($matching_associations) {
                list($issued, $assoc) = $matching_associations[0];
                return $assoc;
            } else {
                return null;
            }
        }
    }

    /**
     * @access private
     */
    function _getAssociation($filename)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $assoc_file = @fopen($filename, 'rb');

        if ($assoc_file === false) {
            return null;
        }

        $assoc_s = fread($assoc_file, filesize($filename));
        fclose($assoc_file);

        if (!$assoc_s) {
            return null;
        }

        $association =
            Auth_OpenID_Association::deserialize('Auth_OpenID_Association',
                                                $assoc_s);

        if (!$association) {
            Auth_OpenID_FileStore::_removeIfPresent($filename);
            return null;
        }

        if ($association->getExpiresIn() == 0) {
            Auth_OpenID_FileStore::_removeIfPresent($filename);
            return null;
        } else {
            return $association;
        }
    }

    /**
     * Remove an association if it exists. Do nothing if it does not.
     *
     * @return bool $success
     */
    function removeAssociation($server_url, $handle)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $assoc = $this->getAssociation($server_url, $handle);
        if ($assoc === null) {
            return false;
        } else {
            $filename = $this->getAssociationFilename($server_url, $handle);
            return Auth_OpenID_FileStore::_removeIfPresent($filename);
        }
    }

    /**
     * Mark this nonce as present.
     */
    function storeNonce($nonce)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $filename = $this->nonce_dir . DIRECTORY_SEPARATOR . $nonce;
        $nonce_file = fopen($filename, 'w');
        if ($nonce_file === false) {
            return false;
        }
        fclose($nonce_file);
        return true;
    }

    /**
     * Return whether this nonce is present. As a side effect, mark it
     * as no longer present.
     *
     * @return bool $present
     */
    function useNonce($nonce)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $filename = $this->nonce_dir . DIRECTORY_SEPARATOR . $nonce;
        $st = @stat($filename);

        if ($st === false) {
            return false;
        }

        // Either it is too old or we are using it. Either way, we
        // must remove the file.
        if (!unlink($filename)) {
            return false;
        }

        $now = time();
        $nonce_age = $now - $st[9];

        // We can us it if the age of the file is less than the
        // expiration time.
        return $nonce_age <= $this->max_nonce_age;
    }

    /**
     * Remove expired entries from the database. This is potentially
     * expensive, so only run when it is acceptable to take time.
     */
    function clean()
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $nonces = Auth_OpenID_FileStore::_listdir($this->nonce_dir);
        $now = time();

        // Check all nonces for expiry
        foreach ($nonces as $nonce) {
            $filename = $this->nonce_dir . DIRECTORY_SEPARATOR . $nonce;
            $st = @stat($filename);

            if ($st !== false) {
                // Remove the nonce if it has expired
                $nonce_age = $now - $st[9];
                if ($nonce_age > $this->max_nonce_age) {
                    Auth_OpenID_FileStore::_removeIfPresent($filename);
                }
            }
        }

        $association_filenames =
            Auth_OpenID_FileStore::_listdir($this->association_dir);

        foreach ($association_filenames as $association_filename) {
            $association_file = fopen($association_filename, 'rb');

            if ($association_file !== false) {
                $assoc_s = fread($association_file,
                                 filesize($association_filename));
                fclose($association_file);

                // Remove expired or corrupted associations
                $association =
                  Auth_OpenID_Association::deserialize(
                         'Auth_OpenID_Association', $assoc_s);

                if ($association === null) {
                    Auth_OpenID_FileStore::_removeIfPresent(
                                                 $association_filename);
                } else {
                    if ($association->getExpiresIn() == 0) {
                        Auth_OpenID_FileStore::_removeIfPresent(
                                                 $association_filename);
                    }
                }
            }
        }
    }

    /**
     * @access private
     */
    function _rmtree($dir)
    {
        if ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        if ($handle = opendir($dir)) {
            while ($item = readdir($handle)) {
                if (!in_array($item, array('.', '..'))) {
                    if (is_dir($dir . $item)) {

                        if (!Auth_OpenID_FileStore::_rmtree($dir . $item)) {
                            return false;
                        }
                    } else if (is_file($dir . $item)) {
                        if (!unlink($dir . $item)) {
                            return false;
                        }
                    }
                }
            }

            closedir($handle);

            if (!@rmdir($dir)) {
                return false;
            }

            return true;
        } else {
            // Couldn't open directory.
            return false;
        }
    }

    /**
     * @access private
     */
    function _mkstemp($dir)
    {
        foreach (range(0, 4) as $i) {
            $name = tempnam($dir, "php_openid_filestore_");

            if ($name !== false) {
                return $name;
            }
        }
        return false;
    }

    /**
     * @access private
     */
    function _mkdtemp($dir)
    {
        foreach (range(0, 4) as $i) {
            $name = $dir . strval(DIRECTORY_SEPARATOR) . strval(getmypid()) .
                "-" . strval(rand(1, time()));
            if (!mkdir($name, 0700)) {
                return false;
            } else {
                return $name;
            }
        }
        return false;
    }

    /**
     * @access private
     */
    function _listdir($dir)
    {
        $handle = opendir($dir);
        $files = array();
        while (false !== ($filename = readdir($handle))) {
            $files[] = $filename;
        }
        return $files;
    }

    /**
     * @access private
     */
    function _isFilenameSafe($char)
    {
        $_Auth_OpenID_filename_allowed = Auth_OpenID_letters .
            Auth_OpenID_digits . ".";
        return (strpos($_Auth_OpenID_filename_allowed, $char) !== false);
    }

    /**
     * @access private
     */
    function _safe64($str)
    {
        $h64 = base64_encode(Auth_OpenID_SHA1($str));
        $h64 = str_replace('+', '_', $h64);
        $h64 = str_replace('/', '.', $h64);
        $h64 = str_replace('=', '', $h64);
        return $h64;
    }

    /**
     * @access private
     */
    function _filenameEscape($str)
    {
        $filename = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $c = $str[$i];
            if (Auth_OpenID_FileStore::_isFilenameSafe($c)) {
                $filename .= $c;
            } else {
                $filename .= sprintf("_%02X", ord($c));
            }
        }
        return $filename;
    }

    /**
     * Attempt to remove a file, returning whether the file existed at
     * the time of the call.
     *
     * @access private
     * @return bool $result True if the file was present, false if not.
     */
    function _removeIfPresent($filename)
    {
        return @unlink($filename);
    }
}

?>
