<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Encrypt\AES;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract AES encryption class
 *
 * @since    4.0.0
 */
abstract class AbstractAES
{
    /**
     * Trims or zero-pads a key / IV
     *
     * @param   string $key  The key or IV to treat
     * @param   int    $size The block size of the currently used algorithm
     *
     * @return  null|string  Null if $key is null, treated string of $size byte length otherwise
     */
    public function resizeKey($key, $size)
    {
        if (empty($key)) {
            return null;
        }

        $keyLength = \strlen($key);

        if (\function_exists('mb_strlen')) {
            $keyLength = mb_strlen($key, 'ASCII');
        }

        if ($keyLength == $size) {
            return $key;
        }

        if ($keyLength > $size) {
            if (\function_exists('mb_substr')) {
                return mb_substr($key, 0, $size, 'ASCII');
            }

            return substr($key, 0, $size);
        }

        return $key . str_repeat("\0", ($size - $keyLength));
    }

    /**
     * Returns null bytes to append to the string so that it's zero padded to the specified block size
     *
     * @param   string $string    The binary string which will be zero padded
     * @param   int    $blockSize The block size
     *
     * @return  string  The zero bytes to append to the string to zero pad it to $blockSize
     */
    protected function getZeroPadding($string, $blockSize)
    {
        $stringSize = \strlen($string);

        if (\function_exists('mb_strlen')) {
            $stringSize = mb_strlen($string, 'ASCII');
        }

        if ($stringSize == $blockSize) {
            return '';
        }

        if ($stringSize < $blockSize) {
            return str_repeat("\0", $blockSize - $stringSize);
        }

        $paddingBytes = $stringSize % $blockSize;

        return str_repeat("\0", $blockSize - $paddingBytes);
    }
}
