<?php
/**
 * JFormTest.php -- unit testing file for JForm
 *
 * @version		$Id$
 * @package	Joomla.UnitTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JForm.
 *
 * @package	Joomla.UnitTest
 * @subpackage Utilities
 *
 */
class JFormTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Saves the Factory pointers
	 *
	 * @return void
	 */
	protected function saveFactoryState()
	{
		$this->factoryState['application'] = JFactory::$application;
		$this->factoryState['config'] = JFactory::$config;
		$this->factoryState['session'] = JFactory::$session;
		$this->factoryState['language'] = JFactory::$language;
		$this->factoryState['document'] = JFactory::$document;
		$this->factoryState['acl'] = JFactory::$acl;
		$this->factoryState['database'] = JFactory::$database;
		$this->factoryState['mailer'] = JFactory::$mailer;
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return void
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application = $this->factoryState['application'];
		JFactory::$config = $this->factoryState['config'];
		JFactory::$session = $this->factoryState['session'];
		JFactory::$language = $this->factoryState['language'];
		JFactory::$document = $this->factoryState['document'];
		JFactory::$acl = $this->factoryState['acl'];
		JFactory::$database = $this->factoryState['database'];
		JFactory::$mailer = $this->factoryState['mailer'];
	}

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
	 * Testing the static methods.
	 *
	 * @return void
	 */
	public function testAddFormPath()
	{
		// Check the default behaviour.
		$paths = JForm::addFormPath();

		// The default path is the class file folder/forms
		$valid = array(
			JPATH_LIBRARIES.'/joomla/form/forms'
		);

		$this->assertThat(
			$paths,
			$this->equalTo($valid)
		);

		// Test adding a custom folder.
		JForm::addFormPath(dirname(__FILE__));
		$paths = JForm::addFormPath();

		// The valid will be added to the start of the stack.
		array_unshift($valid, dirname(__FILE__));

		$this->assertThat(
			$paths,
			$this->equalTo($valid)
		);
	}

	/**
	 * Testing getInstance
	 *
	 * @return void
	 *
	 * @TODO implement getInstance tests
	 */
	public function testGetInstance()
	{
		//$form = JFormInspector::getInstance
	}

	/**
	 * Testing methods used by the instantiated object.
	 *
	 * @return void
	 */
	public function testConstruct()
	{
		// Check the empty contructor for basic errors.
		$form = new JFormInspector;

		$this->assertThat(
			($form instanceof JForm),
			$this->isTrue()
		);

		// Test that the default options sets array to false.
		$options = $form->getOptions();
		$valid = array('array' => false, 'prefix' => '%__');

		$this->assertThat(
			$options,
			$this->equalTo($valid)
		);

		// Check that the constructor will process an a change in the array option.
		$input = array(
			'array' => true,
		);
		$form = new JFormInspector($input);
		$options = $form->getOptions();

		$this->assertThat(
			$options,
			$this->equalTo($input)
		);
	}

	/**
	 * Testing addField
	 *
	 * @return void
	 *
	 * @TODO implement addField tests
	 */
	public function testAddField()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing addFields
	 *
	 * @return void
	 *
	 * @TODO implement addFields tests
	 */
	public function testAddFields()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing bind
	 *
	 * @return void
	 *
	 * @TODO implement bind tests
	 */
	public function testBind()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing filter
	 *
	 * @return void
	 */
	public function testFilter()
	{
		include_once JPATH_BASE . '/libraries/joomla/user/user.php';

		$user = new JUser;
		$mockSession = $this->getMock('JSession', array('_start', 'get'));
		$mockSession->expects($this->once())->method('get')->will(
			$this->returnValue($user)
		);
		JFactory::$session = $mockSession;
		// Adjust the timezone offset to a known value.
		$config = JFactory::getConfig();
		$config->setValue('config.offset', 10);

		// TODO: Mock JFactory and JUser
		//$user = JFactory::getUser();
		//$user->setParam('timezone', 5);

		$form = new JForm;
		$form->load('example');

		$text = '<script>alert();</script> <p>Some text</p>';
		$data = array(
			'f_text' => $text,
			'f_safe_text' => $text,
			'f_raw_text' => $text,
			'f_svr_date' => '2009-01-01 00:00:00',
			'f_usr_date' => '2009-01-01 00:00:00',
			'f_unset' => 1
		);

		$result = $form->filter($data);

		// Check that the unset filter worked.
		$this->assertThat(
			isset($result['f_text']),
			$this->isTrue()
		);

		$this->assertThat(
			isset($result['f_safe_text']),
			$this->isTrue()
		);

		$this->assertThat(
			isset($result['f_raw_text']),
			$this->isTrue()
		);

		$this->assertThat(
			isset($result['f_svr_date']),
			$this->isTrue()
		);

		$this->assertThat(
			isset($result['f_unset']),
			$this->isFalse()
		);

		// Check the date filters.
		$this->assertThat(
			$result['f_svr_date'],
			$this->equalTo('2008-12-31 14:00:00')
		);

		/*
		$this->assertThat(
			$result['f_usr_date'],
			$this->equalTo('2009-01-01 05:00:00')
		);
		*/

		// Check that text filtering worked.
		$this->assertThat(
			$result['f_raw_text'],
			$this->equalTo($text)
		);

		$this->assertThat(
			$result['f_text'],
			$this->equalTo('alert(); Some text')
		);

		$this->assertThat(
			$result['f_safe_text'],
			$this->equalTo('alert(); <p>Some text</p>')
		);

		$this->markTestIncomplete();
	}

	/**
	 * Testing getField
	 *
	 * @return void
	 *
	 * @TODO implement getField tests
	 */
	public function testGetField()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getFieldAttributes
	 *
	 * @return void
	 *
	 * @TODO implement getFieldAttributes tests
	 */
	public function testGetFieldAttributes()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getFields
	 *
	 * @return void
	 *
	 * @TODO implement getFields tests
	 */
	public function testGetFields()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getFieldSets
	 *
	 * @return void
	 *
	 * @TODO implement getFieldSets tests
	 */
	public function testGetFieldsets()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getGroups
	 *
	 * @return void
	 *
	 * @TODO implement getGroups tests
	 */
	public function testGetGroups()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getInput
	 *
	 * @return void
	 *
	 * @TODO implement getInput tests
	 */
	public function testGetInput()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getLabel
	 *
	 * @return void
	 *
	 * @TODO implement getLabel tests
	 */
	public function testGetLabel()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing getNameSetName
	 *
	 * @return void
	 *
	 * @TODO implement getNameSetName tests
	 */
	public function testGetNameSetName()
	{
		$form = new JForm;
		$input = 'j-form';

		// Check input = output.
		$form->setName($input);
		$name = $form->getName();

		$this->assertThat(
			$name,
			$this->equalTo($input)
		);
	}

	/**
	 * Testing getValue
	 *
	 * @return void
	 *
	 * @TODO implement getValue tests
	 */
	public function testGetValue()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing load
	 *
	 * @return void
	 *
	 * @TODO implement load tests
	 */
	public function testLoad()
	{
		$form = new JFormInspector;

		// Check empty data returns false.
		$result = $form->load('');
		$this->assertThat(
			$result,
			$this->isFalse()
		);

		// Check poorly formed xml returns false.
		$result = $form->load('<fields><field /></fields>');
		$this->assertThat(
			$result,
			$this->isFalse()
		);

		$result = $form->load('<form><fields /></form>');
		$this->assertThat(
			$result,
			$this->isFalse()
		);

		// Check loading of good string data.
		$result = $form->load('<form><fields><field name="field_name" /></fields></form>', false);
		$this->assertThat(
			$result,
			$this->isTrue()
		);

		// Check non-existent file fails.
		$result = $form->load('not_found');
		$this->assertThat(
			$result,
			$this->isFalse()
		);

		// Check loading of good file.
		JForm::addFormPath(dirname(__FILE__));
		$result = $form->load('example');
		$this->assertThat(
			$result,
			$this->isTrue()
		);

		// Reassemble the XML from the form object and compare with the original.
		$groups = $form->getGroups();
		$xml = '<form><fields>';
		foreach ($groups['_default'] as $elem)
		{
			$xml .= $elem->asXML();
		}
		$xml .= '</fields></form>';

		$original = new JSimpleXML;
		$original->loadFile(dirname(__FILE__).'/example.xml');

		$new = new JSimpleXML;
		$new->loadString($xml);

		$this->assertThat(
			$new->document->toString(),
			$this->equalTo($original->document->toString())
		);
	}

	/**
	 * Testing loadFieldsXML
	 *
	 * @return void
	 *
	 * @TODO implement loadFieldsXML tests
	 */
	public function testLoadFieldsXML()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing loadFieldType
	 *
	 * @return void
	 *
	 * @TODO implement loadFieldType tests
	 */
	public function testLoadFieldType()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing loadFolder
	 *
	 * @return void
	 *
	 * @TODO implement loadFolder tests
	 */
	public function testLoadFolder()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing removeField
	 *
	 * @return void
	 *
	 * @TODO implement removeField tests
	 */
	public function testRemoveField()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing removeGroup
	 *
	 * @return void
	 *
	 * @TODO implement removeGroup tests
	 */
	public function testRemoveGroup()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing setField
	 *
	 * @return void
	 *
	 * @TODO implement setField tests
	 */
	public function testSetField()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing setFieldAttribute
	 *
	 * @return void
	 *
	 * @TODO implement setFieldAttribute tests
	 */
	public function testSetFieldAttribute()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing setFields
	 *
	 * @return void
	 */
	public function testSetFields()
	{
		jimport('joomla.utilities.simplexml');

		// Prepare a sample XML document.
		$xml = new JSimpleXML;
		$xml->loadString('<fields><field name="field_name" /></fields>');
		$form = new JFormInspector;

		$fields = $xml->document->children();

		// Check the default group.
		$form->setFields($fields);

		// Use the inspector class to get the internal data.
		$groups = $form->getGroups();

		// Check the _default group has been added.
		$this->assertTrue(
			isset($groups['_default'])
		);

		// Check the field name has been added to the array.
		$this->assertTrue(
			isset($groups['_default']['field_name'])
		);

		// Check the field data.
		$this->assertThat(
			$groups['_default']['field_name'],
			$this->equalTo($fields[0])
		);
	}

	/**
	 * Testing setValue
	 *
	 * @return void
	 *
	 * @TODO implement setValue tests
	 */
	public function testSetValue()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Testing validate
	 *
	 * @return void
	 *
	 * @TODO implement validate tests
	 */
	public function testValidate()
	{
		$this->markTestIncomplete();
	}
}
