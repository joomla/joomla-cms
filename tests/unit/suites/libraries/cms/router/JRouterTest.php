<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRouter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @group       Router
 * @since       3.1
 */
class JRouterTest extends TestCase
{
	/**
	 * Backup of the $_SERVER variable
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $server;

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
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->server = $_SERVER;

		/*
		 * The following is needed to work around a bug in JApplicationWeb::detectRequestUri()
		 * @see https://github.com/joomla-projects/joomla-pythagoras/issues/2
		 */
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = '';
		}

		$this->object = new JRouter;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   4.0
	 */
	protected function tearDown()
	{
		$_SERVER = $this->server;
		unset($this->server);
		unset($this->object);

		parent::tearDown();
	}

	/**
	 * Tests the getInstance() method
	 *
	 * @return  void
	 * @testdox JRouter::getInstance() returns a (cached) instance of a router object
	 * @since   4.0
	 */
	public function testGetInstance()
	{
		$object = JRouter::getInstance('administrator');
		$this->assertInstanceOf('JRouterAdministrator', $object);

		$this->assertSame($object, JRouter::getInstance('administrator'));
	}

	/**
	 * Tests the exception thrown by the getInstance() method
	 * 
	 * @return  void
	 * @since   3.4
	 * @expectedException RuntimeException
	 * @testdox getInstance() throws a RuntimeException, if a router for an unknown client is requested
	 */
	public function testGetInstanceException()
	{
		JRouter::getInstance('unknown');
	}

	/**
	 * Cases for testParse
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public function casesParse()
	{
		return array(
			// Empty URLs create an empty result
			'empty-urls-empty-result' => array(
				array(),
				array(),
				false,
				'',
				array(),
				array()
			),
			// Empty URLs and rule can create a result
			'empty-url-rule-nonempty-result' => array(
				array(
					function (&$router, &$uri)
					{
						$uri->setVar('result', '1');
					}
				),
				array(JRouter::PROCESS_DURING),
				false,
				'',
				array('result' => '1'),
				array()
			),
			// Vars are stored in the object
			'store-vars-in-object' => array(
				array(
					function (&$router, &$uri)
					{
						$uri->setVar('result', '1');
					}
				),
				array(JRouter::PROCESS_DURING),
				true,
				'',
				array('result' => '1'),
				array('result' => '1')
			),
			// absolute URL with no query params returns empty result
			'abs-no-query-no-result' => array(
				array(),
				array(),
				true,
				'http://www.example.test',
				array(),
				array()
			),
			// URL with query params returns result
			'query-result' => array(
				array(),
				array(),
				false,
				'?result=1&test=true',
				array('result' => '1', 'test' => 'true'),
				array()
			),
			// URL with query params returns result
			'query-result-setvar' => array(
				array(),
				array(),
				true,
				'?result=1&test=true',
				array('result' => '1', 'test' => 'true'),
				array('result' => '1', 'test' => 'true')
			),
			// Several rules are applied to one URL
			'several-rules-applied-to-url' => array(
				array(
					function (&$router, &$uri)
					{
						$uri->setVar('rule1', '1');
					},
					function (&$router, &$uri)
					{
						$uri->setVar('rule2', '1');
					}
				),
				array(JRouter::PROCESS_DURING, JRouter::PROCESS_DURING),
				false,
				'?result=1&test=true',
				array('result' => '1', 'test' => 'true', 'rule1' => '1', 'rule2' => '1'),
				array()
			),
			// Several rules are applied to one URL - ordered
			'several-rules-applied-to-url-ordered' => array(
				array(
					function (&$router, &$uri)
					{
						$uri->setVar('rule1', '1');
					},
					function (&$router, &$uri)
					{
						$uri->setVar('rule2', '1');
					},
					function (&$router, &$uri)
					{
						$uri->setVar('rule3', '1');
					}
				),
				array(JRouter::PROCESS_AFTER, JRouter::PROCESS_DURING, JRouter::PROCESS_BEFORE),
				false,
				'?result=1&test=true',
				array('result' => '1', 'test' => 'true', 'rule3' => '1', 'rule2' => '1', 'rule1' => '1'),
				array()
			),
			// Several rules are applied to one URL - ordered
			'rules-overwrite-data' => array(
				array(
					function (&$router, &$uri)
					{
						$uri->setVar('rule2', '2');
					},
					function (&$router, &$uri)
					{
						$uri->setVar('rule2', '1');
					}
				),
				array(JRouter::PROCESS_AFTER, JRouter::PROCESS_DURING),
				false,
				'?result=1&test=true',
				array('result' => '1', 'test' => 'true', 'rule2' => '2'),
				array()
			),
		);
	}

	/**
	 * Tests the parse() method
	 *
	 * @dataProvider casesParse
	 * @return  void
	 * @testdox JRouter::parse() parses a JUri object into an array of parameters
	 * @since   4.0
	 */
	public function testParse($rules, $stages, $setVars, $url, $expected, $expectedVars)
	{
		foreach ($rules as $i => $rule)
		{
			$this->object->attachParseRule($rule, $stages[$i]);
		}

		$uri = new JUri($url);
		$result = $this->object->parse($uri, $setVars);

		$this->assertEquals($expected, $result);
		$this->assertEquals($expectedVars, $this->object->getVars());
	}

	/**
	 * @testdox      build() gives the same result as the JUri constructor
	 * @since        4.0
	 */
	public function testBuild()
	{
		$uri    = new JUri('index.php?var1=value1');
		$object = new JRouter;
		$result = $this->object->build('index.php?var1=value1');
		$this->assertEquals($uri, $result);
	}




	/**
	 * @see     https://github.com/joomla-projects/joomla-pythagoras/issues/3
	 * @since   3.4
	 */
	public function testMultipleVariablesCanBeAddedAtOnceAndOptionallyReplaceExistingVariables()
	{
		$this->assertEquals(array(), $this->object->getVars());

		$this->object->setVars(array('var1' => 'value1'));
		$this->assertEquals(array('var1' => 'value1'), $this->object->getVars());

		$this->object->setVars(array('var2' => 'value2'));
		$this->assertEquals(array('var1' => 'value1', 'var2' => 'value2'), $this->object->getVars());

		$this->object->setVars(array('var3' => 'value3'), false);
		$this->assertEquals(array('var3' => 'value3'), $this->object->getVars());

		$this->object->setVars(array(), false);
		$this->assertEquals(array(), $this->object->getVars());
	}

	/**
	 * Cases for testSetVar
	 *
	 * @since   3.1
	 */
	public function casesVariables()
	{
		$cases = array(
			array(array(), 'var', 'value', true, 'value'),
			array(array(), 'var', 'value', false, null),
			array(array('var' => 'value1'), 'var', 'value2', true, 'value2'),
			array(array('var' => 'value1'), 'var', 'value2', false, 'value2'),
		);

		return $cases;
	}

	/**
	 * @param   array   $preset   An associative array with variables
	 * @param   string  $var      The name of the variable
	 * @param   mixed   $value    The value of the variable
	 * @param   boolean $create   If True, the variable will be created if it doesn't exist yet
	 * @param   string  $expected Expected return value
	 *
	 * @dataProvider  casesVariables
	 * @since         3.1
	 */
	public function testSingleVariablesCanBeAddedAndOptionallyReplaceExistingVariables($preset, $var, $value, $create, $expected)
	{
		$this->object->setVars($preset, false);

		$this->object->setVar($var, $value, $create);
		$this->assertEquals($expected, $this->object->getVar($var));
	}

	/**
	 * @since   3.4
	 * @testdox Router throws an InvalidArgumentException when attaching a build rule to an undefined stage
	 * @expectedException InvalidArgumentException
	 */
	public function testRouterThrowsInvalidArgumentExceptionWhenAttachingBuildRuleToUndefinedStage()
	{
		$callback = function (JRouter $router, JUri $uri) { };
		$this->object->attachBuildRule($callback, 'undefined');
	}

	/**
	 * @since   3.4
	 * @testdox Router throws an InvalidArgumentException when attaching a parse rule to an undefined stage
	 * @expectedException InvalidArgumentException
	 */
	public function testRouterThrowsInvalidArgumentExceptionWhenAttachingParseRuleToUndefinedStage()
	{
		$callback = function (JRouter $router, JUri $uri) { };
		$this->object->attachParseRule($callback, 'undefined');
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
		return array(
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => 'index.php?var1=value1&var2=value2'
			),
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						$uri->setPath('value1');
					}
				),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => 'value1?var1=value1&var2=value2'
			),
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						$uri->setPath('value1');
					},
					function (JRouter $router, JUri $uri)
					{
						$uri->delVar('var1');
					},
				),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => 'value1?var2=value2'
			),
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						$uri->setPath('value1/value2');
					},
					function (JRouter $router, JUri $uri)
					{
						$uri->delVar('var1');
					},
					function (JRouter $router, JUri $uri)
					{
						$uri->delVar('var2');
					},
				),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => 'value1/value2'
			),
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						$uri->setVar('stage', 'during');
					}
				),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => 'index.php?var1=value1&var2=value2&stage=during'
			),
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						$uri->setVar('stage', 'before');
					}
				),
				'stage'    => JRouter::PROCESS_BEFORE,
				'expected' => 'index.php?var1=value1&var2=value2&stage=before'
			),
			array(
				'url'      => 'index.php?var1=value1&var2=value2',
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						$uri->setVar('stage', 'after');
					}
				),
				'stage'    => JRouter::PROCESS_AFTER,
				'expected' => 'index.php?var1=value1&var2=value2&stage=after'
			),
		);
	}

	/**
	 * @param   string $url       The URL
	 * @param   array  $functions Callback to execute
	 * @param   string $stage     Stage to process
	 * @param   string $expected  Expected return value
	 *
	 * @dataProvider casesProcessBuildRules
	 *
	 * @testdox      Processing the build rules gives the right URIs
	 * @since        3.4
	 */
	public function testProcessingTheBuildRulesGivesTheRightUris($url, $functions, $stage, $expected)
	{
		foreach ($functions as $function)
		{
			$this->object->attachBuildRule($function, $stage);
		}

		$this->assertEquals($expected, (string) $this->object->build($url));
	}

	/**
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesProcessBuildRulesOrder()
	{
		return array(
			array(
				'url'      => 'index.php',
				'expected' => 'index.php?var1=after&var3=before&var4=during&var5=after'
			),
			array(
				'url'      => 'index.php?var1=value1',
				'expected' => 'index.php?var1=after&var3=before&var4=during&var5=after'
			),
			array(
				'url'      => 'index.php?var2=value2',
				'expected' => 'index.php?var2=value2&var1=after&var3=before&var4=during&var5=after'
			),
			array(
				'url'      => 'index.php?var3=value3',
				'expected' => 'index.php?var3=before&var1=after&var4=during&var5=after'
			),
			array(
				'url'      => 'index.php?var4=value4',
				'expected' => 'index.php?var4=during&var1=after&var3=before&var5=after'
			),
			array(
				'url'      => 'index.php?var5=value5',
				'expected' => 'index.php?var5=after&var1=after&var3=before&var4=during'
			),
		);
	}

	/**
	 * @param   string $url      The URL
	 * @param   string $expected Expected return value
	 *
	 * @dataProvider casesProcessBuildRulesOrder
	 *
	 * @since        4.0
	 */
	public function testStagesAreProcessedInCorrectOrder($url, $expected)
	{
		$this->object->attachBuildRule(
			function (JRouter $router, JUri $uri)
			{
				$uri->setVar('var1', 'before');
				$uri->setVar('var3', 'before');
				$uri->setVar('var4', 'before');
			},
			JRouter::PROCESS_BEFORE
		);
		$this->object->attachBuildRule(
			function (JRouter $router, JUri $uri)
			{
				$uri->setVar('var1', 'during');
				$uri->setVar('var4', 'during');
				$uri->setVar('var5', 'during');
			},
			JRouter::PROCESS_DURING
		);
		$this->object->attachBuildRule(
			function (JRouter $router, JUri $uri)
			{
				$uri->setVar('var1', 'after');
				$uri->setVar('var5', 'after');
			},
			JRouter::PROCESS_AFTER
		);

		$this->assertEquals($expected, (string) $this->object->build($url));
	}

	/**
	 * Cases for testCreateURI
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesCreateUri()
	{
		return array(
			array(
				'url'      => '',
				'preset'   => array(),
				'expected' => ''
			),
			array(
				'url'      => 'index.php',
				'preset'   => array(),
				'expected' => 'index.php'
			),
			array(
				'url'      => array('var1' => 'value1', 'var2' => 'value2'),
				'preset'   => array(),
				'expected' => 'index.php?var1=value1&var2=value2'
			),
			array(
				'url'      => array('var1' => 'value1', 'var2' => 'value2'),
				'preset'   => array('var3' => 'value3'),
				'expected' => 'index.php?var3=value3&var1=value1&var2=value2'
			),
			array(
				'url'      => array('var1' => 'value1', 'var2' => 'value2'),
				'preset'   => array('var2' => 'value3'),
				'expected' => 'index.php?var2=value2&var1=value1'
			),
			array(
				'url'      => '&var1=value1&var2=value2',
				'preset'   => array(),
				'expected' => 'index.php?var1=value1&var2=value2'
			),
			array(
				'url'      => '&var1=value1&var2=value2',
				'preset'   => array('var3' => 'value3'),
				'expected' => 'index.php?var3=value3&var1=value1&var2=value2'
			),
			array(
				'url'      => '&var1=value1&var2=value2',
				'preset'   => array('var2' => 'value3'),
				'expected' => 'index.php?var2=value2&var1=value1'
			),
			array(
				'url'      => '&var1=value1&var2=',
				'preset'   => array(),
				'expected' => 'index.php?var1=value1'
			),
			array(
				'url'      => '&amp;var1=value1&amp;var2=value2',
				'preset'   => array(),
				'expected' => 'index.php?var1=value1&var2=value2'
			),
		);
	}

	/**
	 * Tests createUri() method
	 *
	 * @param   array  $url      valid inputs to the createUri() method
	 * @param   array  $preset   global Vars that should be merged into the URL
	 * @param   string $expected expected URI string
	 *
	 * @dataProvider casesCreateUri
	 * @testdox      createUri() generates URI combining URL and preset variables
	 * @since        3.4
	 */
	public function testCreateUriGeneratesUriFromUrlAndPreset($url, $preset, $expected)
	{
		$this->object->setVars($preset, false);

		$createUriMethod = new ReflectionMethod('JRouter', 'createUri');
		$createUriMethod->setAccessible(true);
		$this->assertEquals($expected, (string) ($createUriMethod->invoke($this->object, $url)));
	}

	/**
	 * Tests the detachRule() method
	 *
	 * @return  void
	 *
	 * @since   4.0
	 * @covers  JRouter::detachRule
	 */
	public function testDetachRule()
	{
		$rule = function () {};
		$this->object->attachParseRule($rule);
		$rules = $this->object->getRules();
		$this->assertEquals(array($rule), $rules['parse']);
		$this->assertTrue($this->object->detachRule('parse', $rule));
		$rules = $this->object->getRules();
		$this->assertEquals(array(), $rules['parse']);
		$this->assertFalse($this->object->detachRule('parse', $rule));
	}

	/**
	 * @since 4.0
	 * @expectedException InvalidArgumentException
	 */
	public function testDetachRuleWrongType()
	{
		$rule = function () {};
		$this->object->detachRule('parsewrong', $rule);
	}

	/**
	 * @since 4.0
	 * @expectedException InvalidArgumentException
	 */
	public function testDetachRuleWrongStage()
	{
		$rule = function () {};
		$this->object->detachRule('parse', $rule, 'wrong');
	}

	/**
	 * @since 4.0
	 * @expectedException InvalidArgumentException
	 */
	public function testProcessBuildRules()
	{
		$uri = new JUri();
		$method = new ReflectionMethod('JRouter', 'processBuildRules');
		$method->setAccessible(true);
		$method->invokeArgs($this->object, array(&$uri, 'after'));
	}

	/**
	 * @since 4.0
	 * @expectedException InvalidArgumentException
	 */
	public function testProcessParseRules()
	{
		$uri = new JUri();
		$processParseRulesMethod = new ReflectionMethod('JRouter', 'processParseRules');
		$processParseRulesMethod->setAccessible(true);
		$processParseRulesMethod->invokeArgs($this->object, array(&$uri, 'afterwrong'));
	}
}
