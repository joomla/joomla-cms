<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Templates
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Templates component
 *
 * @static
 * @package		Joomla
 * @subpackage	Templates
 * @since 1.6
 */
class TemplatesViewCsschoose extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		JToolBarHelper::title( JText::_( 'Template CSS Editor' ), 'thememanager' );
		JToolBarHelper::custom( 'edit_css', 'edit.png', 'edit_f2.png', 'Edit', true );
		JToolBarHelper::cancel('edit');
		JToolBarHelper::help( 'screen.templates' );

		// Initialize some variables
		$option 	= JRequest::getCmd('option');
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		// Determine template CSS directory
		$dir = $client->path.DS.'templates'.DS.$template.DS.'css';

		// List template .css files
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '\.css$', false, false);

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('template',	$template);
		$this->assignRef('files',		$files);

		parent::display($tpl);
	}
}