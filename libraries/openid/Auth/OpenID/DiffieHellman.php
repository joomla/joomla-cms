<?php

/**
 * The OpenID library's Diffie-Hellman implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

require_once 'Auth/OpenID/BigMath.php';
require_once 'Auth/OpenID/HMACSHA1.php';

function Auth_OpenID_getDefaultMod()
{
    return '155172898181473697471232257763715539915724801'.
        '966915404479707795314057629378541917580651227423'.
        '698188993727816152646631438561595825688188889951'.
        '272158842675419950341258706556549803580104870537'.
        '681476726513255747040765857479291291572334510643'.
        '245094715007229621094194349783925984760375594985'.
        '848253359305585439638443';
}

function Auth_OpenID_getDefaultGen()
{
    return '2';
}

/**
 * The Diffie-Hellman key exchange class.  This class relies on
 * {@link Auth_OpenID_MathLibrary} to perform large number operations.
 *
 * @access private
 * @package OpenID
 */
class Auth_OpenID_DiffieHellman {

    var $mod;
    var $gen;
    var $private;
    var $lib = null;

    function Auth_OpenID_DiffieHellman($mod = null, $gen = null,
                                       $private = null, $lib = null)
    {
        if ($lib === null) {
            $this->lib =& Auth_OpenID_getMathLib();
        } else {
            $this->lib =& $lib;
        }

        if ($mod === null) {
            $this->mod = $this->lib->init(Auth_OpenID_getDefaultMod());
        } else {
            $this->mod = $mod;
        }

        if ($gen === null) {
            $this->gen = $this->lib->init(Auth_OpenID_getDefaultGen());
        } else {
            $this->gen = $gen;
        }

        if ($private === null) {
            $r = $this->lib->rand($this->mod);
            $this->private = $this->lib->add($r, 1);
        } else {
            $this->private = $private;
        }

        $this->public = $this->lib->powmod($this->gen, $this->private,
                                           $this->mod);
    }

    function getSharedSecret($composite)
    {
        return $this->lib->powmod($composite, $this->private, $this->mod);
    }

    function getPublicKey()
    {
        return $this->public;
    }

    /**
     * Generate the arguments for an OpenID Diffie-Hellman association
     * request
     */
    function getAssocArgs()
    {
        $cpub = $this->lib->longToBase64($this->getPublicKey());
        $args = array(
                      'openid.dh_consumer_public' => $cpub,
                      'openid.session_type' => 'DH-SHA1'
                      );

        if ($this->lib->cmp($this->mod, Auth_OpenID_getDefaultMod()) ||
            $this->lib->cmp($this->gen, Auth_OpenID_getDefaultGen())) {
            $args['openid.dh_modulus'] = $this->lib->longToBase64($this->mod);
            $args['openid.dh_gen'] = $this->lib->longToBase64($this->gen);
        }

        return $args;
    }

    function usingDefaultValues()
    {
        return ($this->mod == Auth_OpenID_getDefaultMod() &&
                $this->gen == Auth_OpenID_getDefaultGen());
    }

    /**
     * Perform the server side of the OpenID Diffie-Hellman association
     */
    function serverAssociate($consumer_args, $assoc_secret)
    {
        $lib =& Auth_OpenID_getMathLib();

        if (isset($consumer_args['openid.dh_modulus'])) {
            $mod = $lib->base64ToLong($consumer_args['openid.dh_modulus']);
        } else {
            $mod = null;
        }

        if (isset($consumer_args['openid.dh_gen'])) {
            $gen = $lib->base64ToLong($consumer_args['openid.dh_gen']);
        } else {
            $gen = null;
        }
        
        $cpub64 = @$consumer_args['openid.dh_consumer_public'];
        if (!isset($cpub64)) {
            return false;
        }

        $dh = new Auth_OpenID_DiffieHellman($mod, $gen);
        $cpub = $lib->base64ToLong($cpub64);
        $mac_key = $dh->xorSecret($cpub, $assoc_secret);
        $enc_mac_key = base64_encode($mac_key);
        $spub64 = $lib->longToBase64($dh->getPublicKey());

        $server_args = array(
                             'session_type' => 'DH-SHA1',
                             'dh_server_public' => $spub64,
                             'enc_mac_key' => $enc_mac_key
                             );

        return $server_args;
    }

    function consumerFinish($reply)
    {
        $spub = $this->lib->base64ToLong($reply['dh_server_public']);
        if ($this->lib->cmp($spub, 0) <= 0) {
            return false;
        }
        $enc_mac_key = base64_decode($reply['enc_mac_key']);
        return $this->xorSecret($spub, $enc_mac_key);
    }

    function xorSecret($composite, $secret)
    {
        $dh_shared = $this->getSharedSecret($composite);
        $dh_shared_str = $this->lib->longToBinary($dh_shared);
        $sha1_dh_shared = Auth_OpenID_SHA1($dh_shared_str);

        $xsecret = "";
        for ($i = 0; $i < strlen($secret); $i++) {
            $xsecret .= chr(ord($secret[$i]) ^ ord($sha1_dh_shared[$i]));
        }

        return $xsecret;
    }
}
