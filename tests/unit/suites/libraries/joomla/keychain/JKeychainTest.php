<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Keychain
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.filesystem.folder');

/**
 * Tests for the Joomla Platform Keychain Class
 *
 * @package     Joomla.UnitTest
 * @subpackage  Keychain
 * @since       3.1.4
 */
class JKeychainTest extends \PHPUnit\Framework\TestCase
{
	protected static $workDirectory;

	/**
	 * Set up the system by ensuring some files aren't there.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public static function setUpBeforeClass()
	{
		self::$workDirectory = JPATH_TESTS . "/tmp/keychain/" . uniqid();
		JFolder::copy(__DIR__ . '/data', self::$workDirectory . '/data');

		parent::setUpBeforeClass();
	}

	/**
	 * Clean up afterwards.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public static function tearDownAfterClass()
	{
		// Clean up files
		JFolder::delete(self::$workDirectory);

		parent::tearDownAfterClass();
	}

	/**
	 * Test loading a file created in the CLI client (Joomla! Platform)
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testLoadCLIKeychain()
	{
		$keychain = new JKeychain;

		$keychainFile = self::$workDirectory . '/data/cli-keychain.dat';
		$passphraseFile = self::$workDirectory . '/data/cli-passphrase.dat';
		$publicKeyFile = self::$workDirectory . '/data/publickey.pem';

		$keychain->loadKeychain($keychainFile, $passphraseFile, $publicKeyFile);

		$this->assertEquals('value', $keychain->get('test'));
	}

	/**
	 * Test trying to create a new passphrase file
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testCreatePassphraseFile()
	{
		$privateKeyFile = self::$workDirectory . '/data/private.key';
		$passphraseFile = self::$workDirectory . '/data/web-passphrase.dat';

		$keychain = new JKeychain;
		$keychain->createPassphraseFile('testpassphrase', $passphraseFile, $privateKeyFile, 'password');

		$this->assertFileExists($passphraseFile, 'Test passphrase file exists');
	}

	/**
	 * Try to load a keychain that doesn't exist (this shouldn't cause an error)
	 *
	 * @expectedException         RuntimeException
	 * @expectedExceptionMessage  Attempting to load non-existent keychain file
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testLoadKeychainNonexistant()
	{
		$keychainFile = self::$workDirectory . '/data/fake-web-keychain.dat';
		$publicKeyFile = self::$workDirectory . '/data/publickey.pem';
		$passphraseFile = self::$workDirectory . '/data/web-passphrase.dat';

		$keychain = new JKeychain;

		$keychain->loadKeychain($keychainFile, $passphraseFile, $publicKeyFile);
	}

	/**
	 * Try to load a keychain that isn't a keychain
	 *
	 * @depends                   testCreatePassphraseFile
	 * @expectedException         RuntimeException
	 * @expectedExceptionMessage  Failed to decrypt keychain file
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testLoadKeychainInvalid()
	{
		$publicKeyFile = self::$workDirectory . '/data/publickey.pem';
		$passphraseFile = self::$workDirectory . '/data/web-passphrase.dat';

		$keychain = new JKeychain;

		$keychain->loadKeychain($passphraseFile, $passphraseFile, $publicKeyFile);
	}

	/**
	 * Create a new keychain and persist it to a new file.
	 *
	 * @depends  testCreatePassphraseFile
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testSaveKeychain()
	{
		$keychainFile = self::$workDirectory . '/data/web-keychain.dat';
		$publicKeyFile = self::$workDirectory . '/data/publickey.pem';
		$passphraseFile = self::$workDirectory . '/data/web-passphrase.dat';

		$keychain = new JKeychain;
		$keychain->set('dennis', 'liao');
		$this->assertTrue((bool) $keychain->saveKeychain($keychainFile, $passphraseFile, $publicKeyFile), 'Assert that saveKeychain returns true.');

		$this->assertFileExists($keychainFile, 'Check that keychain file was created properly.');
	}

	/**
	 * Load a keychain file we just created
	 *
	 * @depends  testSaveKeychain
	 *
	 * @return   void
	 *
	 * @since    3.1.4
	 */
	public function testLoadKeychain()
	{
		$keychainFile = self::$workDirectory . '/data/web-keychain.dat';
		$publicKeyFile = self::$workDirectory . '/data/publickey.pem';
		$passphraseFile = self::$workDirectory . '/data/web-passphrase.dat';

		$keychain = new JKeychain;

		$keychain->loadKeychain($keychainFile, $passphraseFile, $publicKeyFile);

		$this->assertEquals('liao', $keychain->get('dennis'));
	}

	/**
	 * Delete a value from the keychain
	 *
	 * @depends  testSaveKeychain
	 *
	 * @return   void
	 *
	 * @since    3.1.4
	 */
	public function testDeleteValue()
	{
		$keychainFile = self::$workDirectory . '/data/web-keychain.dat';
		$publicKeyFile = self::$workDirectory . '/data/publickey.pem';
		$passphraseFile = self::$workDirectory . '/data/web-passphrase.dat';

		$keychain = new JKeychain;

		$keychain->loadKeychain($keychainFile, $passphraseFile, $publicKeyFile);

		$this->assertEquals('liao', $keychain->get('dennis'));

		$keychain->deleteValue('dennis');

		$this->assertFalse($keychain->exists('dennis'));

		$keychain->saveKeychain($keychainFile, $passphraseFile, $publicKeyFile);

		$keychain = new JKeychain;

		$keychain->loadKeychain($keychainFile, $passphraseFile, $publicKeyFile);

		$this->assertFalse($keychain->exists('dennis'));
	}
}
