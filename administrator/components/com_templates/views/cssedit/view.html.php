<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
class TemplatesViewCssedit extends JView
{
	protected $option = null;
	protected $client = null;
	protected $ftp = null;
	protected $template = null;
	protected $content = null;

	public function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('Template Manager'), 'thememanager');
		
		JToolBarHelper::apply('apply_css');
		JToolBarHelper::save('save_css');
		JToolBarHelper::cancel('choose_css');
		JToolBarHelper::help('screen.templates');

		// Initialize some variables
		$option		= JRequest::getCmd('option');

		$content	= &$this->get('Data');
		$client		= &$this->get('Client');
		$template	= &$this->get('Template');
		$filename	= &$this->get('Filename');
		$id	= &$this->get('Id');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		$this->assign('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('template',	$template);
		$this->assignRef('id',	$id);
		$this->assignRef('filename',	$filename);
		$this->assignRef('content',		$content);

		parent::display($tpl);
	}
}
