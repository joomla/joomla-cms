<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('checkboxes');

/**
 * Test class for JFormCheckboxes.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.3
 */
class JFormFieldCheckboxesTest extends TestCaseDatabase
{
	/**
	 * Sets up dependencies for the test.
	 *
	 * @since       11.3
	 *
	 * @return void
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
	 * Test the getInput method with no value and no checked attribute.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputNoValueNoChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		// Test with no value, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formFieldCheckboxes, 'element', $element);
		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red'
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" /> was missing.'
		);

		// Test that the 'blue' option exists
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue'
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" /> was missing.'
		);

		// Test that no option is checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'checked' => true
				)
			);

		$this->assertNotTag(
			$matcher,
			$result,
			'One or more inputs were checked.'
		);
	}

	/**
	 * Test the getInput method with one value selected and no checked attribute.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputValueNoChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		// Test with one value checked, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formFieldCheckboxes, 'element', $element);
		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'value', 'red');
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists and is checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red',
				'checked' => true
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" checked /> was missing.'
		);

		// Test that the 'blue' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" /> was missing.'
		);
	}

	/**
	 * Test the getInput method with one value that is an array and no checked attribute.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputValueArrayNoChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		// Test with one value checked, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		$valuearray = array('red');
		TestReflection::setValue($formFieldCheckboxes, 'element', $element);
		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'value', $valuearray);
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists and is checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red',
				'checked' => true
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" checked /> was missing.'
		);

		// Test that the 'blue' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" /> was missing.'
		);
	}

	/**
	 * Test the getInput method  with no value and one value in checked.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputNoValueOneChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');
		TestReflection::setValue($formFieldCheckboxes, 'checkedOptions', 'blue');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" /> was missing.'
		);

		// Test that the 'blue' option exists and is checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue',
				'checked' => true
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" checked /> was missing.'
		);
	}

	/**
	 * Test the getInput method with no value and two values in the checked element.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputNoValueTwoChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		// Test with nothing checked, two values in checked element
		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');
		TestReflection::setValue($formFieldCheckboxes, 'value', '""');
		TestReflection::setValue($formFieldCheckboxes, 'checkedOptions', 'red,blue');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" /> was missing.'
		);

		// Test that the 'blue' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" /> was missing.'
		);
	}

	/**
	 * Test the getInput method with one value and a different checked value.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputValueChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		// Test with one item checked, a different value in checked element
		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'value', 'red');
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');
		TestReflection::setValue($formFieldCheckboxes, 'checkedOptions', 'blue');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists and is checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red',
				'checked' => true
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" checked /> was missing.'
		);

		// Test that the 'blue' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" /> was missing.'
		);
	}

	/**
	 * Test the getInput method with multiple values, no checked.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetInputValuesNoChecked()
	{
		$formFieldCheckboxes = $this->getMock('JFormFieldCheckboxes', array('getOptions'));

		$option1 = new JObject;
		$option1->set('value', 'red');
		$option1->set('text', 'red');
		$option1->set('checked', false);

		$option2 = new JObject;
		$option2->set('value', 'blue');
		$option2->set('text', 'blue');
		$option2->set('checked', false);

		$optionsReturn = array($option1, $option2);
		$formFieldCheckboxes->expects($this->any())
			->method('getOptions')
			->will($this->returnValue($optionsReturn));

		// Test with two values checked, no checked element
		TestReflection::setValue($formFieldCheckboxes, 'id', 'myTestId');
		TestReflection::setValue($formFieldCheckboxes, 'value', 'yellow,green');
		TestReflection::setValue($formFieldCheckboxes, 'name', 'myTestName');

		// Get the result once, we will perform multiple tests
		$result = TestReflection::invoke($formFieldCheckboxes, 'getInput');

		// Test that the tag exists
		$matcher = array('id' => 'myTestId');

		$this->assertTag(
			$matcher,
			$result,
			'The tag did not have the correct id.'
		);

		// Test that the 'red' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'red',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="red" /> was missing.'
		);

		// Test that the 'blue' option exists and is not checked
		$matcher['descendant'] = array(
			'tag' => 'input',
			'attributes' => array(
				'type' => 'checkbox',
				'name' => 'myTestName',
				'value' => 'blue',
				'checked' => false
				)
			);

		$this->assertTag(
			$matcher,
			$result,
			'A descendant tag like <input type="checkbox" name="color" value="blue" /> was missing.'
		);
	}

	/**
	 * Test the getOptions method.
	 *
	 * @since       12.2
	 *
	 * @return void
	 */
	public function testGetOptions()
	{
		$formFieldCheckboxes = new JFormFieldCheckboxes;

		$option1 = new stdClass;
		$option1->value = 'yellow';
		$option1->text = 'yellow';
		$option1->disable = true;
		$option1->class = '';
		$option1->onclick = '';
		$option1->checked = false;
		$option1->selected = false;
		$option1->onchange = '';

		$option2 = new stdClass;
		$option2->value = 'green';
		$option2->text = 'green';
		$option2->disable = false;
		$option2->class = '';
		$option2->onclick = '';
		$option2->checked = true;
		$option2->selected = true;
		$option2->onchange = '';

		$optionsExpected = array($option1, $option2);

		// Test with two values checked, no checked element
		TestReflection::setValue(
			$formFieldCheckboxes, 'element', simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="yellow" disabled="true">yellow</option>
			<option value="green" checked="true">green</option>
			</field>')
		);

		$this->assertEquals(
			$optionsExpected,
			TestReflection::invoke($formFieldCheckboxes, 'getOptions'),
			'The field with two values did not produce the right options'
		);
	}
}
