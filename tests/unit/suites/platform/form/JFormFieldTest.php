<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/form/form.php';
require_once JPATH_PLATFORM . '/joomla/form/field.php';

/**
 * Test class for JFormField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @since       11.1
 */
class JFormFieldTest extends TestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();
		require_once JPATH_TESTS . '/stubs/FormInspectors.php';
		include_once 'JFormDataHelper.php';
	}

	/**
	 * Tear down test
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Tests the JFormField::__construct method
	 *
	 * @covers JFormField::__construct
	 *
	 * @return void
	 */
	public function testConstruct()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		$this->assertThat(
			$field instanceof JFormField,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The JFormField constuctor should return a JFormField object.'
		);

		$this->assertThat(
			$field->getForm(),
			$this->identicalTo($form),
			'Line:' . __LINE__ . ' The internal form should be identical to the variable passed in the contructor.'
		);

		// Add custom path.
		JForm::addFieldPath(__DIR__ . '/_testfields');

		JFormHelper::loadFieldType('foo.bar');
		$field = new FooFormFieldBar($form);
		$this->assertEquals(
			$field->type,
			'FooBar',
			'Line:' . __LINE__ . ' The field type should have been guessed by the constructor.'
		);

		JFormHelper::loadFieldType('foo');
		$field = new JFormFieldFoo($form);
		$this->assertEquals(
			$field->type,
			'Foo',
			'Line:' . __LINE__ . ' The field type should have been guessed by the constructor.'
		);

		JFormHelper::loadFieldType('modal_foo');
		$field = new JFormFieldModal_Foo($form);
		$this->assertEquals(
			$field->type,
			'Modal_Foo',
			'Line:' . __LINE__ . ' The field type should have been guessed by the constructor.'
		);
	}

	/**
	 * Tests the JFormField::__get method
	 *
	 * @return void
	 */
	public function testGet()
	{
		// Tested in testSetup.
	}

	/**
	 * Tests the JFormField::GetId method
	 *
	 * @covers JFormField::getId
	 *
	 * @return void
	 */
	public function testGetId()
	{
		$form = new JFormInspector('form1', array('control' => 'jform'));

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$colours = array_pop($xml->xpath('fields/fields[@name="params"]/field[@name="colours"]'));

		$this->assertThat(
			$field->setup($colours, 'red', 'params'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			// Use original 'id' and 'name' here (from XML definition of the form field)
			$field->getId((string) $colours['id'], (string) $colours['name']),
			$this->equalTo('jform_params_colours'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JFormField::getInput method
	 *
	 * @return void
	 */
	public function testGetInput()
	{
		// Tested in actual field types because this is an abstract method.
	}

	/**
	 * Tests the JFormField::getLabel method
	 *
	 * @covers JFormField::getLabel
	 *
	 * @return void
	 */
	public function testGetLabel()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$title = array_pop($xml->xpath('fields/field[@name="title"]'));

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$equals = '<label id="title_id-lbl" for="title_id" class="hasTip required" ' .
			'title="Title::The title.">Title<span class="star">&#160;*</span></label>';

		$this->assertThat(
			$field->getLabel(),
			$this->equalTo($equals),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		// Not required

		$colours = array_pop($xml->xpath('fields/fields[@name="params"]/field[@name="colours"]'));

		$this->assertThat(
			$field->setup($colours, 'id'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->getLabel(),
			$this->equalTo('<label id="colours-lbl" for="colours" class="">colours</label>'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		// Hidden field

		$id = array_pop($xml->xpath('fields/field[@name="id"]'));

		$this->assertThat(
			$field->setup($id, 'id'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->getLabel(),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JFormField::getTitle method
	 *
	 * @covers JFormField::getTitle
	 *
	 * @return void
	 */
	public function testGetTitle()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$title = array_pop($xml->xpath('fields/field[@name="title"]'));

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->getTitle(),
			$this->equalTo('Title'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		// Hidden field

		$id = array_pop($xml->xpath('fields/field[@name="id"]'));

		$this->assertThat(
			$field->setup($id, 'id'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->getTitle(),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JFormField::setForm method
	 *
	 * @covers JFormField::setForm
	 *
	 * @return void
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
			'Line:' . __LINE__ . ' The internal form should be identical to the last set.'
		);
	}

	/**
	 * Test an invalid argument for the JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @expectedException PHPUnit_Framework_Error
	 *
	 * @return void
	 */
	public function testSetupInvalidElement()
	{
		$form = new JFormInspector('form1');
		$field = new JFormFieldInspector($form);

		$wrong = 'wrong';
		$this->assertThat(
			$field->setup($wrong, 0),
			$this->isFalse(),
			'Line:' . __LINE__ . ' If not a form object, setup should return false.'
		);

	}

	/**
	 * Tests the JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetup()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load(JFormDataHelper::$loadFieldDocument),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldInspector($form);

		// Standard usage.

		$xml = $form->getXML();
		$title = array_pop($xml->xpath('fields/field[@name="title"]'));

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('title'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->value,
			$this->equalTo('The title'),
			'Line:' . __LINE__ . ' The value should be set from the setup method argument.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('title_id'),
			'Line:' . __LINE__ . ' The property should be set from the XML (non-alpha transposed to underscore).'
		);

		$this->assertThat(
			(string) $title['class'],
			$this->equalTo('inputbox required'),
			'Line:' . __LINE__ . ' The property should be set from the XML.'
		);

		$this->assertThat(
			$field->validate,
			$this->equalTo('none'),
			'Line:' . __LINE__ . ' The property should be set from the XML.'
		);

		$this->assertThat(
			$field->multiple,
			$this->isFalse(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->required,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->input,
			$this->equalTo(''),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$equals = '<label id="title_id-lbl" for="title_id" class="hasTip required" title="Title::The title.">' .
			'Title<span class="star">&#160;*</span></label>';

		$this->assertThat(
			$field->label,
			$this->equalTo($equals),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->title,
			$this->equalTo('Title'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->unexisting,
			$this->equalTo(null),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		// Test multiple attribute and form group name.

		$colours = array_pop($xml->xpath('fields/fields[@name="params"]/field[@name="colours"]'));

		$this->assertThat(
			$field->setup($colours, 'green', 'params'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('params_colours'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('params[colours][]'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->multiple,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertEquals(
			$field->group,
			'params',
			'Line:' . __LINE__ . ' The property should be set to the the group name.'
		);

		// Test hidden field type.

		$id = array_pop($xml->xpath('fields/field[@name="id"]'));

		$this->assertThat(
			$field->setup($id, 42),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hidden,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The hidden property should be set from the field type.'
		);

		// Test hidden attribute.

		$createdDate = array_pop($xml->xpath('fields/field[@name="created_date"]'));

		$this->assertThat(
			$field->setup($createdDate, '0000-00-00 00:00:00'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hidden,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The hidden property should be set from the hidden attribute.'
		);

		// Test automatic generated name.

		$spacer = array_pop($xml->xpath('fields/field[@type="spacer"]'));

		$this->assertThat(
			$field->setup($spacer, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('__field1'),
			'Line:' . __LINE__ . ' The spacer name should be set using an automatic generated name.'
		);

		// Test nested groups and forced multiple.

		$comment = array_pop($xml->xpath('fields/fields[@name="params"]/fields[@name="subparams"]/field[@name="comment"]'));
		$field->forceMultiple = true;

		$this->assertThat(
			$field->setup($comment, 'My comment', 'params.subparams'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('params_subparams_comment'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('params[subparams][comment]'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertEquals(
			$field->group,
			'params.subparams',
			'Line:' . __LINE__ . ' The property should be set to the the group name.'
		);

		$this->assertEquals(
			$field->element['class'],
			'required',
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}
}
