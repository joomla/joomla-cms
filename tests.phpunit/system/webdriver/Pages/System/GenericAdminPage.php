<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class for the back-end control panel screen.
 *
 */
class GenericAdminPage extends AdminPage
{
	protected $waitForXpath = "//button[contains(@onclick, 'option=com_help&keyref=Help')]";

}
