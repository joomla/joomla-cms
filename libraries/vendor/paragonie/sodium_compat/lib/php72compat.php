<?php

/**
 * This file will monkey patch the pure-PHP implementation in place of the
 * PECL functions and constants, but only if they do not already exist.
 *
 * Thus, the functions or constants just proxy to the appropriate
 * ParagonIE_Sodium_Compat method or class constant, respectively.
 */
foreach (array(
    'CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_NSECBYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_ABYTES',
    'CRYPTO_AEAD_AES256GCM_KEYBYTES',
    'CRYPTO_AEAD_AES256GCM_NSECBYTES',
    'CRYPTO_AEAD_AES256GCM_NPUBBYTES',
    'CRYPTO_AEAD_AES256GCM_ABYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_IETF_NSECBYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES',
    'CRYPTO_AEAD_CHACHA20POLY1305_IETF_ABYTES',
    'CRYPTO_AUTH_BYTES',
    'CRYPTO_AUTH_KEYBYTES',
    'CRYPTO_BOX_SEALBYTES',
    'CRYPTO_BOX_SECRETKEYBYTES',
    'CRYPTO_BOX_PUBLICKEYBYTES',
    'CRYPTO_BOX_KEYPAIRBYTES',
    'CRYPTO_BOX_MACBYTES',
    'CRYPTO_BOX_NONCEBYTES',
    'CRYPTO_BOX_SEEDBYTES',
    'CRYPTO_KX_BYTES',
    'CRYPTO_KX_PUBLICKEYBYTES',
    'CRYPTO_KX_SECRETKEYBYTES',
    'CRYPTO_GENERICHASH_BYTES',
    'CRYPTO_GENERICHASH_BYTES_MIN',
    'CRYPTO_GENERICHASH_BYTES_MAX',
    'CRYPTO_GENERICHASH_KEYBYTES',
    'CRYPTO_GENERICHASH_KEYBYTES_MIN',
    'CRYPTO_GENERICHASH_KEYBYTES_MAX',
    'CRYPTO_PWHASH_SALTBYTES',
    'CRYPTO_PWHASH_STRPREFIX',
    'CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE',
    'CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE',
    'CRYPTO_PWHASH_MEMLIMIT_MODERATE',
    'CRYPTO_PWHASH_OPSLIMIT_MODERATE',
    'CRYPTO_PWHASH_MEMLIMIT_SENSITIVE',
    'CRYPTO_PWHASH_OPSLIMIT_SENSITIVE',
    'CRYPTO_SCALARMULT_BYTES',
    'CRYPTO_SCALARMULT_SCALARBYTES',
    'CRYPTO_SHORTHASH_BYTES',
    'CRYPTO_SHORTHASH_KEYBYTES',
    'CRYPTO_SECRETBOX_KEYBYTES',
    'CRYPTO_SECRETBOX_MACBYTES',
    'CRYPTO_SECRETBOX_NONCEBYTES',
    'CRYPTO_SIGN_BYTES',
    'CRYPTO_SIGN_SEEDBYTES',
    'CRYPTO_SIGN_PUBLICKEYBYTES',
    'CRYPTO_SIGN_SECRETKEYBYTES',
    'CRYPTO_SIGN_KEYPAIRBYTES',
    'CRYPTO_STREAM_KEYBYTES',
    'CRYPTO_STREAM_NONCEBYTES',
    ) as $constant
) {
    if (!defined("SODIUM_$constant")) {
        define("SODIUM_$constant", constant("ParagonIE_Sodium_Compat::$constant"));
    }
}

if (!is_callable('sodium_bin2hex')) {
    /**
     * @param string $string
     * @return string
     */
    function sodium_bin2hex($string)
    {
        return ParagonIE_Sodium_Compat::bin2hex($string);
    }
}
if (!is_callable('sodium_compare')) {
    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    function sodium_compare($a, $b)
    {
        return ParagonIE_Sodium_Compat::compare($a, $b);
    }
}
if (!is_callable('sodium_crypto_aead_aes256gcm_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key)
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
if (!is_callable('sodium_crypto_aead_aes256gcm_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('sodium_crypto_aead_aes256gcm_is_available')) {
    /**
     * @return bool
     */
    function sodium_crypto_aead_aes256gcm_is_available()
    {
        return ParagonIE_Sodium_Compat::crypto_aead_aes256gcm_is_available();
    }
}
if (!is_callable('sodium_crypto_aead_chacha20poly1305_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key)
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
if (!is_callable('sodium_crypto_aead_chacha20poly1305_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('sodium_crypto_aead_chacha20poly1305_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_aead_chacha20poly1305_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_keygen();
    }
}
if (!is_callable('sodium_crypto_aead_chacha20poly1305_ietf_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key)
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
if (!is_callable('sodium_crypto_aead_chacha20poly1305_ietf_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('sodium_crypto_aead_chacha20poly1305_ietf_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_aead_chacha20poly1305_ietf_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_aead_chacha20poly1305_ietf_keygen();
    }
}
if (!is_callable('sodium_crypto_aead_xchacha20poly1305_ietf_decrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_aead_xchacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')) {
    /**
     * @param string $message
     * @param string $assocData
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_aead_xchacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
    }
}
if (!is_callable('sodium_crypto_aead_xchacha20poly1305_ietf_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_aead_xchacha20poly1305_ietf_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_aead_xchacha20poly1305_ietf_keygen();
    }
}
if (!is_callable('sodium_crypto_auth')) {
    /**
     * @param string $message
     * @param string $key
     * @return string
     */
    function sodium_crypto_auth($message, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_auth($message, $key);
    }
}
if (!is_callable('sodium_crypto_auth_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_auth_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_auth_keygen();
    }
}
if (!is_callable('sodium_crypto_auth_verify')) {
    /**
     * @param string $mac
     * @param string $message
     * @param string $key
     * @return bool
     */
    function sodium_crypto_auth_verify($mac, $message, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_auth_verify($mac, $message, $key);
    }
}
if (!is_callable('sodium_crypto_box')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $kp
     * @return string
     */
    function sodium_crypto_box($message, $nonce, $kp)
    {
        return ParagonIE_Sodium_Compat::crypto_box($message, $nonce, $kp);
    }
}
if (!is_callable('sodium_crypto_box_keypair')) {
    /**
     * @return string
     */
    function sodium_crypto_box_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_box_keypair();
    }
}
if (!is_callable('sodium_crypto_box_keypair_from_secretkey_and_publickey')) {
    /**
     * @param string $sk
     * @param string $pk
     * @return string
     */
    function sodium_crypto_box_keypair_from_secretkey_and_publickey($sk, $pk)
    {
        return ParagonIE_Sodium_Compat::crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);
    }
}
if (!is_callable('sodium_crypto_box_open')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $kp
     * @return string|bool
     */
    function sodium_crypto_box_open($message, $nonce, $kp)
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
if (!is_callable('sodium_crypto_box_publickey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function sodium_crypto_box_publickey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_box_publickey($keypair);
    }
}
if (!is_callable('sodium_crypto_box_publickey_from_secretkey')) {
    /**
     * @param string $sk
     * @return string
     */
    function sodium_crypto_box_publickey_from_secretkey($sk)
    {
        return ParagonIE_Sodium_Compat::crypto_box_publickey_from_secretkey($sk);
    }
}
if (!is_callable('sodium_crypto_box_seal')) {
    /**
     * @param string $message
     * @param string $publicKey
     * @return string
     */
    function sodium_crypto_box_seal($message, $publicKey)
    {
        return ParagonIE_Sodium_Compat::crypto_box_seal($message, $publicKey);
    }
}
if (!is_callable('sodium_crypto_box_seal_open')) {
    /**
     * @param string $message
     * @param string $kp
     * @return string|bool
     */
    function sodium_crypto_box_seal_open($message, $kp)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_box_seal_open($message, $kp);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('sodium_crypto_box_secretkey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function sodium_crypto_box_secretkey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_box_secretkey($keypair);
    }
}
if (!is_callable('sodium_crypto_box_seed_keypair')) {
    /**
     * @param string $seed
     * @return string
     */
    function sodium_crypto_box_seed_keypair($seed)
    {
        return ParagonIE_Sodium_Compat::crypto_box_seed_keypair($seed);
    }
}
if (!is_callable('sodium_crypto_generichash')) {
    /**
     * @param string $message
     * @param string|null $key
     * @param int $outLen
     * @return string
     */
    function sodium_crypto_generichash($message, $key = null, $outLen = 32)
    {
        return ParagonIE_Sodium_Compat::crypto_generichash($message, $key, $outLen);
    }
}
if (!is_callable('sodium_crypto_generichash_final')) {
    /**
     * @param string|null $ctx
     * @param int $outputLength
     * @return string
     */
    function sodium_crypto_generichash_final(&$ctx, $outputLength = 32)
    {
        return ParagonIE_Sodium_Compat::crypto_generichash_final($ctx, $outputLength);
    }
}
if (!is_callable('sodium_crypto_generichash_init')) {
    /**
     * @param string|null $key
     * @param int $outLen
     * @return string
     */
    function sodium_crypto_generichash_init($key = null, $outLen = 32)
    {
        return ParagonIE_Sodium_Compat::crypto_generichash_init($key, $outLen);
    }
}
if (!is_callable('sodium_crypto_generichash_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_generichash_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_generichash_keygen();
    }
}
if (!is_callable('sodium_crypto_generichash_update')) {
    /**
     * @param string|null $ctx
     * @param string $message
     * @return void
     */
    function sodium_crypto_generichash_update(&$ctx, $message = '')
    {
        ParagonIE_Sodium_Compat::crypto_generichash_update($ctx, $message);
    }
}
if (!is_callable('sodium_crypto_kx')) {
    /**
     * @param string $my_secret
     * @param string $their_public
     * @param string $client_public
     * @param string $server_public
     * @return string
     */
    function sodium_crypto_kx($my_secret, $their_public, $client_public, $server_public)
    {
        return ParagonIE_Sodium_Compat::crypto_kx(
            $my_secret,
            $their_public,
            $client_public,
            $server_public
        );
    }
}
if (!is_callable('sodium_crypto_pwhash')) {
    /**
     * @param int $outlen
     * @param string $passwd
     * @param string $salt
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function sodium_crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit);
    }
}
if (!is_callable('sodium_crypto_pwhash_str')) {
    /**
     * @param string $passwd
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function sodium_crypto_pwhash_str($passwd, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_str($passwd, $opslimit, $memlimit);
    }
}
if (!is_callable('sodium_crypto_pwhash_str_verify')) {
    /**
     * @param string $passwd
     * @param string $hash
     * @return bool
     */
    function sodium_crypto_pwhash_str_verify($passwd, $hash)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_str_verify($passwd, $hash);
    }
}
if (!is_callable('sodium_crypto_pwhash_scryptsalsa208sha256')) {
    /**
     * @param int $outlen
     * @param string $passwd
     * @param string $salt
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function sodium_crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit);
    }
}
if (!is_callable('sodium_crypto_pwhash_scryptsalsa208sha256_str')) {
    /**
     * @param string $passwd
     * @param int $opslimit
     * @param int $memlimit
     * @return string
     */
    function sodium_crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
    }
}
if (!is_callable('sodium_crypto_pwhash_scryptsalsa208sha256_str_verify')) {
    /**
     * @param string $passwd
     * @param string $hash
     * @return bool
     */
    function sodium_crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash)
    {
        return ParagonIE_Sodium_Compat::crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash);
    }
}
if (!is_callable('sodium_crypto_scalarmult')) {
    /**
     * @param string $n
     * @param string $p
     * @return string
     */
    function sodium_crypto_scalarmult($n, $p)
    {
        return ParagonIE_Sodium_Compat::crypto_scalarmult($n, $p);
    }
}
if (!is_callable('sodium_crypto_scalarmult_base')) {
    /**
     * @param string $n
     * @return string
     */
    function sodium_crypto_scalarmult_base($n)
    {
        return ParagonIE_Sodium_Compat::crypto_scalarmult_base($n);
    }
}
if (!is_callable('sodium_crypto_secretbox')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_secretbox($message, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_secretbox($message, $nonce, $key);
    }
}
if (!is_callable('sodium_crypto_secretbox_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_secretbox_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_secretbox_keygen();
    }
}
if (!is_callable('sodium_crypto_secretbox_open')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $key
     * @return string|bool
     */
    function sodium_crypto_secretbox_open($message, $nonce, $key)
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
if (!is_callable('sodium_crypto_shorthash')) {
    /**
     * @param string $message
     * @param string $key
     * @return string
     */
    function sodium_crypto_shorthash($message, $key = '')
    {
        return ParagonIE_Sodium_Compat::crypto_shorthash($message, $key);
    }
}
if (!is_callable('sodium_crypto_shorthash_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_shorthash_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_shorthash_keygen();
    }
}
if (!is_callable('sodium_crypto_sign')) {
    /**
     * @param string $message
     * @param string $sk
     * @return string
     */
    function sodium_crypto_sign($message, $sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign($message, $sk);
    }
}
if (!is_callable('sodium_crypto_sign_detached')) {
    /**
     * @param string $message
     * @param string $sk
     * @return string
     */
    function sodium_crypto_sign_detached($message, $sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_detached($message, $sk);
    }
}
if (!is_callable('sodium_crypto_sign_keypair')) {
    /**
     * @return string
     */
    function sodium_crypto_sign_keypair()
    {
        return ParagonIE_Sodium_Compat::crypto_sign_keypair();
    }
}
if (!is_callable('sodium_crypto_sign_open')) {
    /**
     * @param string $signedMessage
     * @param string $pk
     * @return string|bool
     */
    function sodium_crypto_sign_open($signedMessage, $pk)
    {
        try {
            return ParagonIE_Sodium_Compat::crypto_sign_open($signedMessage, $pk);
        } catch (Error $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!is_callable('sodium_crypto_sign_publickey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function sodium_crypto_sign_publickey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_publickey($keypair);
    }
}
if (!is_callable('sodium_crypto_sign_publickey_from_secretkey')) {
    /**
     * @param string $sk
     * @return string
     */
    function sodium_crypto_sign_publickey_from_secretkey($sk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_publickey_from_secretkey($sk);
    }
}
if (!is_callable('sodium_crypto_sign_secretkey')) {
    /**
     * @param string $keypair
     * @return string
     */
    function sodium_crypto_sign_secretkey($keypair)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_secretkey($keypair);
    }
}
if (!is_callable('sodium_crypto_sign_seed_keypair')) {
    /**
     * @param string $seed
     * @return string
     */
    function sodium_crypto_sign_seed_keypair($seed)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_seed_keypair($seed);
    }
}
if (!is_callable('sodium_crypto_sign_verify_detached')) {
    /**
     * @param string $signature
     * @param string $message
     * @param string $pk
     * @return bool
     */
    function sodium_crypto_sign_verify_detached($signature, $message, $pk)
    {
        return ParagonIE_Sodium_Compat::crypto_sign_verify_detached($signature, $message, $pk);
    }
}
if (!is_callable('sodium_crypto_stream')) {
    /**
     * @param int $len
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_stream($len, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_stream($len, $nonce, $key);
    }
}
if (!is_callable('sodium_crypto_stream_keygen')) {
    /**
     * @return string
     */
    function sodium_crypto_stream_keygen()
    {
        return ParagonIE_Sodium_Compat::crypto_stream_keygen();
    }
}
if (!is_callable('sodium_crypto_stream_xor')) {
    /**
     * @param string $message
     * @param string $nonce
     * @param string $key
     * @return string
     */
    function sodium_crypto_stream_xor($message, $nonce, $key)
    {
        return ParagonIE_Sodium_Compat::crypto_stream_xor($message, $nonce, $key);
    }
}
if (!is_callable('sodium_hex2bin')) {
    /**
     * @param string $string
     * @return string
     */
    function sodium_hex2bin($string)
    {
        return ParagonIE_Sodium_Compat::hex2bin($string);
    }
}
if (!is_callable('sodium_increment')) {
    /**
     * @param &string $string
     * @return void
     */
    function sodium_increment(&$string)
    {
        ParagonIE_Sodium_Compat::increment($string);
    }
}
if (!is_callable('sodium_library_version_major')) {
    /**
     * @return int
     */
    function sodium_library_version_major()
    {
        return ParagonIE_Sodium_Compat::library_version_major();
    }
}
if (!is_callable('sodium_library_version_minor')) {
    /**
     * @return int
     */
    function sodium_library_version_minor()
    {
        return ParagonIE_Sodium_Compat::library_version_minor();
    }
}
if (!is_callable('sodium_version_string')) {
    /**
     * @return string
     */
    function sodium_version_string()
    {
        return ParagonIE_Sodium_Compat::version_string();
    }
}
if (!is_callable('sodium_memcmp')) {
    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    function sodium_memcmp($a, $b)
    {
        return ParagonIE_Sodium_Compat::memcmp($a, $b);
    }
}
if (!is_callable('sodium_memzero')) {
    /**
     * @param string &$str
     * @return void
     */
    function sodium_memzero(&$str)
    {
        ParagonIE_Sodium_Compat::memzero($str);
    }
}
if (!is_callable('sodium_randombytes_buf')) {
    /**
     * @param int $amount
     * @return string
     */
    function sodium_randombytes_buf($amount)
    {
        return ParagonIE_Sodium_Compat::randombytes_buf($amount);
    }
}

if (!is_callable('sodium_randombytes_uniform')) {
    /**
     * @param int $upperLimit
     * @return int
     */
    function sodium_randombytes_uniform($upperLimit)
    {
        return ParagonIE_Sodium_Compat::randombytes_uniform($upperLimit);
    }
}

if (!is_callable('sodium_randombytes_random16')) {
    /**
     * @return int
     */
    function sodium_randombytes_random16()
    {
        return ParagonIE_Sodium_Compat::randombytes_random16();
    }
}
