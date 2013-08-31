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
 * User notes controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       2.5
 */
class UsersControllerNotes extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $text_prefix = 'COM_USERS_NOTES';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	* @since  3.1
	*/
	protected $redirectUrl = 'index.php?option=com_users&view=notes';

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
	protected $name = 'Note';

	/*
	 * @var  string   Model prefix
	* @since  3.1
	*/
	protected $prefix = 'UsersModel';


	/**
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   2.5
	 * @deprecated 3.5
	 */
	public function getModel($name = 'Note', $prefix = 'UsersModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
