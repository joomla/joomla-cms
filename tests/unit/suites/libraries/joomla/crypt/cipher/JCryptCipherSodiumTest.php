<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Crypt\Cipher\SodiumCipher;
use Joomla\CMS\Crypt\Crypt;

/**
 * Test class for \Joomla\CMS\Crypt\Cipher\SodiumCipher.
 */
class JCryptCipherSodiumTest extends TestCase
{
	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		if (!SodiumCipher::isSupported())
		{
			self::markTestSkipped('The libsodium extension is not available.');
		}
	}

	/**
	 * Test data for processing
	 *
	 * @return  array
	 */
	public function dataStrings()
	{
		return array(
			array('c-;3-(Is>{DJzOHMCv_<#yKuN/G`/Us{GkgicWG$M|HW;kI0BVZ^|FY/"Obt53?PNaWwhmRtH;lWkWE4vlG5CIFA!abu&F=Xo#Qw}gAp3;GL\'k])%D}C+W&ne6_F$3P5'),
			array('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
					'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor ' .
					'in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt ' .
					'in culpa qui officia deserunt mollit anim id est laborum.'),
			array('لا أحد يحب الألم بذاته، يسعى ورائه أو يبتغيه، ببساطة لأنه الألم...'),
			array('Широкая электрификация южных губерний даст мощный толчок подъёму сельского хозяйства'),
			array('The quick brown fox jumps over the lazy dog.')
		);
	}

	/**
	 * @testdox  Validates data is encrypted and decrypted correctly
	 *
	 * @param   string  $data  The decrypted data to validate
	 *
	 * @covers        Joomla\CMS\Crypt\Cipher\SodiumCipher::decrypt
	 * @covers        Joomla\CMS\Crypt\Cipher\SodiumCipher::encrypt
	 * @dataProvider  dataStrings
	 */
	public function testDataEncryptionAndDecryption($data)
	{
		$cipher = new SodiumCipher;
		$key    = $cipher->generateKey();

		$cipher->setNonce($this->callRandombytesBuf());

		$encrypted = $cipher->encrypt($data, $key);

		// Assert that the encrypted value is not the same as the clear text value.
		$this->assertNotSame($data, $encrypted);

		$decrypted = $cipher->decrypt($encrypted, $key);

		// Assert the decrypted string is the same value we started with
		$this->assertSame($data, $decrypted);
	}

	/**
	 * @testdox  Validates keys are correctly generated
	 *
	 * @covers   Joomla\CMS\Crypt\Cipher\SodiumCipher::generateKey
	 */
	public function testGenerateKey()
	{
		$cipher = new SodiumCipher;
		$key    = $cipher->generateKey();

		// Assert that the key is the correct type.
		$this->assertInstanceOf('\\Joomla\\CMS\\Crypt\\Key', $key);

		// Assert the keys pass validation
		$this->assertSame(Crypt::safeStrlen($key->private), 32);
		$this->assertSame(Crypt::safeStrlen($key->public), 32);

		// Assert the key is of the correct type.
		$this->assertAttributeEquals('sodium', 'type', $key);
	}

	/**
	 * Calls the `randombytes_buf` function from the available `libsodium` implementation
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callRandombytesBuf()
	{
		// Try calling core PHP function
		if (is_callable('sodium_randombytes_buf'))
		{
			return sodium_randombytes_buf(SODIUM_CRYPTO_BOX_NONCEBYTES);
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\randombytes_buf'))
		{
			return \Sodium\randombytes_buf(\Sodium\CRYPTO_BOX_NONCEBYTES);
		}

		// Neither method is available, panic
		throw new RuntimeException('Could not call "randombytes_buf" function');
	}
}
