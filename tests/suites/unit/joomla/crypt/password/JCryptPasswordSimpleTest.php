<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Hash
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/crypt/password.php';
require_once JPATH_PLATFORM . '/joomla/crypt/password/simple.php';

/**
 * Test class for JCryptPasswordSimple.
 */
class JCryptPasswordSimpleTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Data provider for testCreate method.
	 */
	public function createData()
	{
		return array(
			'Blowfish'   => array('password', JCryptPassword::BLOWFISH, 'ABCDEFGHIJKLMNOPQRSTUV', '$2y$10$ABCDEFGHIJKLMNOPQRSTUOiAi7OcdE4zRCh6NcGWusEcNPtq6/w8.'),
			'MD5'        => array('password', JCryptPassword::MD5, 'ABCDEFGHIJKL', '$1$ABCDEFGH$hGGndps75hhROKqu/zh9q1'),
			'Joomla'     => array('password', JCryptPassword::JOOMLA, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456', '883a96d8da5440781fe7b60f1d4ae2b3:ABCDEFGHIJKLMNOPQRSTUVWXYZ123456'),
			'Blowfish_5' => array('password', JCryptPassword::BLOWFISH, 'ABCDEFGHIJKLMNOPQRSTUV', '$2y$05$ABCDEFGHIJKLMNOPQRSTUOvv7EU5o68GAoLxyfugvULZR70IIMZqW', 5)
		);
	}

	/**
	 * Tests create method.
	 *
	 * @covers  JCryptPasswordSimple::create
	 *
	 * @dataProvider  createData
	 */
	public function testCreate($password, $type, $salt, $expected, $cost = 10)
	{
		$hasher = $this->getMock('JCryptPasswordSimple', array('getSalt'));

		$hasher->setCost($cost);

		$hasher->expects($this->any())
			   ->method('getSalt')
			   ->with(strlen($salt))
			   ->will($this->returnValue($salt));

		$this->assertEquals(
			$expected,
			$hasher->create($password, $type)
		);
	}

	/**
	 * Data Provider for testVerify.
	 */
	public function verifyData()
	{
		return array(
			'Blowfish Valid:'   => array('password', '$2y$10$ABCDEFGHIJKLMNOPQRSTUOiAi7OcdE4zRCh6NcGWusEcNPtq6/w8.', true),
			'Blowfish Invalid:' => array('wrong password', '$2y$10$ABCDEFGHIJKLMNOPQRSTUOiAi7OcdE4zRCh6NcGWusEcNPtq6/w8.', false),
			'MD5 Valid'         => array('password', '$1$ABCDEFGH$hGGndps75hhROKqu/zh9q1', true),
			'MD5 Invalid'       => array('passw0rd', '$1$ABCDEFGH$hGGndps75hhROKqu/zh9q1', false),
			'Joomla Valid'      => array('password', '883a96d8da5440781fe7b60f1d4ae2b3:ABCDEFGHIJKLMNOPQRSTUVWXYZ123456', true),
			'Joomla Invalid'    => array('passw0rd', '883a96d8da5440781fe7b60f1d4ae2b3:ABCDEFGHIJKLMNOPQRSTUVWXYZ123456', false)
		);
	}

	/**
	 * Tests the verify method.
	 *
	 * @covers        JCryptPasswordSimple::verify
	 * @dataProvider  verifyData
	 */
	public function testVerify($password, $hash, $expectation)
	{
		$hasher = new JCryptPasswordSimple;

		$this->assertEquals($hasher->verify($password, $hash), $expectation);
	}
}
