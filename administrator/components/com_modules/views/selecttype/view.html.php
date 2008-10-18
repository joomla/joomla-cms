<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Modules
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
 * HTML View class for the Modules component
 *
 * @static
 * @package		Joomla
 * @subpackage	Modules
 * @since 1.6
 */
class ModulesViewSelecttype extends JView
{
	function display($tpl = null)
	{
		// Initialize some variables
		$modules	= array();
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		JToolBarHelper::title( JText::_( 'Module' ) . ': <small><small>[ '. JText::_( 'New' ) .' ]</small></small>', 'module.png' );
		JToolBarHelper::customX( 'edit', 'forward.png', 'forward_f2.png', 'Next', true );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.modules.new' );

		// path to search for modules
		if ($client->id == '1') {
			$path		= JPATH_ADMINISTRATOR.DS.'modules';
			$langbase	= JPATH_ADMINISTRATOR;
		} else {
			$path		= JPATH_ROOT.DS.'modules';
			$langbase	= JPATH_ROOT;
		}

		jimport('joomla.filesystem.folder');
		$dirs = JFolder::folders( $path );
		$lang =& JFactory::getLanguage();

		foreach ($dirs as $dir)
		{
			if (substr( $dir, 0, 4 ) == 'mod_')
			{
				$files 				= JFolder::files( $path.DS.$dir, '^([_A-Za-z0-9]*)\.xml$' );
				$module				= new stdClass;
				$module->file 		= $files[0];
				$module->module 	= str_replace( '.xml', '', $files[0] );
				$module->path 		= $path.DS.$dir;
				$modules[]			= $module;

				$lang->load( $module->module, $langbase );
			}
		}

		$this->loadHelper('xml');
		ModulesHelperXML::parseXMLModuleFile( $modules, $client );

		// sort array of objects alphabetically by name
		JArrayHelper::sortObjects( $modules, 'name' );

		$this->assignRef('modules',		$modules);
		$this->assignRef('client',		$client);

		parent::display($tpl);
	}
}