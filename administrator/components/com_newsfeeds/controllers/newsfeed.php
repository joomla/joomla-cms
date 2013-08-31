<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeed controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.6
 */
class NewsfeedsControllerNewsfeed extends JControllerForm
{	/**
	 * @var    string  The URL option for the component.
	 * @since  3.1
	 */
	protected $option = 'com_newsfeeds';
	/*
	 * @var  string Model name
	 * @since  3.1
	 */
	protected $modelName = 'Newsfeed';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	*/
	protected $redirectUrl = 'index.php?option=com_newsfeeds&view=newsfeeds';

}
