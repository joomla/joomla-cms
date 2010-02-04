<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Profile view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewProfile extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @access	public
	 * @param	string	$tpl	The template file to include
	 * @since	1.0
	 */
	function display($tpl = null)
	{
		// Get the view data.
		$form		= &$this->get('Form');
		$data		= &$this->get('Data');
		$profile	= &$this->get('Profile');
		$state		= $this->get('State');
		$params		= $state->get('params');

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Check if a member was found.
		if (!$data->id) {
			JError::raiseError(404, 'USERS_PROFILE_NOT_FOUND');
			return false;
		}

		// Bind the data to the form.
		if ($form) {
			$form->bind($data);
		}

		// Configure the pathway and page title.
		$app		= &JFactory::getApplication();
		$config		= &JFactory::getConfig();
		$user		= &JFactory::getUser();
		$pathway	= &$app->getPathway();
		$menus		= &$app->getMenu();
		$menu		= &$menus->getActive();

		// Append the current member to the breadcrumb if we came from a users view menu item.
		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'users')
		{
			// Add the member name to the pathway.
			$pathway->addItem($this->escape($data->name));
		}

		// Set the page title if it has not been set already.
		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'profile' && isset($menu->query['profile']) && $menu->query['profile'] == $data->id)
		{
			$mparams = new JParameter($menu->params);

			// If a page title has not been set, set one.
			if (!$mparams->get('page_title')) {
				$params->set('page_title', $data->name);
			}
		}
		else
		{
			$params->set('page_title', $data->name);
		}

		// Set the document title.
		$this->document->setTitle($params->get('page_title'));

		// Push the data into the view.
		$this->assignRef('form',	$form);
		$this->assignRef('data',	$data);
		$this->assignRef('profile',	$profile);
		$this->assignRef('params',	$params);

		parent::display($tpl);
	}
}