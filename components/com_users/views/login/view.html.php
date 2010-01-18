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
 * Login view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewLogin extends JView
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
		$user		= &JFactory::getUser();
		$login		= $user->get('guest') ? true : false;
		$form		= &$this->get('LoginForm');
		$state		= $this->get('State');
		$params		= $state->get('params');

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Configure the pathway and page title.
		$app		= &JFactory::getApplication();
		$config		= &JFactory::getConfig();
		$pathway	= &$app->getPathway();
		$menus		= &$app->getMenu();
		$menu		= &$menus->getActive();

		// Set the page title if it has not been set already.
		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'login')
		{
			$mparams = new JParameter($menu->params);

			// If a page title has not been set, set one.
			if (!$mparams->get('page_title')) {
				$params->set('page_title', $login ? JText::_('Users_Login_Pathway_Login') : JText::_('Users_Login_Pathway_Logout'));
			}
		}
		else
		{
			$params->set('page_title', $login ? JText::_('Users_Login_Pathway_Login') : JText::_('Users_Login_Pathway_Logout'));
		}

		// Set the document title.
		$this->document->setTitle($params->get('page_title'));

		// Push the data into the view.
		$this->assignRef('user',	$user);
		$this->assignRef('form', 	$form);
		$this->assignRef('params',	$params);

		parent::display($tpl);
	}
}