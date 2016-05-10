<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class InstallJoomlaConfigurationPage
 *
 * @since  3.4.0
 *
 * @link   http://codeception.com/docs/07-AdvancedUsage#PageObjects
 */

class JoomlaInstallationConfigurationPage
{
	/**
	 * Array of Page elements indexed by descriptive name or label
	 *
	 * @var array
	 */
	public static $elements = array(
		'Language Selector'         => "//div[@id='jform_language_chzn']/a",
		'English (United Kingdom)'  => "//li[text()='English (United Kingdom)']",
		'No Site Offline'  => "//fieldset[@id='jform_site_offline']/label[2]"
	);
}
