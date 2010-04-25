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
 * Registration view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewRegistration extends JView
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
		$form	= &$this->get('Form');
		$data	= &$this->get('Data');
		$state	= $this->get('State');
		$params	= $state->get('params');

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Bind the data to the form.
		if ($form) {
			$form->bind($data);
		}

		// Push the data into the view.
		$this->assignRef('form',	$form);
		$this->assignRef('data',	$data);
		$this->assignRef('params',	$params);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= &JFactory::getApplication();
		$menus		= &JSite::getMenu();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_USERS_Registration'));
		}

		$title = $this->params->get('page_title', $this->params->get('page_heading'));
		if (empty($title))
		{
			$title = htmlspecialchars_decode($app->getCfg('sitename'));
		}
		$this->document->setTitle($title);
	}
}