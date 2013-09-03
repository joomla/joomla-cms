<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlSelect.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlSelectTest_DataSet
{
	static public $optionsTest = array(
		// @todo remove: array($expected, $arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
		array(
			"<option value=\"1\">&nbsp;Test</option>\n",
			array(
				array(
					'value' => '1',
					'text' => '&nbsp;Test',
				),
			),
		),
	);
}
