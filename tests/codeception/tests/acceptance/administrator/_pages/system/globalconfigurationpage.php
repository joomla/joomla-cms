<?php
/**
 * @package     mustached
 * @subpackage  Page Class
 * @copyright   Copyright (C) 2014 mustached.org All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class LoginManagerPage
 *
 * @since  1.4
 *
 * @link   http://codeception.com/docs/07-AdvancedUsage#PageObjects
 */
class GlobalconfigurationPage
{
	/**
	 * Array of Page elements indexed by descriptive name or label
	 *
	 * @var array
	 */
	public static $elements = array(
	'Error Reporting Dropdown'  => "//div[@id='jform_error_reporting_chzn']/a",
	'option: Development'       => "//div[@id='jform_error_reporting_chzn']/div/ul/li[contains(text(), 'Development')]",
	);
}
