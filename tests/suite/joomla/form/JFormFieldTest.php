<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/form/form.php';
require_once JPATH_PLATFORM.'/joomla/form/formfield.php';

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Form
 */
class JFormFieldTest extends JoomlaTestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->saveFactoryState();
		jimport('joomla.form.form');
		include_once 'inspectors.php';
		include_once 'JFormDataHelper.php';
	}

	/**
	 * Tear down test
	 *
	 * @return void
	 */
	function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Tests the JForm::__construct method
	 */
	public function testConstruct()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		$this->assertThat(
			$field instanceof JFormField,
			$this->isTrue(),
			'Line:'.__LINE__.' The JFormField constuctor should return a JFormField object.'
		);

		$this->assertThat(
			$field->getForm(),
			$this->identicalTo($form),
			'Line:'.__LINE__.' The internal form should be identical to the variable passed in the contructor.'
		);
	}

	/**
	 * Tests the JForm::__get method
	 */
	public function testGet()
	{
		// Tested in testSetup.
	}

	/**
	 * Tests the JForm::GetId method
	 */
	public function testGetId()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$colours = array_pop($xml->xpath('fields/fields[@name="params"]/field[@name="colours"]'));

		$this->assertThat(
			$field->setup($colours, 'red', 'params'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			// use original 'id' and 'name' here (from XML definition of the form field)
			$field->getId((string) $colours['id'], (string) $colours['name']),
			$this->equalTo('params_colours'),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JForm::getInput method
	 */
	public function testGetInput()
	{
		// Tested in actual field types because this is an abstract method.
	}

	/**
	 * Tests the JForm::getLabel method
	 */
	public function testGetLabel()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$title = array_pop($xml->xpath('fields/field[@name="title"]'));

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->getLabel(),
			$this->equalTo('<label id="title_id-lbl" for="title_id" class="hasTip required" title="Title::The title.">Title<span class="star">&#160;*</span></label>'),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JFormField::getTitle method
	 */
	public function testGetTitle()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$title = array_pop($xml->xpath('fields/field[@name="title"]'));

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->getTitle(),
			$this->equalTo('Title'),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JForm::setForm method
	 */
	public function testSetForm()
	{
		$form1 = new JFormInspector('form1');
		$form2 = new JFormInspector('form2');

		$field = new JFormFieldInspector($form1);
		$field->setForm($form2);

		$this->assertThat(
			$field->getForm(),
			$this->identicalTo($form2),
			'Line:'.__LINE__.' The internal form should be identical to the last set.'
		);
	}

	/**
	 * Tests the JForm::setup method
	 */
	public function testSetup()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Error handling.

		$wrong = 'wrong';
		$this->assertThat(
			$field->setup($wrong, 0),
			$this->isFalse(),
			'Line:'.__LINE__.' If not a form object, setup should return false.'
		);

		// Standard usage.

		$xml = $form->getXML();
		$title = array_pop($xml->xpath('fields/field[@name="title"]'));

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('title'),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->value,
			$this->equalTo('The title'),
			'Line:'.__LINE__.' The value should be set from the setup method argument.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('title_id'),
			'Line:'.__LINE__.' The property should be set from the XML (non-alpha transposed to underscore).'
		);

		$this->assertThat(
			(string) $title['class'],
			$this->equalTo('inputbox required'),
			'Line:'.__LINE__.' The property should be set from the XML.'
		);

		$this->assertThat(
			$field->validate,
			$this->equalTo('none'),
			'Line:'.__LINE__.' The property should be set from the XML.'
		);

		$this->assertThat(
			$field->multiple,
			$this->isFalse(),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->required,
			$this->isTrue(),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);

		// Test multiple attribute and form group name.

		$colours = array_pop($xml->xpath('fields/fields[@name="params"]/field[@name="colours"]'));

		$this->assertThat(
			$field->setup($colours, 'green', 'params'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('params_colours'),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('params[colours][]'),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->multiple,
			$this->isTrue(),
			'Line:'.__LINE__.' The property should be computed from the XML.'
		);

		$this->assertEquals(
			$field->group,
			'params',
			'Line:'.__LINE__.' The property should be set to the the group name.'
		);

		// Test hidden field type.

		$id = array_pop($xml->xpath('fields/field[@name="id"]'));

		$this->assertThat(
			$field->setup($id, 42),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hidden,
			$this->isTrue(),
			'Line:'.__LINE__.' The hidden property should be set from the field type.'
		);

		// Test hidden attribute.

		$createdDate = array_pop($xml->xpath('fields/field[@name="created_date"]'));

		$this->assertThat(
			$field->setup($createdDate, '0000-00-00 00:00:00'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hidden,
			$this->isTrue(),
			'Line:'.__LINE__.' The hidden property should be set from the hidden attribute.'
		);

		// Test automatic generated name.

		$spacer = array_pop($xml->xpath('fields/field[@type="spacer"]'));

		$this->assertThat(
			$field->setup($spacer, ''),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('__field1'),
			'Line:'.__LINE__.' The spacer name should be set using an automatic generated name.'
		);
	}
}
