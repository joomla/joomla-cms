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
class TemplatesViewCsschoose extends JView
{
	protected $option = null;
	protected $client = null;
	protected $template = null;
	protected $files = null;

	public function display($tpl = null)
	{

		JToolBarHelper::title(JText::_('Template CSS Editor'), 'thememanager');
		JToolBarHelper::custom('edit_css', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::cancel('edit');
		JToolBarHelper::help('screen.templates');

		require_once JPATH_COMPONENT.DS.'helpers'.DS.'template.php';

		// Initialize some variables
		$option 	= JRequest::getCmd('option');
		$id 		= JRequest::getVar('id', '', 'method', 'int');
		$template	=  TemplatesHelper::getTemplateName($id);
		$client		= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		// Determine template CSS directory
		$dir = $client->path.DS.'templates'.DS.$template.DS.'css';

		// List template .css files
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '\.css$', false, false);

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->assign('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('template',	$template);
		$this->assignRef('files',		$files);
		$this->assignRef('id',		$id);
		$this->assignRef('t_dir',		$dir);

		parent::display($tpl);
	}
}
