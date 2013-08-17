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
 * Test class for JFormField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldTest extends TestCase
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

		require_once JPATH_PLATFORM . '/joomla/form/fields/text.php';
		require_once JPATH_PLATFORM . '/joomla/form/fields/hidden.php';
		require_once JPATH_PLATFORM . '/joomla/form/fields/list.php';

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

		$equals = '<label id="title_id-lbl" for="title_id" class="hasTooltip required" ' .
			'title="<strong>Title</strong><br />The title.">Title<span class="star">&#160;*</span></label>';

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
	 * Tests the name, value, id, title, lalbel property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupNameValueIdTitleLabel()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" id="myId" label="My Title" description="The description."  value="Text Field" />');

		$this->assertThat(
			$field->setup($element, 'The text field.'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('myName'),
			'Line:' . __LINE__ . ' The name property should be computed from the XML.'
		);

		$this->assertThat(
			$field->value,
			$this->equalTo('The text field.'),
			'Line:' . __LINE__ . ' The value should be set from the setup method argument.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('myId'),
			'Line:' . __LINE__ . ' The id property should be set from the XML (non-alpha transposed to underscore).'
		);

		$this->assertThat(
			$field->title,
			$this->equalTo('My Title'),
			'Line:' . __LINE__ . ' The title property should be computed from the XML.'
		);

		$expectedLabel = '<label id="myId-lbl" for="myId" class="hasTooltip" title="<strong>My Title</strong><br />The description.">' .
			'My Title</label>';

		$this->assertThat(
			$field->label,
			$this->equalTo($expectedLabel),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);

		$this->assertThat(
			$field->unexisting,
			$this->equalTo(null),
			'Line:' . __LINE__ . ' The unexisting property should not exists.'
		);
	}

	/**
	 * Tests multiple attribute and form group name property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupMultipleAttributeFormGroup()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" id="myId"/>');

		$this->assertThat(
			$field->setup($element, 'green', 'params'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->id,
			$this->equalTo('params_myId'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('params[myName]'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertEquals(
			$field->group,
			'params',
			'Line:' . __LINE__ . ' The property should be set to the the group name.'
		);
	}

	/**
	 * Tests hidden field type property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupHiddenFieldType()
	{
		$field = new JFormFieldHidden;
		$element = simplexml_load_string(
			'<field name="myName" type="hidden" />');

		$this->assertThat(
			$field->setup($element, 42),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hidden,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The hidden property should be set from the field type.'
		);
	}

	/**
	 * Tests field's hidden attribute property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupHiddenFieldAttribute()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" hidden="true" />');

		$this->assertThat(
			$field->setup($element, 42),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hidden,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The hidden property should be set from the field type.'
		);
	}

	/**
	 * Test automatic generated name property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupAutoGeneratedName()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field type="text" label="Title" description="The title." />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('__field1'),
			'Line:' . __LINE__ . ' The spacer name should be set using an automatic generated name.'
		);
	}

	/**
	 * Test nested groups property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupNestedGroup()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field type="text" name="comment" />');

		$this->assertThat(
			$field->setup($element, 'My comment', 'params.subparams'),
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
	}

	/**
	 * Test disabled and readonly boolean attribute property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupDisabledReadonly()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field type="text" name="myName" disabled="true" readonly="true" />');

		$this->assertThat(
			$field->setup($element, 'User'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->disabled,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->readonly,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests multiple attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupMultipleAttribute()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" id="myId" multiple="true" />');

		$this->assertThat(
			$field->setup($element, 'green'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->multiple,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$element = simplexml_load_string(
			'<field name="myName" type="text" id="myId" multiple="multiple" />');

		$this->assertThat(
			$field->multiple,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$element = simplexml_load_string(
			'<field name="myName" type="text" id="myId" multiple="1" />');

		$this->assertThat(
			$field->multiple,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Test forcemultiple property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupForceMultiple()
	{
		$field = new JFormFieldList;
		$element = simplexml_load_string('
			<field type="list" name="myName">
				<option value="red">Red</option>
				<option value="blue">Blue</option>
			</field>
		');

		$field->forceMultiple = true;

		$this->assertThat(
			$field->setup($element, 'Comment'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->multiple,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be setted true forcefully.'
		);

		$this->assertThat(
			$field->name,
			$this->equalTo('myName[]'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Test class attribute property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupClass()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field type="text" name="myName" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->class,
			$this->equalTo(''),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$element = simplexml_load_string(
			'<field type="text" name="myName" class="inputbox" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->class,
			$this->equalTo('inputbox'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$element = simplexml_load_string(
			'<field type="text" name="myName" class="     inputbox      validate-numeric     " />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->class,
			$this->equalTo('inputbox validate-numeric'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests required attribute and label for required field setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupRequiredLabel()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" id="myId" required="true" label="My Title" description="The description." />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->required,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$expectedLabel = '<label id="myId-lbl" for="myId" class="hasTooltip required" title="<strong>My Title</strong><br />The description.">' .
			'My Title<span class="star">&#160;*</span></label>';

		$this->assertThat(
			$field->label,
			$this->equalTo($expectedLabel),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);
	}

	/**
	 * Tests autofocus, autocomplete, spellcheck boolean attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupAutofocusAutocompleteSpellcheck()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->autofocus,
			$this->isFalse(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->autocomplete,
			$this->equalTo('on'),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->spellcheck,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$element = simplexml_load_string(
			'<field name="myName" type="text" autofocus="true" autocomplete="false" spellcheck="false" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->autofocus,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->autocomplete,
			$this->isFalse(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->spellcheck,
			$this->isFalse(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests inputmode attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupInputmode()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" inputmode="latin numeric" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->inputmode,
			$this->equalTo("latin numeric"),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);
	}

	/**
	 * Tests size attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupSize()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" size="51" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->size,
			$this->equalTo(51),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);
	}

	/**
	 * Tests hint attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupHint()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" hint="Placeholder text." />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->hint,
			$this->equalTo('Placeholder text.'),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);
	}

	/**
	 * Tests validate attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupValidate()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->validate,
			$this->equalTo(null),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);

		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" validate="equals" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->validate,
			$this->equalTo('equals'),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);
	}

	/**
	 * Tests javascript onchange and onclick attribute setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupOnchangeOnclick()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" onchange="iamchanged(this);"  onclick="iamclicked(this);" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->onchange,
			$this->equalTo('iamchanged(this);'),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);

		$this->assertThat(
			$field->onclick,
			$this->equalTo('iamclicked(this);'),
			'Line:' . __LINE__ . ' The label property should be rendered correctly.'
		);
	}
}
