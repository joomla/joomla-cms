<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_TESTS . '/stubs/FormInspectors.php';
require_once __DIR__ . '/TestHelpers/JHtmlField-helper-dataset.php';
include_once 'JFormDataHelper.php';

/**
 * Test class for JFormField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldTest extends TestCaseDatabase
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
		require_once JPATH_PLATFORM . '/joomla/form/fields/checkboxes.php';

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
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getSetupData()
	{
		return JHtmlFieldTest_DataSet::$setupTest;
	}

	/**
	 * Tests the JFormField::__construct method
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
	 * Tests the JFormField::GetId method
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

		$xml = $form->getXml();
		$data = $xml->xpath('fields/fields[@name="params"]/field[@name="colours"]');
		$colours = array_pop($data);

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
	 * Tests the JFormField::getLabel method
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

		$xml = $form->getXml();
		$data = $xml->xpath('fields/field[@name="title"]');
		$title = array_pop($data);

		$this->assertThat(
			$field->setup($title, 'The title'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$matcher = array(
				'id'         => 'title_id-lbl',
				'tag'        => 'label',
				'attributes' => array(
						'for'   => 'title_id',
						'class' => 'hasTooltip required',
						'title' => '<strong>Title</strong><br />The title.'
					),
				'content'    => 'regexp:/Title.*\*/',
				'child'      => array(
						'tag'        => 'span',
						'attributes' => array('class' => 'star'),
						'content'    => 'regexp:/\*/'
					)
			);

		$this->assertTag(
			$matcher,
			$field->getLabel(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		// Not required
		$data = $xml->xpath('fields/fields[@name="params"]/field[@name="colours"]');
		$colours = array_pop($data);

		$this->assertThat(
			$field->setup($colours, 'id'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$matcher = array(
				'id'         => 'colours-lbl',
				'tag'        => 'label',
				'attributes' => array(
						'for'   => 'colours',
						'class' => ''
					),
				'content'    => 'colours'
			);

		$this->assertTag(
			$matcher,
			$field->getLabel(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		// Hidden field
		$data = $xml->xpath('fields/field[@name="id"]');
		$id = array_pop($data);

		$this->assertThat(
			$field->setup($id, 'id'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertEmpty(
			$field->getLabel(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests the JFormField::getTitle method
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

		$xml = $form->getXml();
		$data = $xml->xpath('fields/field[@name="title"]');
		$title = array_pop($data);

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
		$data = $xml->xpath('fields/field[@name="id"]');
		$id = array_pop($data);

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
	 * Tests the name, value, id, title, lalbel property setup by JFormField::setup method
	 *
	 * @param   array   $expected  @todo
	 * @param   string  $element   @todo
	 * @param   string  $value     @todo
	 * @param   string  $group     @todo
	 *
	 * @return void
	 *
	 * @dataProvider  getSetupData
	 */
	public function testSetup($expected, $element, $value, $group=null)
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string($element);

		$this->assertThat(
			$field->setup($element, $value, $group),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		// Matcher for the 'label' attribute
		$matcher = array(
				'id'         => 'myId-lbl',
				'tag'        => 'label',
				'attributes' => array(
						'for'   => 'myId',
						'class' => 'hasTooltip',
						'title' => '<strong>My Title</strong><br />The description.'
					),
				'content'    => 'regexp:/My Title/'
			);

		foreach ($expected as $attr => $value)
		{
			// Label is html use assertTag()
			if ($attr == 'label')
			{
				$this->assertTag(
					$matcher,
					$field->$attr,
					'Line:' . __LINE__ . ' The ' . $attr . ' property should be computed from the XML.'
				);
			}
			else
			{
				$this->assertThat(
					$field->$attr,
					$this->equalTo($value),
					'Line:' . __LINE__ . ' The ' . $attr . ' property should be computed from the XML.'
				);
			}
		}
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
	 * Test forcemultiple property setup by JFormField::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupForceMultiple()
	{
		$field = new JFormFieldCheckboxes;
		$element = simplexml_load_string('
			<field type="checkboxes" name="myName">
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
}
