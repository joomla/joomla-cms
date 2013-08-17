<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/crypt/key.php';
require_once JPATH_PLATFORM . '/joomla/crypt/cipher.php';
require_once JPATH_PLATFORM . '/joomla/crypt/cipher/3des.php';

/**
 * Test class for JCryptCipher3DES.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Crypt
 * @since       12.1
 */
class JCryptCipher3DESTest extends TestCase
{
	/**
	 * @var    JCryptCipher3DES
	 * @since  12.1
	 */
	private $_cipher;

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		// Only run the test if mcrypt is loaded.
		if (!extension_loaded('mcrypt'))
		{
			$this->markTestSkipped('The mcrypt extension must be available for this test to run.');
		}

		$this->_cipher = new JCryptCipher3DES;

		// Build the key for testing.
		$this->key = new JCryptKey('3des');
		$this->key->private = file_get_contents(__DIR__ . '/stubs/encrypted/3des/key.priv');
		$this->key->public = file_get_contents(__DIR__ . '/stubs/encrypted/3des/key.pub');
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function tearDown()
	{
		$this->_cipher = null;

		parent::tearDown();
	}

	/**
	 * Test...
	 *
	 * @return array
	 */
	public function data()
	{
		return array(
			array(
				'1.txt',
				'c-;3-(Is>{DJzOHMCv_<#yKuN/G`/Us{GkgicWG$M|HW;kI0BVZ^|FY/"Obt53?PNaWwhmRtH;lWkWE4vlG5CIFA!abu&F=Xo#Qw}gAp3;GL\'k])%D}C+W&ne6_F$3P5'),
			array(
				'2.txt',
				'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
					'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor ' .
					'in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt ' .
					'in culpa qui officia deserunt mollit anim id est laborum.'),
			array('3.txt', 'لا أحد يحب الألم بذاته، يسعى ورائه أو يبتغيه، ببساطة لأنه الألم...'),
			array('4.txt',
				'Широкая электрификация южных губерний даст мощный ' .
					'толчок подъёму сельского хозяйства'),
			array('5.txt', 'The quick brown fox jumps over the lazy dog.')
		);
	}

	/**
	 * Tests JCryptCipher3DES->decrypt()
	 *
	 * @param   string  $file  @todo
	 * @param   string  $data  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider data
	 * @since   12.1
	 */
	public function testDecrypt($file, $data)
	{
		$encrypted = file_get_contents(__DIR__ . '/stubs/encrypted/3des/' . $file);
		$decrypted = $this->_cipher->decrypt($encrypted, $this->key);

		// Assert that the decrypted values are the same as the expected ones.
		$this->assertEquals($data, $decrypted);
	}

	/**
	 * Tests JCryptCipher3DES->encrypt()
	 *
	 * @param   string  $file  @todo
	 * @param   string  $data  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider data
	 * @since   12.1
	 */
	public function testEncrypt($file, $data)
	{
		$encrypted = $this->_cipher->encrypt($data, $this->key);

		// Assert that the encrypted value is not the same as the clear text value.
		$this->assertNotEquals($data, $encrypted);

		// Assert that the encrypted values are the same as the expected ones.
		$this->assertStringEqualsFile(__DIR__ . '/stubs/encrypted/3des/' . $file, $encrypted);
	}

	/**
	 * Tests JCryptCipher3DES->generateKey()
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGenerateKey()
	{
		$key = $this->_cipher->generateKey();

		// Assert that the key is the correct type.
		$this->assertInstanceOf('JCryptKey', $key);

		// Assert that the private key is 24 bytes long.
		$this->assertEquals(24, strlen($key->private));

		// Assert the key is of the correct type.
		$this->assertAttributeEquals('3des', 'type', $key);
	}
}
