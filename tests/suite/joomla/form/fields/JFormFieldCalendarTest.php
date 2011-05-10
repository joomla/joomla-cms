<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Form
 */
class JFormFieldCalendarTest extends JoomlaTestCase
{
	/**
	 * Sets up dependancies for the test.
	 */
	protected function setUp()
	{
		jimport('joomla.form.form');
		jimport('joomla.form.formfield');
		require_once JPATH_PLATFORM.'/joomla/form/fields/calendar.php';
		include_once dirname(dirname(__FILE__)).'/inspectors.php';
		$this->saveFactoryState();
	}

	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	public function attributeData()
	{

		return array(
			/*
			* Test normal parameters
			*/
			'normal' => array(
				'myCalendarElement',
				'myCalendarId',
				'myValue',
				array(
					'format' => '%m-%Y-%d',
					'size' => '25',
					'maxlength' => '45',
					'class' => 'myClass',
					'readonly' => 'true',
					'disabled' => 'false',
					'onchange' => '',
					'filter' => ''
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%m-%Y-%d',
					array(
						'size' => '25',
						'maxlength' => '45',
						'class' => 'myClass',
						'readonly' => 'readonly',
					)
				)
			),

			/*
			* Non integer size
			*/
			'nonintsize' => array(
				'myCalendarElement',
				'myCalendarId',
				'myValue',
				array(
					'format' => '%m-%Y-%d',
					'size' => '25est',
					'maxlength' => 'forty five',
					'class' => 'myClass',
					'readonly' => 'true',
					'disabled' => 'false',
					'onchange' => '',
					'filter' => ''
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%m-%Y-%d',
					array(
						'size' => '25',
						'maxlength' => '0',
						'class' => 'myClass',
						'readonly' => 'readonly',
					)
				)
			),

			/*
			* No format provided
			*/
			'noformat' => array(
				'myCalendarElement',
				'myCalendarId',
				'myValue',
				array(
					'format' => '',
					'size' => '25est',
					'maxlength' => 'forty five',
					'class' => 'myClass',
					'readonly' => 'true',
					'disabled' => 'false',
					'onchange' => '',
					'filter' => ''
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%Y-%m-%d',
					array(
						'size' => '25',
						'maxlength' => '0',
						'class' => 'myClass',
						'readonly' => 'readonly',
					)
				)
			),

			/*
			* With an onchange value
			*/
			'onchange' => array(
				'myCalendarElement',
				'myCalendarId',
				'myValue',
				array(
					'format' => '',
					'size' => '25est',
					'maxlength' => 'forty five',
					'class' => 'myClass',
					'readonly' => 'true',
					'disabled' => 'false',
					'onchange' => 'This is my onchange value',
					'filter' => ''
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%Y-%m-%d',
					array(
						'size' => '25',
						'maxlength' => '0',
						'class' => 'myClass',
						'onchange' => 'This is my onchange value',
						'readonly' => 'readonly',
					)
				)
			),

			/*
			* With bad readonly value
			*/
			'bad_readonly' => array(
				'myCalendarElement',
				'myCalendarId',
				'myValue',
				array(
					'format' => '',
					'size' => '25est',
					'maxlength' => 'forty five',
					'class' => 'myClass',
					'readonly' => '1',
					'disabled' => 'false',
					'onchange' => 'This is my onchange value',
					'filter' => ''
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%Y-%m-%d',
					array(
						'size' => '25',
						'maxlength' => '0',
						'class' => 'myClass',
						'onchange' => 'This is my onchange value',
					)
				)
			),

			/*
			* disabled is true, no class
			*/
			'disabled_no_class' => array(
				'myCalendarElement',
				'myCalendarId',
				'myValue',
				array(
					'format' => '',
					'size' => '25est',
					'maxlength' => 'forty five',
					'class' => '',
					'readonly' => '1',
					'disabled' => 'true',
					'onchange' => 'This is my onchange value',
					'filter' => ''
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%Y-%m-%d',
					array(
						'size' => '25',
						'maxlength' => '0',
						'disabled' => 'disabled',
						'onchange' => 'This is my onchange value',
					)
				)
			),

			/*
			* value = 'NOW'
			*/
			'value_is_now' => array(
				'myCalendarElement',
				'myCalendarId',
				'NOW',
				array(
					'format' => '',
					'size' => '25est',
					'maxlength' => 'forty five',
					'class' => '',
					'readonly' => '1',
					'disabled' => 'true',
					'onchange' => 'This is my onchange value',
					'filter' => ''
				),
				array(
					'strftime(\'%Y-%m-%d\')',
					'myCalendarElement',
					'myCalendarId',
					'%Y-%m-%d',
					array(
						'size' => '25',
						'maxlength' => '0',
						'disabled' => 'disabled',
						'onchange' => 'This is my onchange value',
					)
				)
			)
		);
	}

	/**
	 * Tests various attribute methods - this method does not handle filters
	 * @dataProvider attributeData
	 */
	public function testGetInputAttributes($name, $id, $value, $element, $expectedParameters)
	{
		// we create stubs for config and session/user objects
		$config = new stdClass;
		JFactory::$config = $config;		// put the stub in place
		$sessionMock = $this->getMock('sessionMock', array('get'));

		require_once JPATH_PLATFORM.'/joomla/user/user.php';
		$userObject = new JUser;

		$sessionMock->expects($this->any())
					->method('get')
					->with('user')
					->will($this->returnValue($userObject));

		JFactory::$session = $sessionMock;	// put the stub in place

		// include our inspector which will allow us to manipulate and call protected methods and attributes
		require_once dirname(__FILE__).'/inspectors/JFormFieldCalendar.php';
		$calendar = new JFormFieldCalendarInspector;

		if ($expectedParameters[0] == 'strftime(\'%Y-%m-%d\')') {
			date_default_timezone_set('UTC');
			$expectedParameters[0] = strftime('%Y-%m-%d');
		}

		// setup our values from our data set
		$calendar->setProtectedProperty('element', $element);
		$calendar->setProtectedProperty('name', $name);
		$calendar->setProtectedProperty('id', $id);
		$calendar->setProtectedProperty('value', $value);

		// create the mock to implant into JHtml so that we can check our values
		$mock = $this->getMock('calendarHandler', array('calendar'));

		// setup the expectation with the values from the dataset
		$mock->expects($this->once())
			->method('calendar')
			->with($expectedParameters[0], $expectedParameters[1], $expectedParameters[2], $expectedParameters[3], $expectedParameters[4]);

		JHtml::register('calendar', array($mock, 'calendar'));		// register our mock with JHtml

		$calendar->getInput();			// invoke our method
		JHtml::unregister('jhtml..calendar');	// unregister the mock
	}

	public function testGetInputServer_UTC()
	{
		// we create stubs for config and session/user objects
		$config = new JObject;
		JFactory::$config = $config;		// put the stub in place
		$sessionMock = $this->getMock('sessionMock', array('get'));

		require_once JPATH_PLATFORM.'/joomla/user/user.php';
		$userObject = new JUser;

		$sessionMock->expects($this->any())
					->method('get')
					->with('user')
					->will($this->returnValue($userObject));

		JFactory::$session = $sessionMock;	// put the stub in place

		$languageMock = $this->getMock('languageMock', array('getTag'));
		$languageMock->expects($this->any())
			->method('getTag')
			->will($this->returnValue('en-GB'));

		JFactory::$language = $languageMock;	// put the stub in place

		// include our inspector which will allow us to manipulate and call protected methods and attributes
		require_once dirname(__FILE__).'/inspectors/JFormFieldCalendar.php';
		$calendar = new JFormFieldCalendarInspector;

		// setup our values from our data set
		$calendar->setProtectedProperty('element',
				array(
					'format' => '%m-%Y-%d',
					'size' => '25',
					'maxlength' => '45',
					'class' => 'myClass',
					'readonly' => 'true',
					'disabled' => 'false',
					'onchange' => '',
					'filter' => 'SERVER_UTC'
				)
		);
		$calendar->setProtectedProperty('name', 'myElementName');
		$calendar->setProtectedProperty('id', 'myElementId');
		$calendar->setProtectedProperty('value', 1269442718);   // 1269442718

		$config->set('offset', 'US/Eastern'); // -5
		$userObject->setParam('timezone', 'America/Buenos_Aires'); // -3

		// create the mock to implant into JHtml so that we can check our values
		$mock = $this->getMock('calendarHandler', array('calendar'));

		// setup the expectation with the values from the dataset
		$mock->expects($this->once())
			->method('calendar')
			->with('2010-03-24 10:58:38', 'myElementName', 'myElementId', '%m-%Y-%d',
				array(
					'size' => '25',
					'maxlength' => '45',
					'class' => 'myClass',
					'readonly' => 'readonly'
				)
			);

		JHtml::register('calendar', array($mock, 'calendar'));		// register our mock with JHtml

		$calendar->getInput();			// invoke our method
		JHtml::unregister('jhtml..calendar');	// unregister the mock
	}

	public function testGetInputUser_UTC()
	{
		// we create stubs for config and session/user objects
		$config = new JObject;
		JFactory::$config = $config;		// put the stub in place
		$sessionMock = $this->getMock('sessionMock', array('get'));

		require_once JPATH_PLATFORM.'/joomla/user/user.php';
		$userObject = new JUser;


		$sessionMock->expects($this->any())
					->method('get')
					->with('user')
					->will($this->returnValue($userObject));

		JFactory::$session = $sessionMock;	// put the stub in place

		$languageMock = $this->getMock('languageMock', array('getTag'));
		$languageMock->expects($this->any())
			->method('getTag')
			->will($this->returnValue('en-GB'));

		JFactory::$language = $languageMock;	// put the stub in place

		// include our inspector which will allow us to manipulate and call protected methods and attributes
		require_once dirname(__FILE__).'/inspectors/JFormFieldCalendar.php';
		$calendar = new JFormFieldCalendarInspector;

		// setup our values from our data set
		$calendar->setProtectedProperty('element',
				array(
					'format' => '%m-%Y-%d',
					'size' => '25',
					'maxlength' => '45',
					'class' => 'myClass',
					'readonly' => 'true',
					'disabled' => 'false',
					'onchange' => '',
					'filter' => 'USER_UTC'
				)
		);
		$calendar->setProtectedProperty('name', 'myElementName');
		$calendar->setProtectedProperty('id', 'myElementId');
		$calendar->setProtectedProperty('value', 1269442718);   // 1269442718

		$config->set('offset', 'Asia/Muscat'); // +4

		// we don't set the user param to see if it properly falls back to the server time (as it should)

		// create the mock to implant into JHtml so that we can check our values
		$mock = $this->getMock('calendarHandler', array('calendar'));

		// setup the expectation with the values from the dataset
		$mock->expects($this->once())
			->method('calendar')
			->with('2010-03-24 18:58:38', 'myElementName', 'myElementId', '%m-%Y-%d',
				array(
					'size' => '25',
					'maxlength' => '45',
					'class' => 'myClass',
					'readonly' => 'readonly'
				)
			);

		JHtml::register('calendar', array($mock, 'calendar'));		// register our mock with JHtml

		$calendar->getInput();			// invoke our method
		JHtml::unregister('jhtml..calendar');	// unregister the mock

		// create the mock to implant into JHtml so that we can check our values
		$mock2 = $this->getMock('calendarHandler', array('calendar'));

		// setup the expectation with the values from the dataset
		$mock2->expects($this->once())
			->method('calendar')
			->with('2010-03-24 22:58:38', 'myElementName', 'myElementId', '%m-%Y-%d',
				array(
					'size' => '25',
					'maxlength' => '45',
					'class' => 'myClass',
					'readonly' => 'readonly'
				)
			);

		$config->set('offset', 'US/Eastern'); // -5
		$userObject->setParam('timezone', 'Asia/Muscat'); // +4		// now we set the user param to test it out.

		JHtml::register('calendar', array($mock2, 'calendar'));		// register our mock with JHtml

		$calendar->getInput();			// invoke our method
		JHtml::unregister('jhtml..calendar');	// unregister the mock
	}

	/**
	 * Test the getInput method.
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="calendar" type="calendar" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldCalendar($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->markTestIncomplete('Problems encountered in next assertion');

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:'.__LINE__.' The getInput method should return something without error.'
		);

		// TODO: Should check all the attributes have come in properly.
	}
}
