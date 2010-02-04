<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Templates component
 *
 * @static
 * @package		Joomla.Administrator
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
		JToolBarHelper::title(JText::_('Template Manager'), 'thememanager');
		JToolBarHelper::custom('edit', 'back.png', 'back_f2.png', 'Back', false, false);

		require_once JPATH_COMPONENT.DS.'helpers'.DS.'templates.php';

		// Initialise some variables
		$option		= JRequest::getCmd('option');
		$id			= JRequest::getVar('id', '', 'method', 'int');
		$template	= TemplatesHelper::getTemplateName($id);
		$client		= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$tp			= true;
		$url		= $client->id ? JURI::base() : JURI::root();

		if (!$template)
		{
			return JError::raiseWarning(500, JText::_('Template not specified'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('id',			$id);
		$this->assignRef('template',	$template);
		$this->assignRef('tp',			$tp);
		$this->assignRef('url',			$url);

		parent::display($tpl);
	}
}
