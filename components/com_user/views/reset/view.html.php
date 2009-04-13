<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Users component
 *
 * @package		Joomla
 * @subpackage	User
 * @since		1.5
 */
class UserViewReset extends JView
{
	/**
	 * Registry namespace prefix
	 *
	 * @var	string
	 */
	var $_namespace	= 'com_user.reset.';

	/**
	 * Display function
	 *
	 * @since 1.5
	 */
	function display($tpl = null)
	{
		jimport('joomla.html.html');

		$mainframe = JFactory::getApplication();

		// Load the form validation behavior
		JHtml::_('behavior.formvalidation');

		// Add the tooltip behavior
		JHtml::_('behavior.tooltip');

		// Get the layout
		$layout	= $this->getLayout();

		if ($layout == 'complete')
		{
			$id		= $mainframe->getUserState($this->_namespace.'id');
			$token	= $mainframe->getUserState($this->_namespace.'token');

			if (is_null($id) || is_null($token))
			{
				$mainframe->redirect('index.php?option=com_user&view=reset');
			}
		}

		// Get the page/component configuration
		$params = &$mainframe->getParams();

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'FORGOT_YOUR_PASSWORD' ));
			}
		} else {
			$params->set('page_title',	JText::_( 'FORGOT_YOUR_PASSWORD' ));
		}
		$document	= &JFactory::getDocument();
		$document->setTitle( $params->get( 'page_title' ) );

		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}
