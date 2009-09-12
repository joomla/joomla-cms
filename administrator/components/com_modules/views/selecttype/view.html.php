<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Modules component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @since 1.6
 */
class ModulesViewSelecttype extends JView
{
	function display($tpl = null)
	{
		// Initialize some variables
		$modules	= array();
		$client		= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		JToolBarHelper::title(JText::_('Module_Manager') . ': '. JText::_('Add_New_Module') .'', 'module.png');
		JToolBarHelper::cancel();
		JToolBarHelper::help('screen.modules.new');

		// path to search for modules
		if ($client->id == '1') {
			$path		= JPATH_ADMINISTRATOR.DS.'modules';
			$langbase	= JPATH_ADMINISTRATOR;
		} else {
			$path		= JPATH_ROOT.DS.'modules';
			$langbase	= JPATH_ROOT;
		}

		jimport('joomla.filesystem.folder');
		$dirs = JFolder::folders($path);
		$lang = &JFactory::getLanguage();

		foreach ($dirs as $dir)
		{
			if (substr($dir, 0, 4) == 'mod_')
			{
				$files 				= JFolder::files($path.DS.$dir, '^([_A-Za-z0-9]*)\.xml$');
				$module				= new stdClass;
				$module->file 		= $files[0];
				$module->module 	= str_replace('.xml', '', $files[0]);
				$module->path 		= $path.DS.$dir;
				$modules[]			= $module;

				// 1.5 Format; Core files or language packs
				$lang->load($module->module, $langbase);
				// 1.6 3PD Extension Support
				$lang->load('joomla', $langbase.DS.'modules'.DS.$module->module);
			}
		}

		$this->loadHelper('xml');
		ModulesHelperXML::parseXMLModuleFile($modules, $client);

		// sort array of objects alphabetically by name
		JArrayHelper::sortObjects($modules, 'name');

		$this->assignRef('modules',		$modules);
		$this->assignRef('client',		$client);

		parent::display($tpl);
	}
}
