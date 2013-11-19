<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadOptionClass('cachehanders');

/**
 * Test class for JFormOptionCacheHandlers.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.3
 */
class JFormOptionCacheHandlersTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test that the correct options are generated.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetOptions()
	{
		$element = simplexml_load_string('<option type="cachehandlers" />');

		$options = JFormOption::getOptions($element, 'TestField');

		$stores = JCache::getStores();

		$this->assertEquals(
			count($options),
			count($stores),
			'Line:' . __LINE__ . ' There should be exactly one option per store type.'
		);

		foreach ($options as $option)
		{
			$this->assertThat(
				in_array($option->value, $stores),
				$this->isTrue(),
				'Line:' . __LINE__ . ' The option value should be one of the store types.'
			);

			$this->assertEquals(
				$option->text,
				JText::_('JLIB_FORM_VALUE_CACHE_' . $option->value),
				'Line:' . __LINE__ . ' The the option text should be derived from the value.'
			);

		}
	}
}
