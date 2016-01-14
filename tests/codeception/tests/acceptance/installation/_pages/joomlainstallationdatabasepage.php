<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class JoomlaControlPanelPage
 *
 * @since  3.4.0
 *
 * @see     http://codeception.com/docs/07-AdvancedUsage#PageObjects
 */

class JoomlaInstallationDatabasePage
{
	/**
	 * Array of Page elements indexed by descriptive name or label
	 *
	 * @var array
	 */
	public static $elements = array(
		'Database Type'            	    => "#jform_db_type",
		'Remove Old Database button'	=> "//label[@for='jform_db_old1']"
	);
}

