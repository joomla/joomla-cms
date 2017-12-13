<?php
namespace Sodium;

use ParagonIE_Sodium_Compat;

/**
 * This file will monkey patch the pure-PHP implementation in place of the
 * PECL functions, but only if they do not already exist.
 *
 * Thus, the functions just proxy to the appropriate ParagonIE_Sodium_Compat
 * method.
 */
if (!is_callable('\\Sodium\\bin2hex')) {
    /**
     * @param string $string
     * @return string
     */
    function bin2hex($string)
    {
        return ParagonIE_Sodium_Compat::bin2hex($string);
    }
}
if (!is_callable('\\Sodium\\compare')) {
    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    function compare($a, $b)
    {
        return ParagonIE_Sodium_Compat::compare($a, $b);
    }
}
if (!is_callable('\\Sodium\\crypto_aead_aes256gcm_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string|bool
     */
    function crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_aead_aes256gcm_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_aead_aes256gcm_is_available')) {
    /**
     * @return bool
     */
    function crypto_aead_aes256gcm_is_available()
    {
        return ParagonIE_Sodium_Compat::crypto_aead_aes256gcm_is_available();
    }
}
if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string|bool
     */
    function crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_ietf_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string|bool
     */
    function crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_ietf_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_auth')) {
    /**
     * @param string $message
     * @param string $key
     * @return string
     */
    function crypto_auth($message, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_auth($message, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_auth_verify')) {
    /**
     * @param string $mac
     * @param string $message
     * @param string $key
     * @return bool
     */
    function crypto_auth_verify($mac, $message, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_auth_verify($mac, $message, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_box')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $kp
     * @return string
     */
    function crypto_box($message, $nonce, $kp)
    {
        return ParagonIE_Sodium_Compat::crypto_box($message, $nonce, $kp);
    }
}
if (!is_callable('\\Sodium\\crypto_box_keypair')) {
    /**
     * @return string
     */
    function crypto_box_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_box_keypair();
    }
}
if (!is_callable('\\Sodium\\crypto_box_keypair_from_secretkey_and_publickey')) {
    /**
     * @param string $sk
     * @param string $pk
     * @return string
     */
    function crypto_box_keypair_from_secretkey_and_publickey($sk, $pk)
    {
        return ParagonIE_Sodium_Compat::crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);
    }
}
if (!is_callable('\\Sodium\\crypto_box_open')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $kp
     * @return string|bool
     */
    function crypto_box_open($message, $nonce, $kp)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_box_open($message, $nonce, $kp);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_box_publickey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function crypto_box_publickey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_box_publickey($keypair);
    }
}
if (!is_callable('\\Sodium\\crypto_box_publickey_from_secretkey')) {
    /**
     * @param string $sk
     * @return string
     */
    function crypto_box_publickey_from_secretkey($sk)
    {
        return ParagonIE_Sodium_Compat::crypto_box_publickey_from_secretkey($sk);
    }
}
if (!is_callable('\\Sodium\\crypto_box_seal')) {
    /**
     * @param string $message
     * @param string $publicKey
     * @return string
     */
    function crypto_box_seal($message, $publicKey)
    {
        return ParagonIE_Sodium_Compat::crypto_box_seal($message, $publicKey);
    }
}
if (!is_callable('\\Sodium\\crypto_box_seal_open')) {
    /**
     * @param string $message
     * @param string $kp
     * @return string|bool
     */
    function crypto_box_seal_open($message, $kp)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_box_seal_open($message, $kp);
        } catch (\Error $ex) {
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_box_secretkey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function crypto_box_secretkey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_box_secretkey($keypair);
    }
}
if (!is_callable('\\Sodium\\crypto_generichash')) {
    /**
     * @param string $message
     * @param string|null $key
     * @param int $outLen
     * @return string
     */
    function crypto_generichash($message, $key = null, $outLen = 32)
    {
        return ParagonIE_Sodium_Compat::crypto_generichash($message, $key, $outLen);
    }
}
if (!is_callable('\\Sodium\\crypto_generichash_final')) {
    /**
     * @param string|null $ctx
     * @param int $outputLength
     * @return string
     */
    function crypto_generichash_final(&$ctx, $outputLength = 32)
    {
        return ParagonIE_Sodium_Compat::crypto_generichash_final($ctx, $outputLength);
    }
}
if (!is_callable('\\Sodium\\crypto_generichash_init')) {
    /**
     * @param string|null $key
     * @param int $outLen
     * @return string
     */
    function crypto_generichash_init($key = null, $outLen = 32)
    {
        return ParagonIE_Sodium_Compat::crypto_generichash_init($key, $outLen);
    }
}
if (!is_callable('\\Sodium\\crypto_generichash_update')) {
    /**
     * @param string|null $ctx
     * @param string $message
     * @return void
     */
    function crypto_generichash_update(&$ctx, $message = '')
    {
        ParagonIE_Sodium_Compat::crypto_generichash_update($ctx, $message);
    }
}
if (!is_callable('\\Sodium\\crypto_kx')) {
    /**
     * @param string $my_secret
     * @param string $their_public
     * @param string $client_public
     * @param string $server_public
     * @return string
     */
    function crypto_kx($my_secret, $their_public, $client_public, $server_public)
    {
        return ParagonIE_Sodium_Compat::crypto_kx(
            $my_secret,
            $their_public,
            $client_public,
            $server_public
        );
    }
}
if (!is_callable('\\Sodium\\crypto_pwhash')) {
    /**
     * @param int $outlen
     * @param string $passwd
     * @param string $salt
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit);
    }
}
if (!is_callable('\\Sodium\\crypto_pwhash_str')) {
    /**
     * @param string $passwd
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function crypto_pwhash_str($passwd, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_str($passwd, $opslimit, $memlimit);
    }
}
if (!is_callable('\\Sodium\\crypto_pwhash_str_verify')) {
    /**
     * @param string $passwd
     * @param string $hash
     * @return bool
     */
    function crypto_pwhash_str_verify($passwd, $hash)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_str_verify($passwd, $hash);
    }
}
if (!is_callable('\\Sodium\\crypto_pwhash_scryptsalsa208sha256')) {
    /**
     * @param int $outlen
     * @param string $passwd
     * @param string $salt
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit);
    }
}
if (!is_callable('\\Sodium\\crypto_pwhash_scryptsalsa208sha256_str')) {
    /**
     * @param string $passwd
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
    }
}
if (!is_callable('\\Sodium\\crypto_pwhash_scryptsalsa208sha256_str_verify')) {
    /**
     * @param string $passwd
     * @param string $hash
     * @return bool
     */
    function crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash);
    }
}
if (!is_callable('\\Sodium\\crypto_scalarmult')) {
    /**
     * @param string $n
     * @param string $p
     * @return string
     */
    function crypto_scalarmult($n, $p)
    {
        return ParagonIE_Sodium_Compat::crypto_scalarmult($n, $p);
    }
}
if (!is_callable('\\Sodium\\crypto_scalarmult_base')) {
    /**
     * @param string $n
     * @return string
     */
    function crypto_scalarmult_base($n)
    {
        return ParagonIE_Sodium_Compat::crypto_scalarmult_base($n);
    }
}
if (!is_callable('\\Sodium\\crypto_secretbox')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function crypto_secretbox($message, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_secretbox($message, $nonce, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_secretbox_open')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $key
     * @return string|bool
     */
    function crypto_secretbox_open($message, $nonce, $key)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_secretbox_open($message, $nonce, $key);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_shorthash')) {
    /**
     * @param string $message
     * @param string $key
     * @return string
     */
    function crypto_shorthash($message, $key = '')
    {
        return ParagonIE_Sodium_Compat::crypto_shorthash($message, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_sign')) {
    /**
     * @param string $message
     * @param string $sk
     * @return string
     */
    function crypto_sign($message, $sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign($message, $sk);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_detached')) {
    /**
     * @param string $message
     * @param string $sk
     * @return string
     */
    function crypto_sign_detached($message, $sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_detached($message, $sk);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_keypair')) {
    /**
     * @return string
     */
    function crypto_sign_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_sign_keypair();
    }
}
if (!is_callable('\\Sodium\\crypto_sign_open')) {
    /**
     * @param string $signedMessage
     * @param string $pk
     * @return string|bool
     */
    function crypto_sign_open($signedMessage, $pk)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_sign_open($signedMessage, $pk);
        } catch (\Error $ex) {
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('\\Sodium\\crypto_sign_publickey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function crypto_sign_publickey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_publickey($keypair);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_publickey_from_secretkey')) {
    /**
     * @param string $sk
     * @return string
     */
    function crypto_sign_publickey_from_secretkey($sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_publickey_from_secretkey($sk);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_secretkey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function crypto_sign_secretkey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_secretkey($keypair);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_seed_keypair')) {
    /**
     * @param string $seed
     * @return string
     */
    function crypto_sign_seed_keypair($seed)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_seed_keypair($seed);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_verify_detached')) {
    /**
     * @param string $signature
     * @param string $message
     * @param string $pk
     * @return bool
     */
    function crypto_sign_verify_detached($signature, $message, $pk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_verify_detached($signature, $message, $pk);
    }
}
if (!is_callable('\\Sodium\\crypto_sign_ed25519_sk_to_curve25519')) {
    /**
     * @param string $sk
     * @return string
     */
    function crypto_sign_ed25519_sk_to_curve25519($sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_ed25519_sk_to_curve25519($sk);
    }
}
if (!is_callable('\\Sodium\\crypto_stream')) {
    /**
     * @param int $len
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function crypto_stream($len, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_stream($len, $nonce, $key);
    }
}
if (!is_callable('\\Sodium\\crypto_stream_xor')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function crypto_stream_xor($message, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_stream_xor($message, $nonce, $key);
    }
}
if (!is_callable('\\Sodium\\hex2bin')) {
    /**
     * @param string $string
     * @return string
     */
    function hex2bin($string)
    {
        return ParagonIE_Sodium_Compat::hex2bin($string);
    }
}
if (!is_callable('\\Sodium\\memcmp')) {
    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    function memcmp($a, $b)
    {
        return ParagonIE_Sodium_Compat::memcmp($a, $b);
    }
}
if (!is_callable('\\Sodium\\memzero')) {
    /**
     * @param string $str
     * @return void
     */
    function memzero(&$str)
    {
        ParagonIE_Sodium_Compat::memzero($str);
    }
}
if (!is_callable('\\Sodium\\randombytes_buf')) {
    /**
     * @param int $amount
     * @return string
     */
    function randombytes_buf($amount)
    {
        return ParagonIE_Sodium_Compat::randombytes_buf($amount);
    }
}

if (!is_callable('\\Sodium\\randombytes_uniform')) {
    /**
     * @param int $upperLimit
     * @return int
     */
    function randombytes_uniform($upperLimit)
    {
        return ParagonIE_Sodium_Compat::randombytes_uniform($upperLimit);
    }
}

if (!is_callable('\\Sodium\\randombytes_random16')) {
    /**
     * @return int
     */
    function randombytes_random16()
    {
        return ParagonIE_Sodium_Compat::randombytes_random16();
    }
}

if (!defined('\\Sodium\\CRYPTO_AUTH_BYTES')) {
    require_once dirname(__FILE__) . '/constants.php';
}
