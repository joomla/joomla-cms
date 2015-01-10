<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JRouterInspector.php';

/**
 * Test class for JRouter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @since       3.1
 */
class JRouterTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JRouter
	 * @since  3.1
	 */
	protected $object;

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

		JUri::reset();

		$this->object = new JRouter;
	}

	/**
	 * Tests the getInstance() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetInstance()
	{
		// Check if a proper object is returned and that the mode is properly set
		JRouterInspector::clearInstanceCache();
		$object = JRouter::getInstance('administrator', array('mode' => 'test', 'randomKey' => 'randomValue'));
		$this->assertTrue(is_a($object, 'JRouterAdministrator'));
		$this->assertEquals($object->getMode(), 'test');

		// This test is commented, since the feature is not implemented (yet)
		// $this->assertEquals($object->get('randomKey'), 'randomValue');

		// Check if the same object is returned by getInstance()
		$object2 = JRouter::getInstance('administrator');
		$this->assertSame($object, $object2);

		require_once JPATH_TESTS . '/suites/libraries/cms/application/stubs/JApplicationHelperInspector.php';
		$apps      = JApplicationHelperInspector::get();
		$obj       = new stdClass();
		$obj->id   = 3;
		$obj->name = 'tester';
		$obj->path = dirname(__FILE__).'/example';
		$apps[3]   = $obj;
		JApplicationHelperInspector::set($apps);

		// Test if legacy app routers are still loaded
		$object3 = JRouter::getInstance('tester');
		$this->assertTrue(is_a($object, 'JRouter'));
	}

	/**
	 * Tests the getInstance() method throwing a proper exception
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @expectedException RuntimeException
	 */
	public function testGetInstanceException()
	{
		// Check if a proper exception is thrown if there is no router class
		$object = JRouter::getInstance('exception');
	}

	/**
	 * Cases for testParse
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesParse()
	{
		$cases   = array();
		$cases[] = array('', JROUTER_MODE_RAW, array(), array());
		$cases[] = array('index.php?var1=value1', JROUTER_MODE_RAW, array(), array());
		$cases[] = array('', JROUTER_MODE_RAW, array('var1' => 'value1'), array('var1' => 'value1'));

		$cases[] = array('', JROUTER_MODE_SEF, array(), array());
		$cases[] = array('index.php?var1=value1', JROUTER_MODE_SEF, array(), array());
		$cases[] = array('', JROUTER_MODE_SEF, array('var1' => 'value1'), array('var1' => 'value1'));

		return $cases;
	}

	/**
	 * Tests the parse method
	 *
	 * @param   array    $uri       An associative array with variables
	 * @param   integer  $mode      JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array    $vars      An associative array with global variables
	 * @param   array    $expected  Expected value
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParse
	 * @since         3.4
	 */
	public function testParse($url, $mode, $vars, $expected)
	{
		$this->object->setMode($mode);
		$this->object->setVars($vars);
		$uri = new JUri($url);

		$this->assertEquals($this->object->parse($uri), $expected);
	}

	/**
	 * Tests the build() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testBuild()
	{
		$uri    = new JUri('index.php?var1=value1');
		$result = $this->object->build('index.php?var1=value1');
		$this->assertEquals($uri, $result);

		$this->assertEquals($uri, $this->object->build('index.php?var1=value1'));

		$object = new JRouter;
		$object->setMode(JROUTER_MODE_SEF);
		$result = $object->build('index.php?var1=value1');
		$this->assertEquals($uri, $result);
	}

	/**
	 * Tests the getMode() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetMode()
	{
		$this->assertEquals($this->object->getMode(), JROUTER_MODE_RAW);

		$this->object->setMode(JROUTER_MODE_SEF);
		$this->assertEquals($this->object->getMode(), JROUTER_MODE_SEF);
	}

	/**
	 * Tests the setMode() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testSetMode()
	{
		$this->object->setMode(JROUTER_MODE_SEF);
		$this->assertEquals($this->object->getMode(), JROUTER_MODE_SEF);

		$this->object->setMode(42);
		$this->assertEquals($this->object->getMode(), 42);
	}

	/**
	 * Cases for testSetVar
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function casesSetVar()
	{
		$cases   = array();
		$cases[] = array(array(), 'myvar', 'myvalue', true, 'myvalue');
		$cases[] = array(array(), 'myvar', 'myvalue', false, null);
		$cases[] = array(array('myvar' => 'myvalue1'), 'myvar', 'myvalue2', true, 'myvalue2');
		$cases[] = array(array('myvar' => 'myvalue1'), 'myvar', 'myvalue2', false, 'myvalue2');

		return $cases;
	}

	/**
	 * Tests the setVar method
	 *
	 * @param   array    $vars      An associative array with variables
	 * @param   string   $var       The name of the variable
	 * @param   mixed    $value     The value of the variable
	 * @param   boolean  $create    If True, the variable will be created if it doesn't exist yet
	 * @param   string   $expected  Expected return value
	 *
	 * @return  void
	 *
	 * @dataProvider  casesSetVar
	 * @since         3.1
	 */
	public function testSetVar($vars, $var, $value, $create, $expected)
	{
		$this->object->setVars($vars, false);
		$this->object->setVar($var, $value, $create);
		$this->assertEquals($this->object->getVar($var), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Tests the setVars() method
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testSetVars()
	{
		$this->assertEquals($this->object->getVars(), array());
		$this->object->setVars(array('var1' => 'value1'));
		$this->assertEquals($this->object->getVars(), array('var1' => 'value1'));

		$this->object->setVars(array('var2' => 'value2'));
		$this->assertEquals($this->object->getVars(), array('var1' => 'value1', 'var2' => 'value2'));

		$this->object->setVars(array('var3' => 'value3'), false);
		$this->assertEquals($this->object->getVars(), array('var3' => 'value3'));

		$this->object->setVars(array(), false);
		$this->assertEquals($this->object->getVars(), array());
	}

	/**
	 * Cases for testGetVar
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesGetVar()
	{
		$cases   = array();
		$cases[] = array(array(), 'myvar', 'myvalue', true, 'myvalue');
		$cases[] = array(array(), 'myvar', 'myvalue', false, null);
		$cases[] = array(array('myvar' => 'myvalue1'), 'myvar', 'myvalue2', true, 'myvalue2');
		$cases[] = array(array('myvar' => 'myvalue1'), 'myvar', 'myvalue2', false, 'myvalue2');

		return $cases;
	}

	/**
	 * Tests the getVar method
	 *
	 * @param   array    $vars      An associative array with variables
	 * @param   string   $var       The name of the variable
	 * @param   mixed    $value     The value of the variable
	 * @param   boolean  $create    If True, the variable will be created if it doesn't exist yet
	 * @param   string   $expected  Expected return value
	 *
	 * @return  void
	 *
	 * @dataProvider  casesGetVar
	 * @since         3.4
	 */
	public function testGetVar($vars, $var, $value, $create, $expected)
	{
		$this->object->setVars($vars, false);
		$this->object->setVar($var, $value, $create);
		$this->assertEquals($this->object->getVar($var), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Tests the getVars() method
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testGetVars()
	{
		$this->assertEquals($this->object->getVars(), array());
		$this->object->setVars(array('var1' => 'value1'));
		$this->assertEquals($this->object->getVars(), array('var1' => 'value1'));

		$this->object->setVars(array('var2' => 'value2'));
		$this->assertEquals($this->object->getVars(), array('var1' => 'value1', 'var2' => 'value2'));

		$this->object->setVars(array('var3' => 'value3'), false);
		$this->assertEquals($this->object->getVars(), array('var3' => 'value3'));

		$this->object->setVars(array(), false);
		$this->assertEquals($this->object->getVars(), array());
	}

	/**
	 * Cases for testAttachBuildRule
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesAttachBuildRule()
	{
		$cases = array();
		$cases[] = array(array(), JRouter::PROCESS_DURING,
			array(
				'buildpreprocess' => array(),
				'build' => array(),
				'buildpostprocess' => array(),
				'parsepreprocess' => array(),
				'parse' => array(),
				'parsepostprocess' => array()
			)
		);
		$callbacks = array(function (&$router, &$uri) {});
		$cases[] = array($callbacks, JRouter::PROCESS_DURING,
			array(
				'buildpreprocess' => array(),
				'build' => $callbacks,
				'buildpostprocess' => array(),
				'parsepreprocess' => array(),
				'parse' => array(),
				'parsepostprocess' => array()
			)
		);
		$cases[] = array($callbacks, JRouter::PROCESS_BEFORE,
			array(
				'buildpreprocess' => $callbacks,
				'build' => array(),
				'buildpostprocess' => array(),
				'parsepreprocess' => array(),
				'parse' => array(),
				'parsepostprocess' => array()
			)
		);
		$cases[] = array($callbacks, JRouter::PROCESS_AFTER,
			array(
				'buildpreprocess' => array(),
				'build' => array(),
				'buildpostprocess' => $callbacks,
				'parsepreprocess' => array(),
				'parse' => array(),
				'parsepostprocess' => array()
			)
		);

		return $cases;
	}

	/**
	 * Tests the attachBuildRule method
	 *
	 * @param   callback  $callbacks   Callbacks to be attached
	 * @param   string    $stage       Stage to process
	 * @param   array     $expected    The expected internal rules array
	 *
	 * @return  void
	 *
	 * @dataProvider  casesAttachBuildRule
	 * @since         3.4
	 */
	public function testAttachBuildRule($callbacks, $stage, $expected)
	{
		$object = new JRouterInspector;

		foreach ($callbacks as $callback)
		{
			$object->attachBuildRule($callback, $stage);
		}

		$this->assertEquals($object->getRules(), $expected);
	}

	/**
	 * Tests the attachBuildRule() method throwing a proper exception
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @expectedException InvalidArgumentException
	 */
	public function testAttachBuildRuleException()
	{
		$callback = array(function (&$router, &$uri) {});
		$this->object->attachBuildRule($callback, 'wrongStage');
	}

	/**
	 * Cases for testAttachParseRule
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesAttachParseRule()
	{
		$cases     = array();
		$cases[]   = array(array(), JRouter::PROCESS_DURING,
			array(
				'buildpreprocess' => array(),
				'build' => array(),
				'buildpostprocess' => array(),
				'parsepreprocess' => array(),
				'parse' => array(),
				'parsepostprocess' => array()
			)
		);
		$callbacks = array(function (&$router, &$uri) {});
		$cases[] = array($callbacks, JRouter::PROCESS_DURING,
			array(
				'buildpreprocess' => array(),
				'build' => array(),
				'buildpostprocess' => array(),
				'parsepreprocess' => array(),
				'parse' => $callbacks,
				'parsepostprocess' => array()
			)
		);
		$cases[] = array($callbacks, JRouter::PROCESS_BEFORE,
			array(
				'buildpreprocess' => array(),
				'build' => array(),
				'buildpostprocess' => array(),
				'parsepreprocess' => $callbacks,
				'parse' => array(),
				'parsepostprocess' => array()
			)
		);
		$cases[] = array($callbacks, JRouter::PROCESS_AFTER,
			array(
				'buildpreprocess' => array(),
				'build' => array(),
				'buildpostprocess' => array(),
				'parsepreprocess' => array(),
				'parse' => array(),
				'parsepostprocess' => $callbacks
			)
		);

		return $cases;
	}

	/**
	 * Tests the attachParseRule method
	 *
	 * @param   callback  $callbacks   Callbacks to be attached
	 * @param   string    $stage       Stage to process
	 * @param   array     $expected    The expected internal rules array
	 *
	 * @return  void
	 *
	 * @dataProvider  casesAttachParseRule
	 * @since         3.4
	 */
	public function testAttachParseRule($callbacks, $stage, $expected)
	{
		$object = new JRouterInspector;

		foreach ($callbacks as $callback)
		{
			$object->attachParseRule($callback, $stage);
		}

		$this->assertEquals($object->getRules(), $expected);
	}

	/**
	 * Tests the attachParseRule() method throwing a proper exception
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @expectedException InvalidArgumentException
	 */
	public function testAttachParseRuleException()
	{
		$callback = array(function (&$router, &$uri) {});
		$this->object->attachParseRule($callback, 'wrongStage');
	}

	/**
	 * Cases for testProcessParseRules
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function casesProcessParseRules()
	{
		$cases   = array();
		$cases[] = array(array(), JRouter::PROCESS_DURING, array());
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('myvar' => 'myvalue');
				}
			), JRouter::PROCESS_DURING,
			array('myvar' => 'myvalue')
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('myvar1' => 'myvalue1');
				},
				function (&$router, &$uri)
				{
					return array('myvar2' => 'myvalue2');
				},
			), JRouter::PROCESS_DURING,
			array('myvar1' => 'myvalue1', 'myvar2' => 'myvalue2')
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('myvar1' => 'myvalue1');
				},
				function (&$router, &$uri)
				{
					return array('myvar2' => 'myvalue2');
				},
				function (&$router, &$uri)
				{
					return array('myvar1' => 'myvalue3');
				},
			), JRouter::PROCESS_DURING,
			array('myvar1' => 'myvalue1', 'myvar2' => 'myvalue2')
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('stage' => 'before');
				}
			), JRouter::PROCESS_BEFORE,
			array('stage' => 'before')
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('stage' => 'after');
				}
			), JRouter::PROCESS_AFTER,
			array('stage' => 'after')
		);

		return $cases;
	}

	/**
	 * testProcessParseRules().
	 *
	 * @param   array   $functions  Callback to execute
	 * @param   string  $stage      Stage to process
	 * @param   string  $expected   Expected return value
	 *
	 * @return  void
	 *
	 * @dataProvider  casesProcessParseRules
	 * @since         3.4
	 */
	public function testProcessParseRules($functions, $stage, $expected)
	{
		$myuri = 'http://localhost';
		$stub = $this->getMock('JRouter', array('parseRawRoute'));
		$stub->expects($this->any())->method('parseRawRoute')->will($this->returnValue(array()));

		foreach ($functions as $function)
		{
			$stub->attachParseRule($function, $stage);
		}

		$this->assertEquals($stub->parse($myuri), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Tests the attachParseRule() method throwing a proper exception
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @expectedException InvalidArgumentException
	 */
	public function testProcessParseRulesException()
	{
		$object = new JRouterInspector;
		$object->runProcessParseRules(new JUri, 'wrongStage');
	}

	/**
	 * Cases for testProcessBuildRules
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesProcessBuildRules()
	{
		$cases   = array();
		$cases[] = array(array(), JRouter::PROCESS_DURING, 'index.php?var1=value1&var2=value2');
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					$uri->setPath('value1');
				}
			), JRouter::PROCESS_DURING,
			'value1?var1=value1&var2=value2'
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					$uri->setPath('value1');
				},
				function (&$router, &$uri)
				{
					$uri->delVar('var1');
				},
			), JRouter::PROCESS_DURING,
			'value1?var2=value2'
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					$uri->setPath('value1/value2');
				},
				function (&$router, &$uri)
				{
					$uri->delVar('var1');
				},
				function (&$router, &$uri)
				{
					$uri->delVar('var2');
				},
			), JRouter::PROCESS_DURING,
			'value1/value2'
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					$uri->setVar('stage', 'during');
				}
			), JRouter::PROCESS_DURING,
			'index.php?var1=value1&var2=value2&stage=during'
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					$uri->setVar('stage', 'before');
				}
			), JRouter::PROCESS_BEFORE,
			'index.php?var1=value1&var2=value2&stage=before'
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					$uri->setVar('stage', 'after');
				}
			), JRouter::PROCESS_AFTER,
			'index.php?var1=value1&var2=value2&stage=after'
		);

		return $cases;
	}

	/**
	 * testProcessBuildRules().
	 *
	 * @param   array   $functions  Callback to execute
	 * @param   string  $stage      Stage to process
	 * @param   string  $expected   Expected return value
	 *
	 * @dataProvider casesProcessBuildRules
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testProcessBuildRules($functions, $stage, $expected)
	{
		$myuri = 'index.php?var1=value1&var2=value2';
		$stub  = $this->getMock('JRouter', array('buildRawRoute'));
		$stub->expects($this->any())->method('buildRawRoute')->will($this->returnValue(array()));

		foreach ($functions as $function)
		{
			$stub->attachBuildRule($function);
		}

		$juri = $stub->build($myuri);
		$this->assertEquals($juri->toString(), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Tests the attachBuildRules() method throwing a proper exception
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @expectedException InvalidArgumentException
	 */
	public function testProcessBuildRulesException()
	{
		$object = new JRouterInspector;
		$object->runProcessBuildRules(new JUri, 'wrongStage');
	}

	/**
	 * Cases for testCreateURI
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesCreateURI()
	{
		$cases = array();

		$cases[] = array('', array(), '');
		$cases[] = array('index.php', array(), 'index.php');

		$cases[] = array(array('var1' => 'value1', 'var2' => 'value2'), array(), 'index.php?var1=value1&var2=value2');
		$cases[] = array(array('var1' => 'value1', 'var2' => 'value2'), array('var3' => 'value3'), 'index.php?var3=value3&var1=value1&var2=value2');
		$cases[] = array(array('var1' => 'value1', 'var2' => 'value2'), array('var2' => 'value3'), 'index.php?var2=value2&var1=value1');

		$cases[] = array('&var1=value1&var2=value2', array(), 'index.php?var1=value1&var2=value2');
		$cases[] = array('&var1=value1&var2=value2', array('var3' => 'value3'), 'index.php?var3=value3&var1=value1&var2=value2');
		$cases[] = array('&var1=value1&var2=value2', array('var2' => 'value3'), 'index.php?var2=value2&var1=value1');

		$cases[] = array('&var1=value1&var2=', array(), 'index.php?var1=value1');

		$cases[] = array('&amp;var1=value1&amp;var2=value2', array(), 'index.php?var1=value1&var2=value2');

		return $cases;
	}

	/**
	 * Tests createURI() method
	 *
	 * @param   array   $url         valid inputs to the createURI() method
	 * @param   array   $globalVars  global Vars that should be merged into the URL
	 * @param   string  $expected    expected URI string
	 *
	 * @dataProvider casesCreateURI
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testCreateURI($url, $globalVars, $expected)
	{
		$object = new JRouterInspector;
		$object->setVars($globalVars, false);
		$juri = $object->runCreateURI($url);

		$this->assertTrue(is_a($juri, 'JUri'));
		$this->assertEquals($expected, $juri->toString());
	}

	/**
	 * Cases for testEncodeSegments
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesEncodeSegments()
	{
		$cases   = array();
		$cases[] = array(array('test'), array('test'));
		$cases[] = array(array('1:test'), array('1-test'));
		$cases[] = array(array('test', '1:test'), array('test', '1-test'));
		$cases[] = array(array('42:test', 'testing:this:method'), array('42-test', 'testing-this-method'));

		return $cases;
	}

	/**
	 * Tests encodeSegments() method
	 *
	 * @param   array   $segments   Array of unencoded segments of a URL
	 * @param   string  $expected   Array of encoded segments of a URL
	 *
	 * @dataProvider casesEncodeSegments
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testEncodeSegments($segments, $expected)
	{
		$object = new JRouterInspector;
		$this->assertEquals($object->runEncodeSegments($segments), $expected);
	}

	/**
	 * Cases for testDecodeSegments
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesDecodeSegments()
	{
		$cases   = array();
		$cases[] = array(array('test'), array('test'));
		$cases[] = array(array('1-test'), array('1:test'));
		$cases[] = array(array('test', '1-test'), array('test', '1:test'));
		$cases[] = array(array('42-test', 'testing-this-method'), array('42:test', 'testing:this-method'));

		return $cases;
	}

	/**
	 * Tests decodeSegments() method
	 *
	 * @param   array   $segments   Array of encoded segments of a URL
	 * @param   string  $expected   Array of decoded segments of a URL
	 *
	 * @dataProvider casesDecodeSegments
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testDecodeSegments($segments, $expected)
	{
		$object = new JRouterInspector;
		$this->assertEquals($object->runDecodeSegments($segments), $expected);
	}
}
