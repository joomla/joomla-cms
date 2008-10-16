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
class TemplatesViewSource extends JView
{
	function display($tpl = null)
	{

		global $mainframe;

		JToolBarHelper::title( JText::_( 'Template HTML Editor' ), 'thememanager' );
		JToolBarHelper::save( 'save_source' );
		JToolBarHelper::apply( 'apply_source' );
		JToolBarHelper::cancel('edit');
		JToolBarHelper::help( 'screen.templates' );

		// Initialize some variables
		$option		= JRequest::getCmd('option');

		$content	=& $this->get('Data');
		$client		=& $this->get('Client');
		$template	=& $this->get('Template');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('template',	$template);
		$this->assignRef('content',	$content);

		parent::display($tpl);
	}
}