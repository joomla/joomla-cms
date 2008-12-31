<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Templates
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
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
	protected $url = null;
	protected $tp = null;
	protected $template = null;

	public function display($tpl = null)
	{
		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );
		JToolBarHelper::back();

		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$option 	= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$tp			= true;
		$url		= $client->id ? JURI::base() : JURI::root();

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
