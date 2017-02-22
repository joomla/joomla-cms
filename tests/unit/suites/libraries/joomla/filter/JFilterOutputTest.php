<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * FilterTestObject
 *
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 * @since       11.1
 */
class FilterTestObject
{
	public $string1;

	public $string2;

	public $string3;

	/**
	 * Sets up a dummy object for the output filter to be tested against
	 */
	public function __construct()
	{
		$this->string1 = "<script>alert();</script>";
		$this->string2 = "This is a test.";
		$this->string3 = "<script>alert(3);</script>";
		$this->array1 = array(1, 2, 3);
	}
}

/**
 * JFilterOutputTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 * @since       11.1
 */
class JFilterOutputTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var beforeObject
	 */
	protected $safeObject;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->safeObject = new FilterTestObject;
		$this->safeObjectArrayTest = new FilterTestObject;
	}


	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->safeObject);
		unset($this->safeObjectArrayTest);
		parent::tearDown();
	}

	/**
	 * Sends the FilterTestObject to the object filter.
	 *
	 * @return void
	 */
	public function testObjectHTMLSafe()
	{
		JFilterOutput::objectHTMLSafe($this->safeObject, null, 'string3');
		$this->assertEquals('&lt;script&gt;alert();&lt;/script&gt;', $this->safeObject->string1, "Script tag should be defused");
		$this->assertEquals('This is a test.', $this->safeObject->string2, "Plain text should pass");
		$this->assertEquals('<script>alert(3);</script>', $this->safeObject->string3, "This Script tag should be passed");
	}

	/**
	 * Sends the FilterTestObject to the object filter.
	 *
	 * @return void
	 */
	public function testObjectHTMLSafeWithArray()
	{
		JFilterOutput::objectHTMLSafe($this->safeObject, null, array('string1', 'string3'));
		$this->assertEquals('<script>alert();</script>', $this->safeObject->string1, "Script tag should pass array test");
		$this->assertEquals('This is a test.', $this->safeObject->string2, "Plain text should pass array test");
		$this->assertEquals('<script>alert(3);</script>', $this->safeObject->string3, "This Script tag should pass array test");
	}

	/**
	 * Tests enforcing XHTML links.
	 *
	 * @return void
	 */
	public function testLinkXHTMLSafe()
	{
		$this->assertEquals(
			'<a href="http://www.example.com/index.frd?one=1&amp;two=2&amp;three=3">This & That</a>',
			JFilterOutput::linkXHTMLSafe('<a href="http://www.example.com/index.frd?one=1&two=2&three=3">This & That</a>'),
			'Should clean ampersands only out of link, not out of link text'
		);
	}

	/**
	 * Tests filtering strings down to ASCII-7 lowercase URL text
	 *
	 * @return void
	 */
	public function testStringURLSafe()
	{
		$this->assertEquals(
			'1234567890-qwertyuiop-qwertyuiop-asdfghjkl-asdfghjkl-zxcvbnm-zxcvbnm',
			JFilterOutput::stringURLSafe('`1234567890-=~!@#$%^&*()_+	qwertyuiop[]\QWERTYUIOP{}|asdfghjkl;\'ASDFGHJKL:"zxcvbnm,./ZXCVBNM<>?'),
			'Should clean keyboard string down to ASCII-7'
		);
	}

	/**
	 * Tests converting strings to URL unicoded slugs.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testStringURLUnicodeSlug()
	{
		$this->assertEquals(
			'what-if-i-do-not-get_this-right',
			JFilterOutput::stringURLUnicodeSlug('What-if I do.not get_this right?'),
			'Should be URL unicoded'
		);
	}

	/**
	 * Tests replacing single ampersands with the entity, but leaving double ampersands
	 * and ampsersand-octothorpe combinations intact.
	 *
	 * @return void
	 */
	public function testAmpReplace()
	{
		$this->assertEquals(
			'&&george&amp;mary&#3son',
			JFilterOutput::ampReplace('&&george&mary&#3son'),
			'Should replace single ampersands with HTML entity'
		);
	}

	/**
	 * dataSet for Clean text
	 *
	 * @return array
	 */
	public static function dataSet()
	{
		$cases = array(
			'case_1' => array(
				'',
				''
			),
			'script_0' => array(
				'<script>alert(\'hi!\');</script>',
				''
			),

		);
		$tests = $cases;

		return $tests;
	}

	/**
	 * Execute a cleanText test case.
	 *
	 * The test framework calls this function once for each element in the array
	 * returned by the named data provider.
	 *
	 * @param   string  $data    The original output
	 * @param   string  $expect  The expected result for this test.
	 *
	 * @dataProvider dataSet
	 * @return void
	 */
	public function testCleanText($data, $expect)
	{
		$this->assertEquals($expect, JFilterOutput::cleanText($data));
	}

	/**
	 * Tests stripping images.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testStripImages()
	{
		$this->assertEquals(
			'Hello  I am waving at you.',
			JFilterOutput::stripImages('Hello <img src="wave.jpg"> I am waving at you.'),
			'Should remove img tags'
		);
	}

	/**
	 * Tests stripping iFrames.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testStripIframes()
	{
		$this->assertEquals(
			'Hello  I am waving at you.',
			JFilterOutput::stripIframes('Hello <iframe src="http://player.vimeo.com/video/37576499" width="500"' .
				' height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe> I am waving at you.'),
				'Should remove iFrame tags'
		);
	}
}
