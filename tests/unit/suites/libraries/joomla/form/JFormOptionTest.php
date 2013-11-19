<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_TESTS . '/stubs/FormInspectors.php';
include_once 'JFormDataHelper.php';

/**
 * Test class for JFormOption.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.3
 */
class JFormOptionTest extends TestCase
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

		JFactory::$application = $this->getMockCmsApp();

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
	 * Tests the JFormOption::getOptions method
	 *
	 * @return void
	 */
	public function testGetOptions()
	{
		$form = new JFormInspector('form1', array('control' => 'jform'));

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		// Standard usage.

		$xml = $form->getXML();
		$xmlopt = array_pop($xml->xpath('fields/fields[@name="params"]/field[@name="colours"]/option'));

		$this->assertThat(
			$xmlopt instanceof SimpleXMLElement,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The object should be an instance of SimpleXMLElement.'
		);

		$option = array_pop(JFormOption::getOptions($xmlopt, 'TestField'));

		$this->assertThat(
			$option->text,
			$this->equalTo((string) $xmlopt),
			'Line:' . __LINE__ . ' The option text should be equal to the XML value.'
		);

		$this->assertThat(
			$option->value,
			$this->equalTo((string) $xmlopt['value']),
			'Line:' . __LINE__ . ' The option value should be equal to the value of the XML "value" attribute.'
		);
	}
}
