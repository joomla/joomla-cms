<?php
/**
 * Loader Test
 *
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */
require_once 'PHPUnit/Framework.php';

/**
 * Test class for Loader.
 *
 * @package  Joomla.UnitTest
 * @since    2.5.25
 */
class LoaderTest extends JoomlaTestCase
{
	/**
	 * Test that we can use libraries using same prefix
	 *
	 * @return void
	 */
	public function testRegisterPrefix()
	{
		JLoader::registerPrefix('Dummy', __DIR__ . '/../../filesystem/libraries/dummy');
		JLoader::registerPrefix('Dummya', __DIR__ . '/../../filesystem/libraries/dummya');

		$helloHelper = new DummyHelperHello;
		$this->assertTrue($helloHelper instanceof DummyHelperHello);

		$helperBye = new DummyaHelperBye;
		$this->assertTrue($helperBye instanceof DummyaHelperBye);
	}
}
