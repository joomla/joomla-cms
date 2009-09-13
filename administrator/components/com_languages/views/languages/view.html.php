<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Languages View class for the Languages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesViewLanguages extends JView
{
	/**
	 * @var array languages information
	 */
	protected $rows=null;
	
	/**
	 * @var object pagination information
	 */
	protected $pagination=null;
	
	/**
	 * @var boolean|JExeption True, if FTP settings should be shown, or an exeption
	 */
	protected $ftp = null;

	/**
	 * @var object client object
	 */
	protected $client = null;

	/**
	 * @var object user object
	 */
	protected $user = null;

	/**
	 * @var string option name
	 */
	protected $option = null;

	
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$rows		= & $this->get('Data');
		$pagination = & $this->get('Pagination');
		$ftp		= & $this->get('Ftp');
		$client		= & $this->get('Client');
		$user		= & $this->get('User');
		$option		= & $this->get('Option');

		// Assign data to the view
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('client',		$client);
		$this->assignRef('user',		$user);
		$this->assignRef('option',		$option);
		
		// Set the toolbar and the submenu
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
		JToolBarHelper::title(JText::_('Languages_Language_Manager'), 'langmanager.png');
		JToolBarHelper::makeDefault('publish');
		JToolBarHelper::divider();		
		JToolBarHelper::help('screen.languages');
	}
}
