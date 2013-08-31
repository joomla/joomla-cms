<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Redirect link controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 * @since       1.6
 */
class RedirectControllerLink extends JControllerForm
{
	/*
	 * @var  string Model name
	* @since  3.1
	*/
	protected $modelName = 'Link';

	/**
	 * @var    string  The URL option for the component.
	 * @since  3.1
	 */
	protected $option = 'com_redirects';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	*/
	protected $redirectUrl = 'index.php?option=com_redirects&view=links';

	// Parent class access checks are sufficient for this controller.
}
