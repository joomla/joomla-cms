<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * This module contains code for dealing with associations between
 * consumers and servers.
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
 * @access private
 */
require_once 'Auth/OpenID/CryptUtil.php';

/**
 * @access private
 */
require_once 'Auth/OpenID/KVForm.php';

/**
 * This class represents an association between a server and a
 * consumer.  In general, users of this library will never see
 * instances of this object.  The only exception is if you implement a
 * custom {@link Auth_OpenID_OpenIDStore}.
 *
 * If you do implement such a store, it will need to store the values
 * of the handle, secret, issued, lifetime, and assoc_type instance
 * variables.
 *
 * @package OpenID
 */
class Auth_OpenID_Association {

    /**
     * This is a HMAC-SHA1 specific value.
     *
     * @access private
     */
    var $SIG_LENGTH = 20;

    /**
     * The ordering and name of keys as stored by serialize.
     *
     * @access private
     */
    var $assoc_keys = array(
                            'version',
                            'handle',
                            'secret',
                            'issued',
                            'lifetime',
                            'assoc_type'
                            );

    /**
     * This is an alternate constructor (factory method) used by the
     * OpenID consumer library to create associations.  OpenID store
     * implementations shouldn't use this constructor.
     *
     * @access private
     *
     * @param integer $expires_in This is the amount of time this
     * association is good for, measured in seconds since the
     * association was issued.
     *
     * @param string $handle This is the handle the server gave this
     * association.
     *
     * @param string secret This is the shared secret the server
     * generated for this association.
     *
     * @param assoc_type This is the type of association this
     * instance represents.  The only valid value of this field at
     * this time is 'HMAC-SHA1', but new types may be defined in the
     * future.
     *
     * @return association An {@link Auth_OpenID_Association}
     * instance.
     */
    function fromExpiresIn($expires_in, $handle, $secret, $assoc_type)
    {
        $issued = time();
        $lifetime = $expires_in;
        return new Auth_OpenID_Association($handle, $secret,
                                           $issued, $lifetime, $assoc_type);
    }

    /**
     * This is the standard constructor for creating an association.
     * The library should create all of the necessary associations, so
     * this constructor is not part of the external API.
     *
     * @access private
     *
     * @param string $handle This is the handle the server gave this
     * association.
     *
     * @param string $secret This is the shared secret the server
     * generated for this association.
     *
     * @param integer $issued This is the time this association was
     * issued, in seconds since 00:00 GMT, January 1, 1970.  (ie, a
     * unix timestamp)
     *
     * @param integer $lifetime This is the amount of time this
     * association is good for, measured in seconds since the
     * association was issued.
     *
     * @param string $assoc_type This is the type of association this
     * instance represents.  The only valid value of this field at
     * this time is 'HMAC-SHA1', but new types may be defined in the
     * future.
     */
    function Auth_OpenID_Association(
        $handle, $secret, $issued, $lifetime, $assoc_type)
    {
        if ($assoc_type != 'HMAC-SHA1') {
            $fmt = 'HMAC-SHA1 is the only supported association type (got %s)';
            trigger_error(sprintf($fmt, $assoc_type), E_USER_ERROR);
        }

        $this->handle = $handle;
        $this->secret = $secret;
        $this->issued = $issued;
        $this->lifetime = $lifetime;
        $this->assoc_type = $assoc_type;
    }

    /**
     * This returns the number of seconds this association is still
     * valid for, or 0 if the association is no longer valid.
     *
     * @return integer $seconds The number of seconds this association
     * is still valid for, or 0 if the association is no longer valid.
     */
    function getExpiresIn($now = null)
    {
        if ($now == null) {
            $now = time();
        }

        return max(0, $this->issued + $this->lifetime - $now);
    }

    /**
     * This checks to see if two {@link Auth_OpenID_Association}
     * instances represent the same association.
     *
     * @return bool $result true if the two instances represent the
     * same association, false otherwise.
     */
    function equal($other)
    {
        return ((gettype($this) == gettype($other))
                && ($this->handle == $other->handle)
                && ($this->secret == $other->secret)
                && ($this->issued == $other->issued)
                && ($this->lifetime == $other->lifetime)
                && ($this->assoc_type == $other->assoc_type));
    }

    /**
     * Convert an association to KV form.
     *
     * @return string $result String in KV form suitable for
     * deserialization by deserialize.
     */
    function serialize()
    {
        $data = array(
                     'version' => '2',
                     'handle' => $this->handle,
                     'secret' => base64_encode($this->secret),
                     'issued' => strval(intval($this->issued)),
                     'lifetime' => strval(intval($this->lifetime)),
                     'assoc_type' => $this->assoc_type
                     );

        assert(array_keys($data) == $this->assoc_keys);

        return Auth_OpenID_KVForm::fromArray($data, $strict = true);
    }

    /**
     * Parse an association as stored by serialize().  This is the
     * inverse of serialize.
     *
     * @param string $assoc_s Association as serialized by serialize()
     * @return Auth_OpenID_Association $result instance of this class
     */
    function deserialize($class_name, $assoc_s)
    {
        $pairs = Auth_OpenID_KVForm::toArray($assoc_s, $strict = true);
        $keys = array();
        $values = array();
        foreach ($pairs as $key => $value) {
            if (is_array($value)) {
                list($key, $value) = $value;
            }
            $keys[] = $key;
            $values[] = $value;
        }

        $class_vars = get_class_vars($class_name);
        $class_assoc_keys = $class_vars['assoc_keys'];

        sort($keys);
        sort($class_assoc_keys);

        if ($keys != $class_assoc_keys) {
            trigger_error('Unexpected key values: ' . strval($keys),
                          E_USER_WARNING);
            return null;
        }

        $version = $pairs['version'];
        $handle = $pairs['handle'];
        $secret = $pairs['secret'];
        $issued = $pairs['issued'];
        $lifetime = $pairs['lifetime'];
        $assoc_type = $pairs['assoc_type'];

        if ($version != '2') {
            trigger_error('Unknown version: ' . $version, E_USER_WARNING);
            return null;
        }

        $issued = intval($issued);
        $lifetime = intval($lifetime);
        $secret = base64_decode($secret);

        return new $class_name(
            $handle, $secret, $issued, $lifetime, $assoc_type);
    }

    /**
     * Generate a signature for a sequence of (key, value) pairs
     *
     * @access private
     * @param array $pairs The pairs to sign, in order.  This is an
     * array of two-tuples.
     * @return string $signature The binary signature of this sequence
     * of pairs
     */
    function sign($pairs)
    {
        $kv = Auth_OpenID_KVForm::fromArray($pairs);
        return Auth_OpenID_HMACSHA1($this->secret, $kv);
    }

    /**
     * Generate a signature for some fields in a dictionary
     *
     * @access private
     * @param array $fields The fields to sign, in order; this is an
     * array of strings.
     * @param array $data Dictionary of values to sign (an array of
     * string => string pairs).
     * @return string $signature The signature, base64 encoded
     */
    function signDict($fields, $data, $prefix = 'openid.')
    {
        $pairs = array();
        foreach ($fields as $field) {
            $pairs[] = array($field, $data[$prefix . $field]);
        }

        return base64_encode($this->sign($pairs));
    }

    /**
     * Add a signature to an array of fields
     *
     * @access private
     */
    function addSignature($fields, &$data, $prefix = 'openid.')
    {
        $sig = $this->signDict($fields, $data, $prefix);
        $signed = implode(",", $fields);
        $data[$prefix . 'sig'] = $sig;
        $data[$prefix . 'signed'] = $signed;
    }

    /**
     * Confirm that the signature of these fields matches the
     * signature contained in the data
     *
     * @access private
     */
    function checkSignature($data, $prefix = 'openid.')
    {
        $signed = $data[$prefix . 'signed'];
        $fields = explode(",", $signed);
        $expected_sig = $this->signDict($fields, $data, $prefix);
        $request_sig = $data[$prefix . 'sig'];

        return ($request_sig == $expected_sig);
    }
}

?>