<?php
/**
 * @version		$Id: view.html.php 11838 2009-05-27 22:07:20Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
class TemplatesViewTemplate extends JView
{
	protected $params = null;
	protected $template = null;
	protected $ftp = null;
	protected $client = null;
	protected $option = null;
	protected $row = null;
	protected $lists = null;
	protected $templatefile = null;

	public function display($tpl = null)
	{

		jimport('joomla.filesystem.path');
		$this->loadHelper('template');

		$client		= &$this->get('Client');
		
		JToolBarHelper::title(JText::_('Template') . ': <small><small>[ '. JText::_('Edit') .' ]</small></small>', 'thememanager');
		if ($client->id == 1) {
			JToolBarHelper::custom('admindefault', 'default.png', 'default_f2.png', 'Set as Default', false, false);
		}
		JToolBarHelper::custom('add', 'new.png', 'new_f2.png', 'New Style', false, false);
		JToolBarHelper::custom('delete', 'delete.png', 'delete_f2.png', 'Delete Style', false, false);
		JToolBarHelper::custom('preview', 'preview.png', 'preview_f2.png', 'Preview', false, false);
		JToolBarHelper::custom('edit_source', 'html.png', 'html_f2.png', 'Edit HTML', false, false);
		JToolBarHelper::custom('choose_css', 'css.png', 'css_f2.png', 'Edit CSS', false, false);
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel', 'Close');
		JToolBarHelper::help('screen.templates');

		$row		= &$this->get('Data');
		$params		= &$this->get('Params');
		$template	= &$this->get('Template');
		$style		= &$this->get('Style');

		if (!$template) {
			return JError::raiseWarning(500, JText::_('Template not specified'));
		}

		if ($client->id == '1')  {
			$lists['selections'] =  JText::_('Cannot assign an administrator template');
		} else {
			$lists['selections'] = TemplatesHelper::createMenuList($row->id);
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);
		$this->assign('option',		JRequest::getCMD('option'));
		$this->assignRef('client',		$client);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('template',	$template);
		$this->assignRef('params',		$params);
		$this->assignRef('style',		$style);

		parent::display($tpl);
	}
}
