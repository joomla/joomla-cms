<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User view levels list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       1.6
 */
class UsersControllerLevels extends JControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_USERS_LEVELS';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	* @since  3.1
	*/
	protected $redirectUrl = 'index.php?option=com_users&view=levels';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_users';

	/*
	 * @var  string  Model name
	* @since  3.1
	*/
	protected $name = 'Level';

	/*
	 * @var  string   Model prefix
	* @since  3.1
	*/
	protected $prefix = 'UsersModel';

	/**
	 * Proxy for getModel.
	 *
	 * @since   1.6
	 * @deprecated 3.5
	 */
	public function getModel($name = 'Level', $prefix = 'UsersModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
