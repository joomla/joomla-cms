<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class InstallJoomla3ManagerPage
 *
 * @since  3.4.0
 *
 * @link   http://codeception.com/docs/07-AdvancedUsage#PageObjects
 */

class JoomlaInstallationOverviewPage
{
	/**
	 * Array of Page elements indexed by descriptive name or label
	 *
	 * @var array
	 */
	public static $elements = array(
		'Sample Data'                       => '#jform_sample_file',
		'No sample Data'                    => '#jform_sample_file0',
		'Default English (GB) Sample Data'  => '#jform_sample_file3'
	);
}
