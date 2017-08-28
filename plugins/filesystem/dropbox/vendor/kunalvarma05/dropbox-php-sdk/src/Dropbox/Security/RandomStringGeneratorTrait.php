<?php
namespace Kunnu\Dropbox\Security;

trait RandomStringGeneratorTrait
{
    /**
     * Converts binary data to hexadecimal of given length
     *
     * @param string $binaryData The binary data to convert to hex.
     * @param int    $length     The length of the string to return.
     *
     * @throws \RuntimeException Throws an exception when multibyte support is not enabled
     *
     * @return string
     */
    public function binToHex($binaryData, $length)
    {
        return substr(bin2hex($binaryData), 0, $length);
    }
}
