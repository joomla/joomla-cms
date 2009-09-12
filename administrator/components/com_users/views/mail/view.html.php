<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the MassMail component
 *
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 * @since 1.0
 */
class UsersViewMassMail extends JView
{
	/**
	 * @var object form object
	 */
	protected $form=null;
	
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$form = & $this->get('Form');

		// Assign data to the view
		$this->assignRef('form', $form);

		// Set the toolbar
		$this->_setToolBar();
		
		// Display the view
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolBar()
	{
		JToolBarHelper::title(JText::_('MassMail'), 'massmail.png');
		JToolBarHelper::custom('send', 'send.png', 'send_f2.png', 'MassMail_Send_Mail', false);
		JToolBarHelper::cancel();
		JToolBarHelper::preferences('com_massmail', '200');
		JToolBarHelper::help('screen.massmail');
	}
}