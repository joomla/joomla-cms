<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('calendar');

/**
 * Test class for JForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldCalendarTest extends TestCase
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
		JFactory::$config      = $this->getMockConfig();
		JFactory::$document    = $this->getMockDocument();
		JFactory::$language    = $this->getMockLanguage();
		JFactory::$session     = $this->getMockSession();

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
	 * @return array
	 */
	public function getSetupData()
	{
		return array(
			/*
			 * Test normal parameters
			 */
			'normal' => array(
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'format' => '%m-%Y-%d',
					'maxlength' => '45',
					'filter' => 'SERVER_UTC',
				),
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'format' => '%m-%Y-%d',
					'maxlength' => 45,
					'filter' => 'SERVER_UTC',
				),
			),

			/*
			 * Non integer size
			 */
			'nonintsize' => array(
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'maxlength' => 'thirty five foo',
				),
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'maxlength' => 45,
				),
			),

			/*
			 * No format provided
			 */
			'noformat' => array(
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
				),
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'format' => '%Y-%m-%d',
				),
			),

			/*
			* No filter provided
			*/
			'nofilter' => array(
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
				),
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'filter' => 'USER_UTC',
				),
			),
		);
	}

	/**
	 * Test...
	 *
	 * @return array
	 */
	public function getInputAttributeData()
	{
		return array(
			/*
			* Test normal parameters
			*/
			'normal' => array(
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'value' => 'myValue',
					'format' => '%m-%Y-%d',
					'size' => 25,
					'maxlength' => 45,
					'class' => 'myClass',
					'readonly' => true,
					'disabled' => false,
					'onchange' => '',
					'filter' => '',
				),
				array(
					'myValue',
					'myCalendarElement',
					'myCalendarId',
					'%m-%Y-%d',
					array(
						'size' => 25,
						'maxlength' => 45,
						'class' => 'myClass',
						'readonly' => 'readonly',
					)
				)
			),

			/*
			 * value = 'NOW'
			 */
			'value_is_now' => array(
				array(
					'name' => 'myCalendarElement',
					'id' => 'myCalendarId',
					'value' => 'NOW',
					'format' => '%Y-%m-%d',
					'size' => 25,
					'maxlength' => 45,
					'disabled' => true,
					'onchange' => 'This is my onchange value',
					'filter' => '',
				),
				array(
					'strftime(\'%Y-%m-%d\')',
					'myCalendarElement',
					'myCalendarId',
					'%Y-%m-%d',
					array(
						'size' => 25,
						'maxlength' => 45,
						'disabled' => 'disabled',
						'onchange' => 'This is my onchange value',
					)
				)
			)
		);
	}

	/**
	 * Tests various attribute methods - this method does not handle filters
	 *
	 * @param   array  $element         @todo
	 * @param   array  $expectedResult  @todo
	 *
	 * @dataProvider getSetupData
	 *
	 * @return void
	 */
	/*public function testSetup($element, $expectedResult)
	{
		require_once JPATH_PLATFORM . '/joomla/form/fields/calendar.php';

		$elementStr = '';

		$userObject = new JUser;

		foreach ($element as $attr => $value)
		{
			$elementStr .= ' ' . $attr . '="' . $value . '"';
		}

		$element = '<field type="calendar" ' . $elementStr . " />";

		$field = new JFormFieldCalendar;
		$element = simplexml_load_string($element);

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		foreach ($expectedResult as $property => $value)
		{
			$this->assertThat(
				$field->$property,
				$this->equalTo($value),
				'Line:' . __LINE__ . ' The property ' . $property . ' should be computed from the XML.'
			);
		}
	}*/

	/**
	 * Tests various attribute methods - this method does not handle filters
	 *
	 * @param   array   $element             @todo
	 * @param   string  $expectedParameters  @todo
	 *
	 * @dataProvider getInputAttributeData
	 *
	 * @return void
	 */
	public function testGetInputAttributes($element, $expectedParameters)
	{
		// We create stubs for config and session/user objects
		$config = new JObject;

		// Put the stub in place
		JFactory::$config = $config;
		$sessionMock = $this->getMock('sessionMock', array('get'));

		$userObject = new JUser;

		$sessionMock->expects($this->any())
			->method('get')
			->with('user')
			->will($this->returnValue($userObject));

		// Put the stub in place
		JFactory::$session = $sessionMock;

		// Instantiate the calendar field
		$calendar = new JFormFieldCalendar;

		if ($expectedParameters[0] == 'strftime(\'%Y-%m-%d\')')
		{
			date_default_timezone_set('UTC');
			$expectedParameters[0] = strftime('%Y-%m-%d');
		}

		// Setup our values from our data set
		foreach ($element as $attr => $value)
		{
			TestReflection::setValue($calendar, $attr, $value);
		}

		// Create the mock to implant into JHtml so that we can check our values
		$mock = $this->getMock('calendarHandler', array('calendar'));

		// Setup the expectation with the values from the dataset
		$mock->expects($this->once())
			->method('calendar')
			->with($expectedParameters[0], $expectedParameters[1], $expectedParameters[2], $expectedParameters[3], $expectedParameters[4]);

		// Register our mock with JHtml
		JHtml::register('calendar', array($mock, 'calendar'));

		// Invoke our method
		TestReflection::invoke($calendar, 'getInput');

		// Unregister the mock
		JHtml::unregister('jhtml..calendar');
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetInputServer_UTC()
	{
		// Create a stub JConfig
		$config = new JObject;

		// Put the stub in place
		JFactory::$config = $config;
		$sessionMock = $this->getMock('sessionMock', array('get'));

		$userObject = new JUser;

		$sessionMock->expects($this->any())
			->method('get')
			->with('user')
			->will($this->returnValue($userObject));

		// Put the stub in place
		JFactory::$session = $sessionMock;

		// Instantiate the calendar field
		$calendar = new JFormFieldCalendar;

		// Setup our values from our data set
		TestReflection::setValue($calendar, 'id', 'myElementId');
		TestReflection::setValue($calendar, 'name', 'myElementName');
		TestReflection::setValue($calendar, 'format', '%m-%Y-%d');
		TestReflection::setValue($calendar, 'size', 25);
		TestReflection::setValue($calendar, 'maxlength', 45);
		TestReflection::setValue($calendar, 'class', 'myClass');
		TestReflection::setValue($calendar, 'readonly', true);
		TestReflection::setValue($calendar, 'disabled', false);
		TestReflection::setValue($calendar, 'onchange', '');
		TestReflection::setValue($calendar, 'filter', 'SERVER_UTC');

		// 1269442718
		TestReflection::setValue($calendar, 'value', 1269442718);

		// -5
		$config->set('offset', 'US/Eastern');

		// -3
		$userObject->setParam('timezone', 'America/Buenos_Aires');

		// Create the mock to implant into JHtml so that we can check our values
		$mock = $this->getMock('calendarHandler', array('calendar'));

		// Setup the expectation with the values from the dataset
		$mock->expects($this->once())
			->method('calendar')
			->with('2010-03-24 10:58:38', 'myElementName', 'myElementId', '%m-%Y-%d',
			array(
				'size' => 25,
				'maxlength' => 45,
				'class' => 'myClass',
				'readonly' => 'readonly'
			)
		);

		// Register our mock with JHtml
		JHtml::register('calendar', array($mock, 'calendar'));

		// Invoke our method
		TestReflection::invoke($calendar, 'getInput');

		// Unregister the mock
		JHtml::unregister('jhtml..calendar');
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetInputUser_UTC()
	{
		// Create a stub JConfig
		$config = new JObject;

		// Put the stub in place
		JFactory::$config = $config;
		$sessionMock = $this->getMock('sessionMock', array('get'));

		$userObject = new JUser;

		$sessionMock->expects($this->any())
			->method('get')
			->with('user')
			->will($this->returnValue($userObject));

		// Put the stub in place
		JFactory::$session = $sessionMock;

		// Instantiate the calendar field
		$calendar = new JFormFieldCalendar;

		// Setup our values from our data set
		TestReflection::setValue($calendar, 'id', 'myElementId');
		TestReflection::setValue($calendar, 'name', 'myElementName');
		TestReflection::setValue($calendar, 'format', '%m-%Y-%d');
		TestReflection::setValue($calendar, 'size', 25);
		TestReflection::setValue($calendar, 'maxlength', 45);
		TestReflection::setValue($calendar, 'class', 'myClass');
		TestReflection::setValue($calendar, 'readonly', true);
		TestReflection::setValue($calendar, 'disabled', false);
		TestReflection::setValue($calendar, 'onchange', '');
		TestReflection::setValue($calendar, 'filter', 'USER_UTC');

		// 1269442718
		TestReflection::setValue($calendar, 'value', 1269442718);

		// +4
		$config->set('offset', 'Asia/Muscat');

		// We don't set the user param to see if it properly falls back to the server time (as it should)

		// Create the mock to implant into JHtml so that we can check our values
		$mock = $this->getMock('calendarHandler', array('calendar'));

		// Setup the expectation with the values from the dataset
		$mock->expects($this->once())
			->method('calendar')
			->with('2010-03-24 18:58:38', 'myElementName', 'myElementId', '%m-%Y-%d',
			array(
				'size' => 25,
				'maxlength' => 45,
				'class' => 'myClass',
				'readonly' => 'readonly'
			)
		);

		// Register our mock with JHtml
		JHtml::register('calendar', array($mock, 'calendar'));

		// Invoke our method
		TestReflection::invoke($calendar, 'getInput');

		// Unregister the mock
		JHtml::unregister('jhtml..calendar');

		// Create the mock to implant into JHtml so that we can check our values
		$mock2 = $this->getMock('calendarHandler', array('calendar'));

		// Setup the expectation with the values from the dataset
		$mock2->expects($this->once())
			->method('calendar')
			->with('2010-03-24 22:58:38', 'myElementName', 'myElementId', '%m-%Y-%d',
			array(
				'size' => 25,
				'maxlength' => 45,
				'class' => 'myClass',
				'readonly' => 'readonly'
			)
		);

		// -5
		$config->set('offset', 'US/Eastern');

		// +4		// now we set the user param to test it out.
		$userObject->setParam('timezone', 'Asia/Muscat');

		// Register our mock with JHtml
		JHtml::register('calendar', array($mock2, 'calendar'));

		// Invoke our method
		TestReflection::invoke($calendar, 'getInput');

		// Unregister the mock
		JHtml::unregister('jhtml..calendar');
	}

	/**
	 * Test the getInput method.
	 *
	 * @return void
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="calendar" type="calendar" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldCalendar($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getInput method should return something without error.'
		);

		// TODO: Should check all the attributes have come in properly.
	}
}
