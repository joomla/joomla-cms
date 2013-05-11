<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Tag Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsControllerTag extends JControllerForm
{
	/*
	 * @var  string Model name
	* @since  3.1
	*/
	protected $modelName = 'Tag';

	/**
	 * @var    string  The URL option for the component.
	 * @since  3.1
	 */
	protected $option = 'com_tags';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	*/
	protected $redirectUrl = 'index.php?option=com_tags&view=tags';

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();
		return ($user->authorise('core.create', 'com_tags'));
	}

	/**
	 * Method to check if you can edit a record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user = JFactory::getUser();
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;

		// Since there is no asset tracking and no categories, revert to the component permissions.
		return parent::allowEdit($data, $key);

	}
}
