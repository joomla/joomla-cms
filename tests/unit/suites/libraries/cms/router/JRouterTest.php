<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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

		parent::tearDown();
	}

	/**
	 * @return array
	 * @since 4.0
	 */
	public function casesClients()
	{
		return array(
			// 'site'  => array('site'),
			'admin' => array('administrator'),
		);
	}

	/**
	 * @dataProvider casesClients
	 * @testdox      JRouter::getInstance() returns a router of the required type with correct mode
	 * @since        3.4
	 *
	 * @param $client
	 */
	public function testProperTypeAndMode($client)
	{
		$cache = new ReflectionProperty('JRouter', 'instances');
		$cache->setAccessible(true);
		$cache->setValue(array());

		#$this->markTestSkipped('Untestable due to global instance cache not clearable.');

		$object = JRouter::getInstance($client, array('mode' => 'test'));

		$expected = 'JRouter' . ucfirst($client);

		$this->assertInstanceOf($expected, $object);
		$this->assertEquals('test', $object->getMode());
	}

	/**
	 * @dataProvider casesClients
	 * @testdox      Subsequent calls to getInstance() return the same instance
	 * @since        3.4
	 *
	 * @param $client
	 */
	public function testSubsequentCallsReturnTheSameInstance($client)
	{
		$object = JRouter::getInstance($client);

		$this->assertSame($object, JRouter::getInstance($client));
	}

	/**
	 * @since   3.4
	 */
	public function testLegacyApplicationRouterIsStillLoaded()
	{
		JApplicationHelper::addClientInfo(array(
			'id'   => 3,
			'name' => 'tester',
			'path' => __DIR__ . '/data'
		));

		$this->assertInstanceOf('JRouter', JRouter::getInstance('tester'));
	}

	/**
	 * @since   3.4
	 * @expectedException RuntimeException
	 * @testdox getInstance() throws a RuntimeException, if a router for an unknown client is requested
	 */
	public function testGetInstanceException()
	{
		JRouter::getInstance('unknown');
	}

	/**
	 * @since   3.4
	 */
	public function casesParse()
	{
		return array(
			'raw-no_url-no_var' => array('', JROUTER_MODE_RAW, array(), array()),
			'raw-url-no_var'    => array('index.php?var1=value1', JROUTER_MODE_RAW, array(), array()),
			'raw-no_url-var'    => array('', JROUTER_MODE_RAW, array('var2' => 'value2'), array('var2' => 'value2')),
			'raw-url-var'       => array(
				'index.php?var1=value1',
				JROUTER_MODE_RAW,
				array('var2' => 'value2'),
				array('var2' => 'value2')
			),
			'sef-no_url-no_var' => array('', JROUTER_MODE_SEF, array(), array()),
			'sef-url-no_var'    => array('index.php?var1=value1', JROUTER_MODE_SEF, array(), array()),
			'sef-no_url-var'    => array('', JROUTER_MODE_SEF, array('var2' => 'value2'), array('var2' => 'value2')),
			'sef-url-var'       => array(
				'index.php?var1=value1',
				JROUTER_MODE_RAW,
				array('var2' => 'value2'),
				array('var2' => 'value2')
			),
		);
	}

	/**
	 * @param   string  $url      A URL
	 * @param   integer $mode     JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array   $vars     An associative array with global variables
	 * @param   array   $expected Expected value
	 *
	 * @dataProvider  casesParse
	 * @testdox       parse() does not evaluate URL parameters
	 * @since         3.4
	 */
	public function testRouterDoesNotEvaluateUrlParameters($url, $mode, $vars, $expected)
	{
		$this->object->setMode($mode);
		$this->object->setVars($vars);
		$uri = new JUri($url);

		$this->assertEquals($this->object->parse($uri), $expected);
	}

	/**
	 * @return array
	 */
	public function casesModes()
	{
		return array(
			'default' => array(null),
			'raw'     => array(JROUTER_MODE_RAW),
			'sef'     => array(JROUTER_MODE_SEF)
		);
	}

	/**
	 * @dataProvider casesModes
	 * @testdox      build() gives the same result as the JUri constructor
	 * @since        3.4
	 *
	 * @param $mode
	 */
	public function testBuildGivesTheSameResultAsTheJuriConstructor($mode)
	{
		$uri    = new JUri('index.php?var1=value1');
		$object = new JRouter;
		if (!empty($mode))
		{
			$object->setMode($mode);
		}
		$result = $this->object->build('index.php?var1=value1');
		$this->assertEquals($uri, $result);
	}

	/**
	 * @testdox Default mode is handling raw URLs
	 * @since   3.4
	 */
	public function testDefaultModeIsHandlingRawUrls()
	{
		$this->assertEquals(JROUTER_MODE_RAW, $this->object->getMode());
	}

	/**
	 * @since   3.4
	 */
	public function testModeCanBeChangedAfterInstantiation()
	{
		$this->object->setMode(JROUTER_MODE_SEF);
		$this->assertEquals(JROUTER_MODE_SEF, $this->object->getMode());
	}

	/**
	 * @since   3.4
	 */
	public function testModeCanBeSetToAnyArbitraryValue()
	{
		$this->object->setMode(42);
		$this->assertEquals(42, $this->object->getMode());
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
		$callback = array(
			function (JRouter $router, JUri $uri)
			{
			}
		);
		$this->object->attachBuildRule($callback, 'undefined');
	}

	/**
	 * @since   3.4
	 * @testdox Router throws an InvalidArgumentException when attaching a parse rule to an undefined stage
	 * @expectedException InvalidArgumentException
	 */
	public function testRouterThrowsInvalidArgumentExceptionWhenAttachingParseRuleToUndefinedStage()
	{
		$callback = array(
			function (JRouter $router, JUri $uri)
			{
			}
		);
		$this->object->attachParseRule($callback, 'undefined');
	}

	/**
	 * @return array
	 */
	public function casesParseRulesForReplace()
	{
		return array(
			'before' => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'before');
					}
				),
				'stage'    => JRouter::PROCESS_BEFORE,
				'expected' => array('var1' => 'before', 'var2' => 'value2')
			),
			'during' => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'during');
					}
				),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => array('var1' => 'during', 'var2' => 'value2')
			),
			'after'  => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'after');
					}
				),
				'stage'    => JRouter::PROCESS_AFTER,
				'expected' => array('var1' => 'after', 'var2' => 'value2')
			),
		);
	}

	/**
	 * @param   array  $preset   Initial router variables
	 * @param   array  $rules    Callback to execute
	 * @param   string $stage    Stage to process
	 * @param   string $expected Expected return value
	 *
	 * @dataProvider  casesParseRulesForReplace
	 * @since         3.4
	 */
	public function testParseRulesCanReplacePresetVariables($preset, $rules, $stage, $expected)
	{
		$this->object->setVars($preset, false);
		foreach ($rules as $rule)
		{
			$this->object->attachParseRule($rule, $stage);
		}

		$uri = $this->getMock('JUri');
		$this->assertEquals($expected, $this->object->parse($uri));
	}

	/**
	 * @return array
	 */
	public function casesParseRulesForAdd()
	{
		return array(
			'before' => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var3' => 'value3');
					},
				),
				'stage'    => JRouter::PROCESS_BEFORE,
				'expected' => array('var1' => 'value1', 'var2' => 'value2', 'var3' => 'value3')
			),
			'during' => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var3' => 'value3');
					},
				),
				'stage'    => JRouter::PROCESS_DURING,
				'expected' => array('var1' => 'value1', 'var2' => 'value2', 'var3' => 'value3')
			),
			'after'  => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var3' => 'value3');
					},
				),
				'stage'    => JRouter::PROCESS_AFTER,
				'expected' => array('var1' => 'value1', 'var2' => 'value2', 'var3' => 'value3')
			),
		);
	}

	/**
	 * @param   array  $preset   Initial router variables
	 * @param   array  $rules    Callback to execute
	 * @param   string $stage    Stage to process
	 * @param   string $expected Expected return value
	 *
	 * @dataProvider  casesParseRulesForAdd
	 * @since         3.4
	 */
	public function testParseRulesCanAddVariables($preset, $rules, $stage, $expected)
	{
		$this->object->setVars($preset, false);
		foreach ($rules as $rule)
		{
			$this->object->attachParseRule($rule, $stage);
		}

		$uri = $this->getMock('JUri');
		$this->assertEquals($expected, $this->object->parse($uri));
	}

	/**
	 * @return array
	 */
	public function casesParseRulesForPrecedence()
	{
		return array(
			'before-same_var' => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'before1');
					},
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'before2');
					},
				),
				'stage'    => JRouter::PROCESS_BEFORE,
				'expected' => array('var1' => 'before1', 'var2' => 'value2')
			),
			'during-same_var' => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'during1');
					},
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'during2');
					},
				),
				'stage'    => JRouter::PROCESS_BEFORE,
				'expected' => array('var1' => 'during1', 'var2' => 'value2')
			),
			'after-same_var'  => array(
				'preset'   => array('var1' => 'value1', 'var2' => 'value2'),
				'rules'    => array(
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'after1');
					},
					function (JRouter $router, JUri $uri)
					{
						return array('var1' => 'after2');
					},
				),
				'stage'    => JRouter::PROCESS_BEFORE,
				'expected' => array('var1' => 'after1', 'var2' => 'value2')
			),
		);
	}

	/**
	 * @param   array  $preset   Initial router variables
	 * @param   array  $rules    Callback to execute
	 * @param   string $stage    Stage to process
	 * @param   string $expected Expected return value
	 *
	 * @dataProvider  casesParseRulesForPrecedence
	 * @since         3.4
	 */
	public function testFirstParseRuleTakesPrecedence($preset, $rules, $stage, $expected)
	{
		$this->object->setVars($preset, false);
		foreach ($rules as $rule)
		{
			$this->object->attachParseRule($rule, $stage);
		}

		$uri = $this->getMock('JUri');
		$this->assertEquals($expected, $this->object->parse($uri));
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

		$this->assertEquals($expected, (string)$this->object->build($url));
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

		$this->assertEquals($expected, (string)$this->object->build($url));
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
		$this->assertEquals($expected, (string)($createUriMethod->invoke($this->object, $url)));
	}

	/**
	 * @since   3.4
	 */
	public function casesEncodeSegments()
	{
		return array(
			array(array('test'), array('test')),
			array(array('1:test'), array('1-test')),
			array(array('test', '1:test'), array('test', '1-test')),
			array(array('42:test', 'testing:this:method'), array('42-test', 'testing-this-method')),
		);
	}

	/**
	 * Tests encodeSegments() method
	 *
	 * @param   array  $segments Array of decoded segments of a URL
	 * @param   string $expected Array of encoded segments of a URL
	 *
	 * @dataProvider casesEncodeSegments
	 * @since        3.4
	 */
	public function testEncodeSegments($segments, $expected)
	{
		$encodeSegmentsMethod = new ReflectionMethod('JRouter', 'encodeSegments');
		$encodeSegmentsMethod->setAccessible(true);
		$this->assertEquals($expected, $encodeSegmentsMethod->invoke($this->object, $segments));
	}

	/**
	 * @since   3.4
	 */
	public function casesDecodeSegments()
	{
		return array(
			array(array('test'), array('test')),
			array(array('1-test'), array('1:test')),
			array(array('test', '1-test'), array('test', '1:test')),
			array(array('42-test', 'testing-this-method'), array('42:test', 'testing:this-method')),
		);
	}

	/**
	 * Tests decodeSegments() method
	 *
	 * @param   string $encoded  Array of encoded segments of a URL
	 * @param   array  $expected Array of decoded segments of a URL
	 *
	 * @dataProvider casesDecodeSegments
	 * @since        3.4
	 */
	public function testDecodeSegments($encoded, $expected)
	{
		$decodeSegmentsMethod = new ReflectionMethod('JRouter', 'decodeSegments');
		$decodeSegmentsMethod->setAccessible(true);
		$this->assertEquals($expected, $decodeSegmentsMethod->invoke($this->object, $encoded));
	}
}
