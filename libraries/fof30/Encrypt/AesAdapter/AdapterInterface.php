<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Encrypt\AesAdapter;

defined('_JEXEC') || die;

use FOF30\Utils\Phpfunc;

/**
 * Interface for AES encryption adapters
 */
interface AdapterInterface
{
	/**
	 * Sets the AES encryption mode.
	 *
	 * WARNING: The strength parameter is deprecated since FOF 3.1 and has no effect.
	 *
	 * @param   string  $mode      Choose between CBC (recommended) or ECB
	 * @param   int     $strength  DEPRECATED AND UNUSED.
	 *
	 * @return  mixed
	 */
	public function setEncryptionMode($mode = 'cbc', $strength = 128);

	/**
	 * Encrypts a string. Returns the raw binary ciphertext.
	 *
	 * WARNING: The plaintext is zero-padded to the algorithm's block size. You are advised to store the size of the
	 * plaintext and trim the string to that length upon decryption.
	 *
	 * @param   string       $plainText  The plaintext to encrypt
	 * @param   string       $key        The raw binary key (will be zero-padded or chopped if its size is different
	 *                                   than the block size)
	 * @param   null|string  $iv         The initialization vector (for CBC mode algorithms)
	 *
	 * @return  string  The raw encrypted binary string.
	 */
	public function encrypt($plainText, $key, $iv = null);

	/**
	 * Decrypts a string. Returns the raw binary plaintext.
	 *
	 * $ciphertext MUST start with the IV followed by the ciphertext, even for EBC data (the first block of data is
	 * dropped in EBC mode since there is no concept of IV in EBC).
	 *
	 * WARNING: The returned plaintext is zero-padded to the algorithm's block size during encryption. You are advised
	 * to trim the string to the original plaintext's length upon decryption. While rtrim($decrypted, "\0") sounds
	 * appealing it's NOT the correct approach for binary data (zero bytes may actually be part of your plaintext, not
	 * just padding!).
	 *
	 * @param   string  $cipherText  The ciphertext to encrypt
	 * @param   string  $key         The raw binary key (will be zero-padded or chopped if its size is different than
	 *                               the block size)
	 *
	 * @return  string  The raw unencrypted binary string.
	 */
	public function decrypt($cipherText, $key);

	/**
	 * Returns the encryption block size in bytes
	 *
	 * @return  int
	 */
	public function getBlockSize();

	/**
	 * Is this adapter supported?
	 *
	 * @return  bool
	 */
	public function isSupported(Phpfunc $phpfunc = null);
}
