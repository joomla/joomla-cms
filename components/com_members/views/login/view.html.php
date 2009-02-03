<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

/**
 * Login view class for JXtended Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersViewLogin extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @param	string	$tpl	The template file to include
	 */
	public function display($tpl = null)
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
				$params->set('page_title', $login ? JText::_('MEMBERS LOGIN PATHWAY LOGIN') : JText::_('MEMBERS LOGIN PATHWAY LOGOUT'));
			}
		}
		else
		{
			$params->set('page_title', $login ? JText::_('MEMBERS LOGIN PATHWAY LOGIN') : JText::_('MEMBERS LOGIN PATHWAY LOGOUT'));
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