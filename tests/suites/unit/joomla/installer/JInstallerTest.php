<?php
/**
 * @package     Joomla.UnitTest
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


/**
 * Test class for JInstaller.
 */
class JInstallerTest extends TestCase
{
	/**
     * @var JSession
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
		$this->saveFactoryState();
		$newDbo = $this->getMock('JDatabase');
		JFactory::$database = &$newDbo;

		$this->object = JInstaller::getInstance();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
		$this->restoreFactoryState();
    }

    /**
	 * @covers  JInstaller::getInstance
	 */
	public function testGetInstance()
	{
		$this->assertThat(
			$this->object = JInstaller::getInstance(),
			$this->isInstanceOf('JInstaller'),
			'JInstaller::getInstance failed'
		);
	}

	/**
	 * @covers  JInstaller::setOverwrite
	 * @covers  JInstaller::isOverwrite
	 */
	public function testIsAndSetOverwrite()
	{
		$this->object->setOverwrite(false);

		$this->assertThat(
			$this->object->isOverwrite(),
			$this->equalTo(false),
			'Get or Set overwrite failed'
		);

		$this->assertThat(
			$this->object->setOverwrite(true),
			$this->equalTo(false),
			'setOverwrite did not return the old value properly.'
		);

		$this->assertThat(
			$this->object->isOverwrite(),
			$this->equalTo(true),
			'getOverwrite did not return the expected value.'
		);
	}

	/**
	 * @covers  JInstaller::setRedirectUrl
	 * @covers  JInstaller::getRedirectUrl
	 */
	public function testGetAndSetRedirectUrl()
	{
		$this->object->setRedirectUrl('http://www.example.com');

		$this->assertThat(
			$this->object->getRedirectUrl(),
			$this->equalTo('http://www.example.com'),
			'Get or Set Redirect Url failed'
		);
	}

	/**
	 * @covers  JInstaller::setUpgrade
	 * @covers  JInstaller::isUpgrade
	 */
	public function testIsAndSetUpgrade()
	{
		$this->object->setUpgrade(false);

		$this->assertThat(
			$this->object->isUpgrade(),
			$this->equalTo(false),
			'Get or Set Upgrade failed'
		);

		$this->assertThat(
			$this->object->setUpgrade(true),
			$this->equalTo(false),
			'setUpgrade did not return the old value properly.'
		);

		$this->assertThat(
			$this->object->isUpgrade(),
			$this->equalTo(true),
			'getUpgrade did not return the expected value.'
		);
	}
}
