<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Weblink controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.6
 */
class WeblinksControllerWeblink extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	An array of input data.
	 *
	 * @return 	boolean
	 */
	protected function _allowAdd($data = array())
	{
		// Initialize variables.
		$user		= JFactory::getUser();
		$categoryId	= JArrayHelper::getValue($data, 'catid', JRequest::getInt('filter_category_id'), 'int');
		$allow		= null;

		if ($categoryId)
		{
			// If the category has been passed in the URL check it.
			$allow	= $user->authorise('core.create', 'com_weblinks.category.'.$categoryId);
		}
		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::_allowAdd($data);
		}
		else {
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return 	boolean
	 */
	protected function _allowEdit($data = array(), $key = 'id')
	{
		// Initialize variables.
		$categoryId	= (int) isset($data['catid']) ? $data['catid'] : 0;
		$user		= JFactory::getUser();
		if ($categoryId)
		{
			// The category has been set. Check the category permissions.
			return $user->authorise('core.edit', 'com_weblinks.category.'.$categoryId);
		}
		else
		{
			// Since there is no asset tracking, revert to the component permissions.
			return parent::_allowEdit($data, $key);
		}
	}
}