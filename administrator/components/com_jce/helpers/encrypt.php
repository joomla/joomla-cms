<?php

/**
 * @copyright Copyright (c)2018 Ryan Demmer
 * @license GNU General Public License version 3, or later
 *
 * @since 2.7
 */
// Protection against direct access
defined('JPATH_PLATFORM') or die();

use Defuse\Crypto\Key;
use Defuse\Crypto\Encoding;
use Defuse\Crypto\Crypto;

/**
 * Implements encrypted settings handling features.
 */
class JceEncryptHelper
{
    protected static function generateKey()
    {        
        $keyObject = Key::createNewRandomKey();
        $keyAscii = $keyObject->saveToAsciiSafeString();

        $keyData = Encoding::binToHex($keyAscii);

        $filecontents = "<?php defined('WF_EDITOR') or die(); define('WF_SERVERKEY', '$keyData'); ?>";
        $filename     = JPATH_ADMINISTRATOR . '/components/com_jce/serverkey.php';

        file_put_contents($filename, $filecontents);

        return Key::loadFromAsciiSafeString($keyAscii);
    }

    /**
     * Gets the configured server key, automatically loading the server key storage file
     * if required.
     *
     * @return string
     */
    public static function getKey($legacy = false)
    {
        if (!defined('WF_SERVERKEY')) {
            $filename = JPATH_ADMINISTRATOR . '/components/com_jce/serverkey.php';

            if (is_file($filename)) {
                include_once($filename);
            }
        }

        if (defined('WF_SERVERKEY')) {
            // return key as string
            if ($legacy) {
                $key = base64_decode(WF_SERVERKEY);
                return $key;
            }

            try {
                $keyAscii = Encoding::hexToBin(WF_SERVERKEY);
                $key = Key::loadFromAsciiSafeString($keyAscii);
            } catch(Defuse\Crypto\Exception\BadFormatException $ex) {
                return "";
            }

            return $key;
        }

        return self::generateKey();
    }

    /**
     * Encrypts the settings using the automatically detected preferred algorithm.
     *
     * @param $settingsINI string The raw settings INI string
     *
     * @return string The encrypted data to store in the database
     */
    public static function encrypt($data, $key = null)
    {
        // Do we have a non-empty key to begin with?
        if (empty($key)) {
            $key = self::getKey();
        }

        if (empty($key)) {
            return $data;
        }

        $encrypted = Crypto::encrypt($data, $key);

        // base64encode
        $encoded = base64_encode($encrypted);

        // add marker
        $data = '###DEFUSE###' . $encoded;

        return $data;
    }

    /**
     * Decrypts the encrypted settings and returns the plaintext INI string.
     *
     * @param $encrypted string The encrypted data
     *
     * @return string The decrypted data
     */
    public static function decrypt($encrypted, $key = null)
    {
        $mode = substr($encrypted, 0, 12);

        if ($mode == '###AES128###' || $mode == '###CTR128###') {
            require_once(__DIR__ . '/encrypt/aes.php');
            
            $encrypted = substr($encrypted, 12);

            $key = self::getKey(true);

            switch ($mode) {
                case '###AES128###':
                    $encrypted = base64_decode($encrypted);
                    $decrypted = @WFUtilEncrypt::AESDecryptCBC($encrypted, $key, 128);
                    break;
    
                case '###CTR128###':
                    $decrypted = @WFUtilEncrypt::AESDecryptCtr($encrypted, $key, 128);
                    break;
            }

            return rtrim($decrypted, "\0");
        }

        if ($mode == '###DEFUSE###') {
            $key = self::getKey();

            if (empty($key)) {
                return $encrypted;
            }

            //get encrypted string without marker
            $encrypted = substr($encrypted, 12);
            
            // base64decode
            $decoded = base64_decode($encrypted);

            try {
                $decrypted = Crypto::decrypt($decoded, $key);
            } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
                return $encrypted;
            }

            return rtrim($decrypted, "\0");
        }

        return $encrypted;
    }
}
