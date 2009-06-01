<?php
/**
 * @version		$Id: view.html.php 11838 2009-05-27 22:07:20Z eddieajau $
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
class TemplatesViewSource extends JView
{
	protected $content = null;
	protected $template = null;
	protected $ftp = null;
	protected $client = null;
	protected $option = null;

	public function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('Template HTML Editor'), 'thememanager');
		JToolBarHelper::save('save_source');
		JToolBarHelper::apply('apply_source');
		JToolBarHelper::cancel('edit');
		JToolBarHelper::help('screen.templates');

		// Initialize some variables
		$option		= JRequest::getCmd('option');

		$content	= &$this->get('Data');
		$client		= &$this->get('Client');
		$template	= &$this->get('Template');
		$id	= &$this->get('id');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('template',	$template);
		$this->assignRef('id',			$id);
		$this->assignRef('content',	$content);

		parent::display($tpl);
	}
}
