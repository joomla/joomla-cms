<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Util\AesAdapter\AdapterInterface;
use Akeeba\Engine\Util\AesAdapter\Mcrypt;
use Akeeba\Engine\Util\AesAdapter\OpenSSL;

/**
 * AES implementation in PHP (c) Chris Veness 2005-2016.
 * Right to use and adapt is granted for under a simple creative commons attribution
 * licence. No warranty of any form is offered.
 *
 * Heavily modified for Akeeba Backup by Nicholas K. Dionysopoulos
 * Also added AES-128 CBC mode (with mcrypt and OpenSSL) on top of AES CTR
 */
class Encrypt
{
	// Sbox is pre-computed multiplicative inverse in GF(2^8) used in SubBytes and KeyExpansion [�5.1.1]
	protected $Sbox =
		[
			0x63, 0x7c, 0x77, 0x7b, 0xf2, 0x6b, 0x6f, 0xc5, 0x30, 0x01, 0x67, 0x2b, 0xfe, 0xd7, 0xab, 0x76,
			0xca, 0x82, 0xc9, 0x7d, 0xfa, 0x59, 0x47, 0xf0, 0xad, 0xd4, 0xa2, 0xaf, 0x9c, 0xa4, 0x72, 0xc0,
			0xb7, 0xfd, 0x93, 0x26, 0x36, 0x3f, 0xf7, 0xcc, 0x34, 0xa5, 0xe5, 0xf1, 0x71, 0xd8, 0x31, 0x15,
			0x04, 0xc7, 0x23, 0xc3, 0x18, 0x96, 0x05, 0x9a, 0x07, 0x12, 0x80, 0xe2, 0xeb, 0x27, 0xb2, 0x75,
			0x09, 0x83, 0x2c, 0x1a, 0x1b, 0x6e, 0x5a, 0xa0, 0x52, 0x3b, 0xd6, 0xb3, 0x29, 0xe3, 0x2f, 0x84,
			0x53, 0xd1, 0x00, 0xed, 0x20, 0xfc, 0xb1, 0x5b, 0x6a, 0xcb, 0xbe, 0x39, 0x4a, 0x4c, 0x58, 0xcf,
			0xd0, 0xef, 0xaa, 0xfb, 0x43, 0x4d, 0x33, 0x85, 0x45, 0xf9, 0x02, 0x7f, 0x50, 0x3c, 0x9f, 0xa8,
			0x51, 0xa3, 0x40, 0x8f, 0x92, 0x9d, 0x38, 0xf5, 0xbc, 0xb6, 0xda, 0x21, 0x10, 0xff, 0xf3, 0xd2,
			0xcd, 0x0c, 0x13, 0xec, 0x5f, 0x97, 0x44, 0x17, 0xc4, 0xa7, 0x7e, 0x3d, 0x64, 0x5d, 0x19, 0x73,
			0x60, 0x81, 0x4f, 0xdc, 0x22, 0x2a, 0x90, 0x88, 0x46, 0xee, 0xb8, 0x14, 0xde, 0x5e, 0x0b, 0xdb,
			0xe0, 0x32, 0x3a, 0x0a, 0x49, 0x06, 0x24, 0x5c, 0xc2, 0xd3, 0xac, 0x62, 0x91, 0x95, 0xe4, 0x79,
			0xe7, 0xc8, 0x37, 0x6d, 0x8d, 0xd5, 0x4e, 0xa9, 0x6c, 0x56, 0xf4, 0xea, 0x65, 0x7a, 0xae, 0x08,
			0xba, 0x78, 0x25, 0x2e, 0x1c, 0xa6, 0xb4, 0xc6, 0xe8, 0xdd, 0x74, 0x1f, 0x4b, 0xbd, 0x8b, 0x8a,
			0x70, 0x3e, 0xb5, 0x66, 0x48, 0x03, 0xf6, 0x0e, 0x61, 0x35, 0x57, 0xb9, 0x86, 0xc1, 0x1d, 0x9e,
			0xe1, 0xf8, 0x98, 0x11, 0x69, 0xd9, 0x8e, 0x94, 0x9b, 0x1e, 0x87, 0xe9, 0xce, 0x55, 0x28, 0xdf,
			0x8c, 0xa1, 0x89, 0x0d, 0xbf, 0xe6, 0x42, 0x68, 0x41, 0x99, 0x2d, 0x0f, 0xb0, 0x54, 0xbb, 0x16,
		];

	// Rcon is Round Constant used for the Key Expansion [1st col is 2^(r-1) in GF(2^8)] [�5.2]
	protected $Rcon = [
		[0x00, 0x00, 0x00, 0x00],
		[0x01, 0x00, 0x00, 0x00],
		[0x02, 0x00, 0x00, 0x00],
		[0x04, 0x00, 0x00, 0x00],
		[0x08, 0x00, 0x00, 0x00],
		[0x10, 0x00, 0x00, 0x00],
		[0x20, 0x00, 0x00, 0x00],
		[0x40, 0x00, 0x00, 0x00],
		[0x80, 0x00, 0x00, 0x00],
		[0x1b, 0x00, 0x00, 0x00],
		[0x36, 0x00, 0x00, 0x00],
	];

	protected $passwords = [];

	/**
	 * The algorithm to use for PBKDF2. Must be a supported hash_hmac algorithm. Default: sha1
	 *
	 * @var  string
	 */
	private $pbkdf2Algorithm = 'sha1';

	/**
	 * Number of iterations to use for PBKDF2
	 *
	 * @var  int
	 */
	private $pbkdf2Iterations = 1000;

	/**
	 * Should we use a static salt for PBKDF2?
	 *
	 * @var  int
	 */
	private $pbkdf2UseStaticSalt = 0;

	/**
	 * The static salt to use for PBKDF2
	 *
	 * @var  string
	 */
	private $pbkdf2StaticSalt = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

	/**
	 * AES Cipher function: encrypt 'input' with Rijndael algorithm
	 *
	 * @param   string  $input  message as byte-array (16 bytes)
	 * @param   array   $w      key schedule as 2D byte-array (Nr+1 x Nb bytes) -
	 *                          generated from the cipher key by KeyExpansion()
	 *
	 * @return      array  ciphertext as byte-array (16 bytes)
	 */
	public function Cipher($input, $w)
	{
		// main Cipher function [�5.1]
		$Nb = 4; // block size (in words): no of columns in state (fixed at 4 for AES)
		$Nr = count($w) / $Nb - 1; // no of rounds: 10/12/14 for 128/192/256-bit keys

		$state = []; // initialise 4xNb byte-array 'state' with input [�3.4]

		for ($i = 0; $i < 4 * $Nb; $i++)
		{
			$state[$i % 4][(int) floor($i / 4)] = $input[$i];
		}

		$state = $this->AddRoundKey($state, $w, 0, $Nb);

		for ($round = 1; $round < $Nr; $round++)
		{
			// apply Nr rounds
			$state = $this->SubBytes($state, $Nb);
			$state = $this->ShiftRows($state, $Nb);
			$state = $this->MixColumns($state, $Nb);
			$state = $this->AddRoundKey($state, $w, $round, $Nb);
		}

		$state = $this->SubBytes($state, $Nb);
		$state = $this->ShiftRows($state, $Nb);
		$state = $this->AddRoundKey($state, $w, $Nr, $Nb);

		$output = [4 * $Nb]; // convert state to 1-d array before returning [�3.4]

		for ($i = 0; $i < 4 * $Nb; $i++)
		{
			$output[$i] = $state[$i % 4][(int) floor($i / 4)];
		}

		return $output;
	}

	/**
	 * Key expansion for Rijndael Cipher(): performs key expansion on cipher key
	 * to generate a key schedule
	 *
	 * @param   array  $key  cipher key byte-array (16 bytes)
	 *
	 * @return    array key schedule as 2D byte-array (Nr+1 x Nb bytes)
	 */
	public function KeyExpansion($key)
	{
		// generate Key Schedule from Cipher Key [�5.2]
		$Nb = 4; // block size (in words): no of columns in state (fixed at 4 for AES)
		$Nk = count($key) / 4; // key length (in words): 4/6/8 for 128/192/256-bit keys
		$Nr = $Nk + 6; // no of rounds: 10/12/14 for 128/192/256-bit keys

		$w    = [];
		$temp = [];

		for ($i = 0; $i < $Nk; $i++)
		{
			$r     = [$key[4 * $i], $key[4 * $i + 1], $key[4 * $i + 2], $key[4 * $i + 3]];
			$w[$i] = $r;
		}

		for ($i = $Nk; $i < ($Nb * ($Nr + 1)); $i++)
		{
			$w[(int) $i] = [];

			for ($t = 0; $t < 4; $t++)
			{
				$temp[$t] = $w[(int) $i - 1][$t];
			}

			if ($i % $Nk == 0)
			{
				$temp = $this->SubWord($this->RotWord($temp));

				for ($t = 0; $t < 4; $t++)
				{
					$temp[$t] ^= $this->Rcon[(int) ($i / $Nk)][$t];
				}
			}
			elseif ($Nk > 6 && $i % $Nk == 4)
			{
				$temp = $this->SubWord($temp);
			}

			for ($t = 0; $t < 4; $t++)
			{
				$w[(int) $i][$t] = $w[(int) $i - $Nk][$t] ^ $temp[$t];
			}
		}

		return $w;
	}

	/**
	 * Encrypt a text using AES encryption in Counter mode of operation
	 *  - see http://csrc.nist.gov/publications/nistpubs/800-38a/sp800-38a.pdf
	 *
	 * Unicode multi-byte character safe
	 *
	 * @param   string  $plaintext  source text to be encrypted
	 * @param   string  $password   the password to use to generate a key
	 * @param   int     $nBits      number of bits to be used in the key (128, 192, or 256)
	 *
	 * @return string encrypted text
	 */
	public function AESEncryptCtr($plaintext, $password, $nBits)
	{
		$blockSize = 16; // block size fixed at 16 bytes / 128 bits (Nb=4) for AES

		// standard allows 128/192/256 bit keys
		if (!($nBits == 128 || $nBits == 192 || $nBits == 256))
		{
			return '';
		}

		// note PHP (5) gives us plaintext and password in UTF8 encoding!

		// use AES itself to encrypt password to get cipher key (using plain password as source for
		// key expansion) - gives us well encrypted key
		$nBytes  = $nBits / 8; // no bytes in key
		$pwBytes = [];

		for ($i = 0; $i < $nBytes; $i++)
		{
			$pwBytes[$i] = ord(substr($password, $i, 1)) & 0xff;
		}

		$key = $this->Cipher($pwBytes, $this->KeyExpansion($pwBytes));
		$key = array_merge($key, array_slice($key, 0, $nBytes - 16)); // expand key to 16/24/32 bytes long

		// initialise counter block (NIST SP800-38A �B.2): millisecond time-stamp for nonce in
		// 1st 8 bytes, block counter in 2nd 8 bytes
		$counterBlock = [];
		$nonce        = floor(microtime(true) * 1000); // timestamp: milliseconds since 1-Jan-1970
		$nonceSec     = floor($nonce / 1000);
		$nonceMs      = $nonce % 1000;

		// encode nonce with seconds in 1st 4 bytes, and (repeated) ms part filling 2nd 4 bytes
		for ($i = 0; $i < 4; $i++)
		{
			$counterBlock[$i] = $this->urs($nonceSec, $i * 8) & 0xff;
		}

		for ($i = 0; $i < 4; $i++)
		{
			$counterBlock[$i + 4] = $nonceMs & 0xff;
		}

		// and convert it to a string to go on the front of the ciphertext
		$ctrTxt = '';

		for ($i = 0; $i < 8; $i++)
		{
			$ctrTxt .= chr($counterBlock[$i]);
		}

		// generate key schedule - an expansion of the key into distinct Key Rounds for each round
		$keySchedule = $this->KeyExpansion($key);

		$blockCount = ceil(strlen($plaintext) / $blockSize);
		$ciphertxt  = []; // ciphertext as array of strings

		for ($b = 0; $b < $blockCount; $b++)
		{
			// set counter (block #) in last 8 bytes of counter block (leaving nonce in 1st 8 bytes)
			// done in two stages for 32-bit ops: using two words allows us to go past 2^32 blocks (68GB)
			for ($c = 0; $c < 4; $c++)
			{
				$counterBlock[15 - $c] = $this->urs($b, $c * 8) & 0xff;
			}

			for ($c = 0; $c < 4; $c++)
			{
				$counterBlock[15 - $c - 4] = $this->urs($b / 0x100000000, $c * 8);
			}

			$cipherCntr = $this->Cipher($counterBlock, $keySchedule); // -- encrypt counter block --

			// block size is reduced on final block
			$blockLength = $b < $blockCount - 1 ? $blockSize : (strlen($plaintext) - 1) % $blockSize + 1;
			$cipherByte  = [];

			for ($i = 0; $i < $blockLength; $i++)
			{ // -- xor plaintext with ciphered counter byte-by-byte --
				$cipherByte[$i] = $cipherCntr[$i] ^ ord(substr($plaintext, $b * $blockSize + $i, 1));
				$cipherByte[$i] = chr($cipherByte[$i]);
			}

			$ciphertxt[$b] = implode('', $cipherByte); // escape troublesome characters in ciphertext
		}

		// implode is more efficient than repeated string concatenation
		$ciphertext = $ctrTxt . implode('', $ciphertxt);
		$ciphertext = base64_encode($ciphertext);

		return $ciphertext;
	}

	/**
	 * Decrypt a text encrypted by AES in counter mode of operation
	 *
	 * @param   string  $ciphertext  source text to be decrypted
	 * @param   string  $password    the password to use to generate a key
	 * @param   int     $nBits       number of bits to be used in the key (128, 192, or 256)
	 *
	 * @return string decrypted text
	 */
	public function AESDecryptCtr($ciphertext, $password, $nBits)
	{
		$blockSize = 16; // block size fixed at 16 bytes / 128 bits (Nb=4) for AES

		// standard allows 128/192/256 bit keys
		if (!($nBits == 128 || $nBits == 192 || $nBits == 256))
		{
			return '';
		}

		$ciphertext = base64_decode($ciphertext);

		// use AES to encrypt password (mirroring encrypt routine)
		$nBytes  = $nBits / 8; // no bytes in key
		$pwBytes = [];

		for ($i = 0; $i < $nBytes; $i++)
		{
			$pwBytes[$i] = ord(substr($password, $i, 1)) & 0xff;
		}

		$key = $this->Cipher($pwBytes, $this->KeyExpansion($pwBytes));
		$key = array_merge($key, array_slice($key, 0, $nBytes - 16)); // expand key to 16/24/32 bytes long

		// recover nonce from 1st element of ciphertext
		$counterBlock = [];
		$ctrTxt       = substr($ciphertext, 0, 8);

		for ($i = 0; $i < 8; $i++)
		{
			$counterBlock[$i] = ord(substr($ctrTxt, $i, 1));
		}

		// generate key schedule
		$keySchedule = $this->KeyExpansion($key);

		// separate ciphertext into blocks (skipping past initial 8 bytes)
		$nBlocks = ceil((strlen($ciphertext) - 8) / $blockSize);
		$ct      = [];

		for ($b = 0; $b < $nBlocks; $b++)
		{
			$ct[$b] = substr($ciphertext, 8 + $b * $blockSize, 16);
		}

		$ciphertext = $ct; // ciphertext is now array of block-length strings

		// plaintext will get generated block-by-block into array of block-length strings
		$plaintxt = [];

		for ($b = 0; $b < $nBlocks; $b++)
		{
			// set counter (block #) in last 8 bytes of counter block (leaving nonce in 1st 8 bytes)
			for ($c = 0; $c < 4; $c++)
			{
				$counterBlock[15 - $c] = $this->urs($b, $c * 8) & 0xff;
			}

			for ($c = 0; $c < 4; $c++)
			{
				$counterBlock[15 - $c - 4] = $this->urs(($b + 1) / 0x100000000 - 1, $c * 8) & 0xff;
			}

			$cipherCntr = $this->Cipher($counterBlock, $keySchedule); // encrypt counter block

			$plaintxtByte = [];

			for ($i = 0; $i < strlen($ciphertext[$b]); $i++)
			{
				// -- xor plaintext with ciphered counter byte-by-byte --
				$plaintxtByte[$i] = $cipherCntr[$i] ^ ord(substr($ciphertext[$b], $i, 1));
				$plaintxtByte[$i] = chr($plaintxtByte[$i]);
			}

			$plaintxt[$b] = implode('', $plaintxtByte);
		}

		// join array of blocks into single plaintext string
		$plaintext = implode('', $plaintxt);

		return $plaintext;
	}

	/**
	 * AES encryption in CBC mode. This is the standard mode (the CTR methods
	 * actually use Rijndael-128 in CTR mode, which - technically - isn't AES).
	 * The data length is tucked as a 32-bit unsigned integer (little endian)
	 * after the ciphertext. It supports AES-128 only.
	 *
	 * @param   string  $plaintext  The data to encrypt
	 * @param   string  $password   Encryption password
	 *
	 * @return  string  The ciphertext
	 * @author Nicholas K. Dionysopoulos
	 *
	 * @since  3.0.1
	 */
	public function AESEncryptCBC($plaintext, $password)
	{
		$adapter = $this->getAdapter();

		if (!$adapter->isSupported())
		{
			return false;
		}

		// Get encryption parameters
		$rand          = new RandomValue();
		$params        = $this->getKeyDerivationParameters();
		$useStaticSalt = $params['useStaticSalt'];
		$keySizeBytes  = $params['keySize'];
		$salt          = null;

		if ($useStaticSalt)
		{
			$key = $this->getStaticSaltExpandedKey($password);
		}
		else
		{
			// Create a salt and derive a key from the password using PBKDF2
			$algorithm  = $params['algorithm'];
			$iterations = $params['iterations'];
			$salt       = $rand->generate(64);
			$key        = $this->pbkdf2($password, $salt, $algorithm, $iterations, $keySizeBytes);
		}


		// Also create a new, random IV
		$iv = $rand->generate($keySizeBytes);

		// The ciphertext is the encrypted string...
		$ciphertext = $adapter->encrypt($plaintext, $key, $iv);

		// ...minus the IV which was placed in front
		$ciphertext = substr($ciphertext, $keySizeBytes);

		if (!$useStaticSalt)
		{
			// ...plus the PBKDF2 setup values at the end (68 bytes)...
			$ciphertext .= 'JPST' . $salt;
		}

		// ...plus the IV at the end (20 bytes)...
		$ciphertext .= 'JPIV' . $iv;

		// ...plus the plaintext length (4 bytes).
		$ciphertext .= pack('V', strlen($plaintext));

		return $ciphertext;
	}

	/**
	 * Get the parameters fed into PBKDF2 to expand the user password into an encryption key. These are the static
	 * parameters (key size, hashing algorithm and number of iterations). A new salt is used for each encryption block
	 * to minimize the risk of attacks against the password.
	 *
	 * @return  array
	 */
	public function getKeyDerivationParameters()
	{
		return [
			'keySize'       => 16,
			'algorithm'     => $this->pbkdf2Algorithm,
			'iterations'    => $this->pbkdf2Iterations,
			'useStaticSalt' => $this->pbkdf2UseStaticSalt,
			'staticSalt'    => $this->pbkdf2StaticSalt,
		];
	}

	/**
	 * AES decryption in CBC mode. This is the standard mode (the CTR methods
	 * actually use Rijndael-128 in CTR mode, which - technically - isn't AES).
	 *
	 * It supports AES-128 only. It assumes that the last 4 bytes
	 * contain a little-endian unsigned long integer representing the unpadded
	 * data length.
	 *
	 * @param   string  $ciphertext  The data to encrypt
	 * @param   string  $password    Encryption password
	 *
	 * @return  string  The plaintext
	 * @author Nicholas K. Dionysopoulos
	 *
	 * @since  3.0.1
	 */
	public function AESDecryptCBC($ciphertext, $password)
	{
		$adapter = $this->getAdapter();

		if (!$adapter->isSupported())
		{
			return false;
		}

		// Read the data size
		$data_size = unpack('V', substr($ciphertext, -4));

		// Do I have a PBKDF2 salt?
		$salt             = substr($ciphertext, -92, 68);
		$rightStringLimit = -4;

		$params        = $this->getKeyDerivationParameters();
		$keySizeBytes  = $params['keySize'];
		$algorithm     = $params['algorithm'];
		$iterations    = $params['iterations'];
		$useStaticSalt = $params['useStaticSalt'];

		if (substr($salt, 0, 4) == 'JPST')
		{
			// We have a stored salt. Retrieve it and tell decrypt to process the string minus the last 44 bytes
			// (4 bytes for JPST, 16 bytes for the salt, 4 bytes for JPIV, 16 bytes for the IV, 4 bytes for the
			// uncompressed string length - note that using PBKDF2 means we're also using a randomized IV per the
			// format specification).
			$salt             = substr($salt, 4);
			$rightStringLimit -= 68;

			$key = $this->pbkdf2($password, $salt, $algorithm, $iterations, $keySizeBytes);
		}
		elseif ($useStaticSalt)
		{
			// We have a static salt. Use it for PBKDF2.
			$key = $this->getStaticSaltExpandedKey($password);
		}
		else
		{
			// Get the expanded key from the password. THIS USES THE OLD, INSECURE METHOD.
			$key = $this->expandKey($password);
		}

		// Try to get the IV from the data
		$iv = substr($ciphertext, -24, 20);

		if (substr($iv, 0, 4) == 'JPIV')
		{
			// We have a stored IV. Retrieve it and tell mdecrypt to process the string minus the last 24 bytes
			// (4 bytes for JPIV, 16 bytes for the IV, 4 bytes for the uncompressed string length)
			$iv               = substr($iv, 4);
			$rightStringLimit -= 20;
		}
		else
		{
			// No stored IV. Do it the dumb way.
			$iv = $this->createTheWrongIV($password);
		}

		// Decrypt
		$plaintext = $adapter->decrypt($iv . substr($ciphertext, 0, $rightStringLimit), $key);

		// Trim padding, if necessary
		if (strlen($plaintext) > $data_size)
		{
			$plaintext = substr($plaintext, 0, $data_size);
		}

		return $plaintext;
	}

	/**
	 * That's the old way of creating an IV that's definitely not cryptographically sound.
	 *
	 * DO NOT USE, EVER, UNLESS YOU WANT TO DECRYPT LEGACY DATA
	 *
	 * @param   string  $password  The raw password from which we create an IV in a super bozo way
	 *
	 * @return  string  A 16-byte IV string
	 *
	 * @since   4.6.0
	 * @author  Nicholas K. Dionysopoulos
	 */
	function createTheWrongIV($password)
	{
		static $ivs = [];

		$key = md5($password);

		if (!isset($ivs[$key]))
		{
			// Create an Initialization Vector (IV) based on the password, using the same technique as for the key
			$nBytes  = 16; // AES uses a 128 -bit (16 byte) block size, hence the IV size is always 16 bytes
			$pwBytes = [];

			for ($i = 0; $i < $nBytes; $i++)
			{
				$pwBytes[$i] = ord(substr($password, $i, 1)) & 0xff;
			}

			$iv    = $this->Cipher($pwBytes, $this->KeyExpansion($pwBytes));
			$newIV = '';

			foreach ($iv as $int)
			{
				$newIV .= chr($int);
			}

			$ivs[$key] = $newIV;
		}

		return $ivs[$key];
	}

	/*
	 * Unsigned right shift function, since PHP has neither >>> operator nor unsigned ints
	 *
	 * @param a  number to be shifted (32-bit integer)
	 * @param b  number of bits to shift a to the right (0..31)
	 * @return   a right-shifted and zero-filled by b bits
	 */

	/**
	 * Expand the password to an appropriate 128-bit encryption key. THIS CODE IS OBSOLETE. DO NOT USE.
	 *
	 * @param   string  $password
	 *
	 * @return  string
	 *
	 * @since   5.2.0
	 * @author  Nicholas K. Dionysopoulos
	 */
	public function expandKey($password)
	{
		// Try to fetch cached key or create it if it doesn't exist
		$nBits     = 128;
		$lookupKey = md5($password . '-' . $nBits);

		if (array_key_exists($lookupKey, $this->passwords))
		{
			$key = $this->passwords[$lookupKey];

			return $key;
		}

		// use AES itself to encrypt password to get cipher key (using plain password as source for
		// key expansion) - gives us well encrypted key.
		$nBytes  = $nBits / 8; // Number of bytes in key
		$pwBytes = [];

		for ($i = 0; $i < $nBytes; $i++)
		{
			$pwBytes[$i] = ord(substr($password, $i, 1)) & 0xff;
		}

		$key    = $this->Cipher($pwBytes, $this->KeyExpansion($pwBytes));
		$key    = array_merge($key, array_slice($key, 0, $nBytes - 16)); // expand key to 16/24/32 bytes long
		$newKey = '';

		foreach ($key as $int)
		{
			$newKey .= chr($int);
		}

		$key = $newKey;

		$this->passwords[$lookupKey] = $key;

		return $key;
	}

	/**
	 * Returns the correct AES-128 CBC encryption adapter
	 *
	 * @return  AdapterInterface
	 *
	 * @since   5.2.0
	 * @author  Nicholas K. Dionysopoulos
	 */
	public function getAdapter()
	{
		static $adapter = null;

		if (is_object($adapter) && ($adapter instanceof AdapterInterface))
		{
			return $adapter;
		}

		$adapter = new OpenSSL();

		if (!$adapter->isSupported())
		{
			$adapter = new Mcrypt();
		}

		return $adapter;
	}

	/**
	 * Returns the length of a string in BYTES, not characters
	 *
	 * @param   string  $string  The string to get the length for
	 *
	 * @return int The size in BYTES
	 */
	public function stringLength($string)
	{
		return function_exists('mb_strlen') ? mb_strlen($string, '8bit') : strlen($string);
	}

	/**
	 * Attempt to use mbstring for getting parts of strings
	 *
	 * @param   string    $string
	 * @param   int       $start
	 * @param   int|null  $length
	 *
	 * @return  string
	 */
	public function subString($string, $start, $length = null)
	{
		return function_exists('mb_substr') ? mb_substr($string, $start, $length, '8bit') :
			substr($string, $start, $length);
	}

	/**
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 *
	 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
	 *
	 * This implementation of PBKDF2 was originally created by https://defuse.ca
	 * With improvements by http://www.variations-of-shadow.com
	 * Modified for Akeeba Engine by Akeeba Ltd (removed unnecessary checks to make it faster)
	 *
	 * @param   string  $password    The password.
	 * @param   string  $salt        A salt that is unique to the password.
	 * @param   string  $algorithm   The hash algorithm to use. Default is sha1.
	 * @param   int     $count       Iteration count. Higher is better, but slower. Default: 1000.
	 * @param   int     $key_length  The length of the derived key in bytes.
	 *
	 * @return  string  A string of $key_length bytes
	 */
	public function pbkdf2($password, $salt, $algorithm = 'sha1', $count = 1000, $key_length = 16)
	{
		if (function_exists("hash_pbkdf2"))
		{
			return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, true);
		}

		$hash_length = $this->stringLength(hash($algorithm, "", true));
		$block_count = ceil($key_length / $hash_length);

		$output = "";

		for ($i = 1; $i <= $block_count; $i++)
		{
			// $i encoded as 4 bytes, big endian.
			$last = $salt . pack("N", $i);

			// First iteration
			$xorResult = hash_hmac($algorithm, $last, $password, true);
			$last      = $xorResult;

			// Perform the other $count - 1 iterations
			for ($j = 1; $j < $count; $j++)
			{
				$last      = hash_hmac($algorithm, $last, $password, true);
				$xorResult ^= $last;
			}

			$output .= $xorResult;
		}

		return $this->subString($output, 0, $key_length);
	}

	/**
	 * @return string
	 */
	public function getPbkdf2Algorithm()
	{
		return $this->pbkdf2Algorithm;
	}

	/**
	 * @param   string  $pbkdf2Algorithm
	 *
	 * @return Encrypt
	 */
	public function setPbkdf2Algorithm($pbkdf2Algorithm)
	{
		$this->pbkdf2Algorithm = $pbkdf2Algorithm;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPbkdf2Iterations()
	{
		return $this->pbkdf2Iterations;
	}

	/**
	 * @param   int  $pbkdf2Iterations
	 *
	 * @return Encrypt
	 */
	public function setPbkdf2Iterations($pbkdf2Iterations)
	{
		$this->pbkdf2Iterations = $pbkdf2Iterations;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPbkdf2UseStaticSalt()
	{
		return $this->pbkdf2UseStaticSalt;
	}

	/**
	 * @param   int  $pbkdf2UseStaticSalt
	 *
	 * @return Encrypt
	 */
	public function setPbkdf2UseStaticSalt($pbkdf2UseStaticSalt)
	{
		$this->pbkdf2UseStaticSalt = $pbkdf2UseStaticSalt;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPbkdf2StaticSalt()
	{
		return $this->pbkdf2StaticSalt;
	}

	/**
	 * @param   string  $pbkdf2StaticSalt
	 *
	 * @return Encrypt
	 */
	public function setPbkdf2StaticSalt($pbkdf2StaticSalt)
	{
		$this->pbkdf2StaticSalt = $pbkdf2StaticSalt;

		return $this;
	}

	/**
	 * Get the expanded key from the user supplied password using a static salt. The results are cached for performance
	 * reasons.
	 *
	 * @param   string  $password  The user-supplied password, UTF-8 encoded.
	 *
	 * @return  string  The expanded key
	 */
	public function getStaticSaltExpandedKey($password)
	{
		$params       = $this->getKeyDerivationParameters();
		$keySizeBytes = $params['keySize'];
		$algorithm    = $params['algorithm'];
		$iterations   = $params['iterations'];
		$staticSalt   = $params['staticSalt'];

		$lookupKey = "PBKDF2-$algorithm-$iterations-" . md5($password . $staticSalt);

		if (!array_key_exists($lookupKey, $this->passwords))
		{
			$this->passwords[$lookupKey] = $this->pbkdf2($password, $staticSalt, $algorithm, $iterations, $keySizeBytes);
		}

		return $this->passwords[$lookupKey];
	}

	protected function AddRoundKey($state, $w, $rnd, $Nb)
	{
		// xor Round Key into state S [�5.1.4]
		for ($r = 0; $r < 4; $r++)
		{
			for ($c = 0; $c < $Nb; $c++)
			{
				$state[$r][$c] ^= $w[$rnd * 4 + $c][$r];
			}
		}

		return $state;
	}

	protected function SubBytes($s, $Nb)
	{
		// apply SBox to state S [�5.1.1]
		for ($r = 0; $r < 4; $r++)
		{
			for ($c = 0; $c < $Nb; $c++)
			{
				$s[$r][$c] = $this->Sbox[$s[$r][$c]];
			}
		}

		return $s;
	}

	protected function ShiftRows($s, $Nb)
	{
		// shift row r of state S left by r bytes [�5.1.2]
		$t = [4];

		for ($r = 1; $r < 4; $r++)
		{
			// shift into temp copy
			for ($c = 0; $c < 4; $c++)
			{
				$t[$c] = $s[$r][($c + $r) % $Nb];
			}

			// and copy back
			for ($c = 0; $c < 4; $c++)
			{
				$s[$r][$c] = $t[$c];
			}

		}

		// note that this will work for Nb=4,5,6, but not 7,8 (always 4 for AES):

		return $s; // see fp.gladman.plus.com/cryptography_technology/rijndael/aes.spec.311.pdf
	}

	protected function MixColumns($s, $Nb)
	{
		// combine bytes of each col of state S [�5.1.3]
		for ($c = 0; $c < 4; $c++)
		{
			$a = [4]; // 'a' is a copy of the current column from 's'
			$b = [4]; // 'b' is a�{02} in GF(2^8)

			for ($i = 0; $i < 4; $i++)
			{
				$a[$i] = $s[$i][$c];
				$b[$i] = $s[$i][$c] & 0x80 ? $s[$i][$c] << 1 ^ 0x011b : $s[$i][$c] << 1;
			}

			// a[n] ^ b[n] is a�{03} in GF(2^8)
			$s[0][$c] = $b[0] ^ $a[1] ^ $b[1] ^ $a[2] ^ $a[3]; // 2*a0 + 3*a1 + a2 + a3
			$s[1][$c] = $a[0] ^ $b[1] ^ $a[2] ^ $b[2] ^ $a[3]; // a0 * 2*a1 + 3*a2 + a3
			$s[2][$c] = $a[0] ^ $a[1] ^ $b[2] ^ $a[3] ^ $b[3]; // a0 + a1 + 2*a2 + 3*a3
			$s[3][$c] = $a[0] ^ $b[0] ^ $a[1] ^ $a[2] ^ $b[3]; // 3*a0 + a1 + a2 + 2*a3
		}

		return $s;
	}

	protected function SubWord($w)
	{
		// apply SBox to 4-byte word w
		for ($i = 0; $i < 4; $i++)
		{
			$w[$i] = $this->Sbox[$w[$i]];
		}

		return $w;
	}

	protected function RotWord($w)
	{
		// rotate 4-byte word w left by one byte
		$tmp = $w[0];

		for ($i = 0; $i < 3; $i++)
		{
			$w[$i] = $w[$i + 1];
		}

		$w[3] = $tmp;

		return $w;
	}

	protected function urs($a, $b)
	{
		$a &= 0xffffffff;
		$b &= 0x1f; // (bounds check)

		if ($a & 0x80000000 && $b > 0)
		{
			// if left-most bit set
			$a = ($a >> 1) & 0x7fffffff; //   right-shift one bit & clear left-most bit
			$a = $a >> ($b - 1); //   remaining right-shifts
		}
		else
		{
			// otherwise
			$a = ($a >> $b); //   use normal right-shift
		}

		return $a;
	}
}
