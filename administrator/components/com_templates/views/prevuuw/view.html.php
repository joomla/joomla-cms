<?php
/**
* @version		$Id$
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
class TemplatesViewPrevuuw extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );
		JToolBarHelper::back();

		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$option 	= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$tp			= true;
		$url		= $client->id ? JURI::base() : $mainframe->getSiteURL();

		if (!$template)
		{
			return JError::raiseWarning( 500, JText::_('Template not specified') );
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('template',	$template);
		$this->assignRef('tp',			$tp);
		$this->assignRef('url',			$url);

		parent::display($tpl);
	}
}