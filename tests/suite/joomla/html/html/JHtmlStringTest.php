<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_PLATFORM . '/joomla/html/html/string.php';

/**
 * Tests for JDate class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       11.3
 */
class JHtmlStringTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test cases for truncate.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	function getTestAbridgeData()
	{
		return array(
			'No change case' => array(
				'Plain text',
				50,
				30,
				'Plain text',
			),
			'Normal case' => array(
				'Abridges text strings over the specified character limit. The behavior will insert an ellipsis into the text.',
				50,
				30,
				'Abridges text strings over the...is into the text.',
			),
		);
	}

	/**
	 * Test cases for truncate.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	function getTestTruncateData()
	{
		return array(
			'No change case' => array(
				'Plain text',
				0,
				'Plain text',
			),
			'Plain text under the limit' => array(
				'Plain text',
				100,
				'Plain text',
			),
			'Plain text at the limit' => array(
				'Plain text',
				10,
				'Plain text',
			),
			'Plain text over the limit by two words' => array(
				'Plain text test',
				12,
				'Plain...',
			),
			'Plain text over the limit by one word' => array(
				'Plain text test',
				13,
				'Plain text...',
			),
			'Plain text over the limit with short trailing words' => array(
				'Plain text a b c d',
				13,
				'Plain text...',
			),
			'Plain text over the limit splitting first word' => array(
				'Plain text',
				3,
				'Pla...',
			),
			'Plain html under the limit' => array(
				'<span>Plain text</span>',
				100,
				'<span>Plain text</span>',
			),
			'Plain html at the limit' => array(
				'<span>Plain text</span>',
				23,
				'<span>Plain text</span>',
			),
			'Plain html over the limit' => array(
				'<span>Plain text</span>',
				22,
				'<span>Plain text</span>...',
			),
			'Plain html over the limit by one word' => array(
				'<span>Plain text</span>',
				12,
				'<span>Plain</span>...',
			),
			'Plain html over the limit splitting first word' => array(
				'<span>Plain text</span>',
				10,
				'<span>Plai</span>...',
			),
			'Complex html over the limit' => array(
				'<div><span><i>Plain</i> <b>text</b> foo</span></div>',
				37,
				'<div><span><i>Plain</i></span></div>...',
			),
			'Complex html over the limit 2' => array(
				'<div><span><i>Plain</i> <b>text</b> foo</span></div>',
				38,
				'<div><span><i>Plain</i> <b>text</b></span></div>...',
			),
		);
	}

	/**
	 * Tests the JHtmlString::abridge method.
	 *
	 * @param   string   $text      The text to truncate.
	 * @param   integer  $length    The maximum length of the text.
	 * @param   integer  $intro     The maximum length of the intro text.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  getTestAbridgeData
	 * @since   11.3
	 */
	public function testAbridge($text, $length, $intro, $expected)
	{
		$this->assertThat(
			JHtmlString::abridge($text, $length, $intro),
			$this->equalTo($expected)
		);
	}

	/**
	 * Tests the JHtmlString::truncate method.
	 *
	 * @param   string   $text      The text to truncate.
	 * @param   integer  $length    The maximum length of the text.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  getTestTruncateData
	 * @since   11.3
	 */
	public function testTruncate($text, $length, $expected)
	{
		$this->assertThat(
			JHtmlString::truncate($text, $length),
			$this->equalTo($expected)
		);
	}
}
