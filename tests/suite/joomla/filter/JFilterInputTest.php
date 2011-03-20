<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/filter/filterinput.php';

/**
 * JFilterInputTest
 *
 * @package Joomla.UnitTest
 * @subpackage Filter
 */
class JFilterInputTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Produces the array of test cases common to all test runs.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function casesGeneric()
	{
		$input = '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`'.
			'abcdefghijklmnopqrstuvwxyz{|}~â‚¬â€šÆ’â€žâ€¦â€ â€¡Ë†â€°Å â€¹Å’Å½â€˜â€™â€œâ€�â€¢â€“â€”Ëœâ„¢Å¡â€ºÅ“Å¾Å¸Â¡Â¢Â£Â¤Â¥Â¦Â§Â¨Â©ÂªÂ«Â¬Â­Â®Â¯Â°Â±Â²Â³Â´ÂµÂ¶Â·'.
			'Â¸Â¹ÂºÂ»Â¼Â½Â¾Â¿Ã€Ã�Ã‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹ÃŒÃ�ÃŽÃ�Ã�Ã‘Ã’Ã“Ã”Ã•Ã–Ã—Ã˜Ã™ÃšÃ›ÃœÃ�ÃžÃŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã°Ã±Ã²Ã³Ã´ÃµÃ¶Ã·Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¾Ã¿';

		return array(
			'int_01' => array(
				'int',
				$input,
				123456789,
				'From generic cases'
			),
			'integer' => array(
				'int',
				$input,
				123456789,
				'From generic cases'
			),
			'int_02' => array(
				'int',
				'abc123456789abc123456789',
				123456789,
				'From generic cases'
			),
			'int_03' => array(
				'int',
				'123456789abc123456789abc',
				123456789,
				'From generic cases'
			),
			'int_04' => array(
				'int',
				'empty',
				0,
				'From generic cases'
			),
			'int_05' => array(
				'int',
				'ab-123ab',
				-123,
				'From generic cases'
			),
			'int_06' => array(
				'int',
				'-ab123ab',
				123,
				'From generic cases'
			),
			'int_07' => array(
				'int',
				'-ab123.456ab',
				123,
				'From generic cases'
			),
			'int_08' => array(
				'int',
				'456',
				456,
				'From generic cases'
			),
			'int_09' => array(
				'int',
				'-789',
				-789,
				'From generic cases'
			),
			'int_10' => array(
				'int',
				-789,
				-789,
				'From generic cases'
			),
			'float_01' => array(
				'float',
				$input,
				123456789,
				'From generic cases'
			),
			'double' => array(
				'double',
				$input,
				123456789,
				'From generic cases'
			),
			'float_02' => array(
				'float',
				20.20,
				20.2,
				'From generic cases'
			),
			'float_03' => array(
				'float',
				'-38.123',
				-38.123,
				'From generic cases'
			),
			'float_04' => array(
				'float',
				'abc-12.456',
				-12.456,
				'From generic cases'
			),
			'float_05' => array(
				'float',
				'-abc12.456',
				12.456,
				'From generic cases'
			),
			'float_06' => array(
				'float',
				'abc-12.456abc',
				-12.456,
				'From generic cases'
			),
			'float_07' => array(
				'float',
				'abc-12 . 456',
				-12,
				'From generic cases'
			),
			'float_08' => array(
				'float',
				'abc-12. 456',
				-12,
				'From generic cases'
			),
			'bool_0' => array(
				'bool',
				$input,
				true,
				'From generic cases'
			),
			'boolean' => array(
				'boolean',
				$input,
				true,
				'From generic cases'
			),
			'bool_1' => array(
				'bool',
				true,
				true,
				'From generic cases'
			),
			'bool_2' => array(
				'bool',
				false,
				false,
				'From generic cases'
			),
			'bool_3' => array(
				'bool',
				'',
				false,
				'From generic cases'
			),
			'bool_4' => array(
				'bool',
				0,
				false,
				'From generic cases'
			),
			'bool_5' => array(
				'bool',
				1,
				true,
				'From generic cases'
			),
			'bool_6' => array(
				'bool',
				null,
				false,
				'From generic cases'
			),
			'bool_7' => array(
				'bool',
				'false',
				true,
				'From generic cases'
			),
			'word_01' => array(
				'word',
				$input,
				'ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'word_02' => array(
				'word',
				null,
				'',
				'From generic cases'
			),
			'word_03' => array(
				'word',
				123456789,
				'',
				'From generic cases'
			),
			'word_04' => array(
				'word',
				'word123456789',
				'word',
				'From generic cases'
			),
			'word_05' => array(
				'word',
				'123456789word',
				'word',
				'From generic cases'
			),
			'word_06' => array(
				'word',
				'w123o4567r89d',
				'word',
				'From generic cases'
			),
			'alnum_01' => array(
				'alnum',
				$input,
				'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'alnum_02' => array(
				'alnum',
				null,
				'',
				'From generic cases'
			),
			'alnum_03' => array(
				'alnum',
				'~!@#$%^&*()_+abc',
				'abc',
				'From generic cases'
			),
			'cmd' => array(
				'cmd',
				$input,
				'-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'base64' => array(
				'base64',
				$input,
				'+/0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'array' => array(
				'array',
				array( 1, 3, 6 ),
				array( 1, 3, 6 ),
				'From generic cases'
			),
			'path_01' => array(
				'path',
				'images/system',
				'images/system',
				'From generic cases'
			),
			'path_02' => array(
				'path',
				'http://www.fred.com/josephus',
				'',
				'From generic cases'
			),
			'user_01' => array(
				'username',
				'&<f>r%e\'d',
				'fred',
				'From generic cases'
			),
			'user_02' => array(
				'username',
				'fred',
				'fred',
				'From generic cases'
			),
			'string_01' => array(
				'string',
				'123.567',
				'123.567',
				'From generic cases'
			),
			'unknown_01' => array(
				'',
				'123.567',
				'123.567',
				'From generic cases'
			),
			'unknown_02' => array(
				'',
				array( 1, 3, 9 ),
				array( 1, 3, 9 ),
				'From generic cases'
			),
			'unknown_03' => array(
				'',
				array( "key" => "Value", "key2" => "This&That", "key2" => "This&amp;That" ),
				array( "key" => "Value", "key2" => "This&That", "key2" => "This&That" ),
				'From generic cases'
			),
			'unknown_04' => array(
				'',
				12.6,
				12.6,
				'From generic cases'
			),
			'tag_01' => array(
				'',
				'<em',
				'em',
				'From generic cases'
			),
			'Kill script' => array(
				'',
				'<img src="javascript:alert();" />',
				'<img />',
				'From generic cases'
			),
			'Nested tags' => array(
				'',
				'<em><strong>Fred</strong></em>',
				'<em><strong>Fred</strong></em>',
				'From generic cases'
			),
			'Malformed Nested tags' => array(
				'',
				'<em><strongFred</strong></em>',
				'<em><strongFred</strong></em>',
				'From generic cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'<img height="300" />',
				'From generic cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img height=300 />',
				'<img height="300" />',
				'From generic cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img height=\'300\' />',
				'<img height="300" />',
				'From generic cases'
			),
			'Attribute is zero' => array(
				'',
				'<img height=0 />',
				'<img height="0" />',
				'From generic cases'
			),
			'Attribute value missing' => array(
				'',
				'<img height= />',
				'<img height="height" />',
				'From generic cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'<img height="300" ismap="ismap" />',
				'From generic cases'
			),
			'Bad Attribute Name' => array(
				'',
				'<br 3bb />',
				'<br />',
				'From generic cases'
			),
			'Bad Tag Name' => array(
				'',
				'<300 />',
				'',
				'From generic cases'
			),
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img class="one two" />',
				'From generic cases'
			),
		);
	}

	/**
	 * Sets up for the test run.
	 *
	 * @return void
	 *
	 */
	function setUp()
	{
	}

	/**
	 * Produces the array of test cases for the Clean Text test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of two values
	 *				The first is the input data for the test run,
	 *				and the second is the expected result of filtering.
	 */
	function casesCleanText()
	{
		$cases = array(
			'case_1' => array(
				'',
				''
			),
			'script_0' => array(
				'<script>alert(\'hi!\');</script>',
				''
			)
		);
		$tests = $cases;

		return $tests;
	}

	/**
	 * Execute a cleanText test case.
	 *
	 * @param string $data   The original output
	 * @param string $expect The expected result for this test.
	 *
	 * @return void
	 *
	 * @dataProvider casesCleanText
	 */
	function testCleanText($data, $expect)
	{
		$this->markTestSkipped('Why are we calling JFilterOutput in JFilterInputTest?');
		$this->assertThat(
			$expect,
			$this->equalTo(JFilterOutput::cleanText($data))
		);
	}

	/**
	 * Produces the array of test cases for plain Whitelist test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function whitelist()
	{
		$casesSpecific = array(
			'Kill script' => array(
				'',
				'<img src="javascript:alert();" />',
				'',
				'From specific cases'
			),
			'Nested tags' => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Malformed Nested tags' => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'',
				'From specific cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img height=300 />',
				'',
				'From specific cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img height=\'300\' />',
				'',
				'From specific cases'
			),
			'Attribute is zero' => array(
				'',
				'<img height=0 />',
				'',
				'From specific cases'
			),
			'Attribute value missing' => array(
				'',
				'<img height= />',
				'',
				'From specific cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'',
				'From specific cases'
			),
			'Bad Attribute Name' => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'',
				'From specific cases'
			),
			'tracker24258' => array(
				// Test for recursion on attributes
				'string',
				'<scrip &nbsp; t>alert(\'test\');</scrip t>',
				'alert(\'test\');',
				'From generic cases'
			),
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with default filter settings (whitelist - no html).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying source of test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelist
	 */
	function testCleanByCallingMember( $type, $data, $expect, $message )
	{
		$filter = new JFilterInput;
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Whitelist img tag test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function whitelistImg()
	{
		$casesSpecific = array(
			'Kill script' => array(
				'',
				'<img src="javascript:alert();" />',
				'<img />',
				'From specific cases'
			),
			'Nested tags' => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Malformed Nested tags' => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'<img />',
				'From specific cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img height=300 />',
				'<img />',
				'From specific cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img height=\'300\' />',
				'<img />',
				'From specific cases'
			),
			'Attribute is zero' => array(
				'',
				'<img height=0 />',
				'<img />',
				'From specific cases'
			),
			'Attribute value missing' => array(
				'',
				'<img height= />',
				'<img />',
				'From specific cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'<img />',
				'From specific cases'
			),
			'Bad Attribute Name' => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img />',
				'From specific cases'
			)
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelistImg
	 */
	function testCleanWithImgWhitelisted( $type, $data, $expect, $message )
	{
		$filter = JFilterInput::getInstance(Array( 'img' ), null, 0, 0);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Whitelist class attribute test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function whitelistClass()
	{
		$casesSpecific = array(
			'Kill script' => array(
				'',
				'<img src="javascript:alert();" />',
				'',
				'From specific cases'
			),
			'Nested tags' => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Malformed Nested tags' => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'',
				'From specific cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img height=300 />',
				'',
				'From specific cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img height=\'300\' />',
				'',
				'From specific cases'
			),
			'Attribute is zero' => array(
				'',
				'<img height=0 />',
				'',
				'From specific cases'
			),
			'Attribute value missing' => array(
				'',
				'<img height= />',
				'',
				'From specific cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'',
				'From specific cases'
			),
			'Bad Attribute Name' => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'',
				'From specific cases'
			)
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelistClass
	 */
	function testCleanWithClassWhitelisted( $type, $data, $expect, $message )
	{
		$filter = JFilterInput::getInstance(null, array( 'class' ), 0, 0);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Whitelist class attribute img tag test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function whitelistClassImg()
	{
		$casesSpecific = array(
			'Kill script' => array(
				'',
				'<img src="javascript:alert();" />',
				'<img />',
				'From specific cases'
			),
			'Nested tags' => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Malformed Nested tags' => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'<img />',
				'From specific cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img height=300 />',
				'<img />',
				'From specific cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img height=\'300\' />',
				'<img />',
				'From specific cases'
			),
			'Attribute is zero' => array(
				'',
				'<img height=0 />',
				'<img />',
				'From specific cases'
			),
			'Attribute value missing' => array(
				'',
				'<img height= />',
				'<img />',
				'From specific cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'<img />',
				'From specific cases'
			),
			'Bad Attribute Name' => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img class="one two" />',
				'From specific cases'
			)
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelistClassImg
	 */
	function testCleanWithImgAndClassWhitelisted( $type, $data, $expect, $message )
	{
		$filter = JFilterInput::getInstance(array( 'img' ), array( 'class' ), 0, 0);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the plain Blacklist test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function blacklist()
	{
		$casesSpecific = array(
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() default blacklist filter settings (strips bad tags).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider blacklist
	 */
	function testCleanWithDefaultBlackList($type, $data, $expect, $message )
	{
		$filter = JFilterInput::getInstance(null, null, 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Blacklist img tag test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function blacklistImg()
	{
		$casesSpecific = array(
			'Kill script' => array(
				'',
				'<img src="javascript:alert();" />',
				'',
				'From specific cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'',
				'From specific cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img height=300 />',
				'',
				'From specific cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img height=\'300\' />',
				'',
				'From specific cases'
			),
			'Attribute is zero' => array(
				'',
				'<img height=0 />',
				'',
				'From specific cases'
			),
			'Attribute value missing' => array(
				'',
				'<img height= />',
				'',
				'From specific cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'',
				'From specific cases'
			),
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'',
				'From specific cases'
			)
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() using custom img blacklist filter settings (strips bad tags).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider blacklistImg
	 */
	function testCleanWithImgBlackList($type, $data, $expect, $message )
	{
		$filter = JFilterInput::getInstance(array( 'img' ), null, 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Blacklist class attribute test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *				The first is the type of input data, the second is the actual input data,
	 *				the third is the expected result of filtering, and the fourth is
	 *				the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	function blacklistClass()
	{
		$casesSpecific = array(
			'tracker9725' => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img />',
				'From specific cases'
			)
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() using custom class blacklist filter settings (strips bad tags).
	 *
	 * @param string $type	The type of input
	 * @param string $data	The input
	 * @param string $expect  The expected result for this test.
	 * @param string $message The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider blacklistClass
	 */
	function testCleanWithClassBlackList($type, $data, $expect, $message )
	{
		$filter = JFilterInput::getInstance(null, array( 'class' ), 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}
}
