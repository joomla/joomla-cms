<?php

/**
 * This file specifies the interface for PHP OpenID store implementations.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * This is the interface for the store objects the OpenID library
 * uses. It is a single class that provides all of the persistence
 * mechanisms that the OpenID library needs, for both servers and
 * consumers.  If you want to create an SQL-driven store, please see
 * then {@link Auth_OpenID_SQLStore} class.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 */
class Auth_OpenID_OpenIDStore {
    /**
     * @var integer The length of the auth key that should be returned
     * by the getAuthKey method.
     */
    var $AUTH_KEY_LEN = 20;

    /**
     * This method puts an Association object into storage,
     * retrievable by server URL and handle.
     *
     * @param string $server_url The URL of the identity server that
     * this association is with. Because of the way the server portion
     * of the library uses this interface, don't assume there are any
     * limitations on the character set of the input string. In
     * particular, expect to see unescaped non-url-safe characters in
     * the server_url field.
     *
     * @param Association $association The Association to store.
     */
    function storeAssociation($server_url, $association)
    {
        trigger_error("Auth_OpenID_OpenIDStore::storeAssociation ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * This method returns an Association object from storage that
     * matches the server URL and, if specified, handle. It returns
     * null if no such association is found or if the matching
     * association is expired.
     *
     * If no handle is specified, the store may return any association
     * which matches the server URL. If multiple associations are
     * valid, the recommended return value for this method is the one
     * that will remain valid for the longest duration.
     *
     * This method is allowed (and encouraged) to garbage collect
     * expired associations when found. This method must not return
     * expired associations.
     *
     * @param string $server_url The URL of the identity server to get
     * the association for. Because of the way the server portion of
     * the library uses this interface, don't assume there are any
     * limitations on the character set of the input string.  In
     * particular, expect to see unescaped non-url-safe characters in
     * the server_url field.
     *
     * @param mixed $handle This optional parameter is the handle of
     * the specific association to get. If no specific handle is
     * provided, any valid association matching the server URL is
     * returned.
     *
     * @return Association The Association for the given identity
     * server.
     */
    function getAssociation($server_url, $handle = null)
    {
        trigger_error("Auth_OpenID_OpenIDStore::getAssociation ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * This method removes the matching association if it's found, and
     * returns whether the association was removed or not.
     *
     * @param string $server_url The URL of the identity server the
     * association to remove belongs to. Because of the way the server
     * portion of the library uses this interface, don't assume there
     * are any limitations on the character set of the input
     * string. In particular, expect to see unescaped non-url-safe
     * characters in the server_url field.
     *
     * @param string $handle This is the handle of the association to
     * remove. If there isn't an association found that matches both
     * the given URL and handle, then there was no matching handle
     * found.
     *
     * @return mixed Returns whether or not the given association existed.
     */
    function removeAssociation($server_url, $handle)
    {
        trigger_error("Auth_OpenID_OpenIDStore::removeAssociation ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * Stores a nonce. This is used by the consumer to prevent replay
     * attacks.
     *
     * @param string $nonce The nonce to store.
     *
     * @return null
     */
    function storeNonce($nonce)
    {
        trigger_error("Auth_OpenID_OpenIDStore::storeNonce ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * This method is called when the library is attempting to use a
     * nonce. If the nonce is in the store, this method removes it and
     * returns a value which evaluates as true. Otherwise it returns a
     * value which evaluates as false.
     *
     * This method is allowed and encouraged to treat nonces older
     * than some period (a very conservative window would be 6 hours,
     * for example) as no longer existing, and return False and remove
     * them.
     *
     * @param string $nonce The nonce to use.
     *
     * @return bool Whether or not the nonce was valid.
     */
    function useNonce($nonce)
    {
        trigger_error("Auth_OpenID_OpenIDStore::useNonce ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * This method returns a key used to sign the tokens, to ensure
     * that they haven't been tampered with in transit. It should
     * return the same key every time it is called. The key returned
     * should be {@link AUTH_KEY_LEN} bytes long.
     *
     * @return string The key. It should be {@link AUTH_KEY_LEN} bytes in
     * length, and use the full range of byte values. That is, it
     * should be treated as a lump of binary data stored in a string.
     */
    function getAuthKey()
    {
        trigger_error("Auth_OpenID_OpenIDStore::getAuthKey ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * This method must return true if the store is a dumb-mode-style
     * store. Unlike all other methods in this class, this one
     * provides a default implementation, which returns false.
     *
     * In general, any custom subclass of {@link Auth_OpenID_OpenIDStore}
     * won't override this method, as custom subclasses are only likely to
     * be created when the store is fully functional.
     *
     * @return bool true if the store works fully, false if the
     * consumer will have to use dumb mode to use this store.
     */
    function isDumb()
    {
        return false;
    }

    /**
     * Removes all entries from the store; implementation is optional.
     */
    function reset()
    {
    }

}
?>