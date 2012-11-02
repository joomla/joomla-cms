<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldPlugins.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.4
 */
class JFormFieldPluginsTest extends TestCaseDatabase
{
	/**
	 * Sets up dependencies for the test.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function setUp()
	{
		// Do not call parent method, it causes unit test failure because of an undefined constant from the CMS

		require_once JPATH_PLATFORM . '/joomla/form/fields/plugins.php';
		include_once dirname(__DIR__) . '/inspectors.php';
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml  dataset
	 *
	 * @since   12.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/testfiles/JFormField.xml');
	}

	/**
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="editors" type="plugins" folder="editors" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldPlugins($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		if (!is_null(self::$driver))
		{
			$this->assertThat(
				strlen($field->input),
				$this->greaterThan(0),
				'Line:' . __LINE__ . ' The getInput method should return something without error.'
			);
		}
		else
		{
			$this->markTestSkipped();
		}

		// TODO: Should check all the attributes have come in properly.
	}
}
