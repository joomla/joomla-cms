<?php
/**
 * @version		$Id: assetgroups.php 12193 2009-06-20 00:43:52Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

require_once dirname(__FILE__).DS.'list.php';

/**
 * Parameter to display a list of the layouts for a module from the module or default template overrides.
 *
 * @package		Joomla.Framework
 * @subpackage	Parameter
 */
class JElementModuleLayouts extends JElementList
{
	/**
	 * @var		string
	 */
	protected $_name = 'ModuleLayouts';

	/**
	 * Get the options for the list.
	 */
	protected function _getOptions(&$node)
	{
		jimport('joomla.database.query');

		$clientId = ($v = $node->attributes('client_id')) ? $v : 0;

		$options	= array();
		$path1		= null;
		$path2		= null;

		// Load template entries for each menuid
		$db			=& JFactory::getDBO();
		$query		= new JQuery;
		$query->select('template');
		$query->from('#__menu_template');
		$query->where('client_id = '.(int) $clientId);
		$query->where('home = 1');
		$db->setQuery($query);
		$template	= $db->loadResult();

		if ($module = $node->attributes('module'))
		{
			$base	= ($clientId == 1) ? JPATH_ADMINISTRATOR : JPATH_SITE;
			$module	= preg_replace('#\W#', '', $module);
			$path1	= $base.DS.'modules'.DS.$module.DS.'tmpl';
			$path2	= $base.DS.'templates'.DS.$template.DS.'html'.DS.$module;
			$options[]	= JHTML::_('select.option', '', '');
		}

		if ($path1 && $path2)
		{
			jimport('joomla.filesystem.file');
			$path1 = JPath::clean($path1);
			$path2 = JPath::clean($path2);

			$files	= JFolder::files($path1, '^[^_]*\.php$');
			foreach ($files as $file) {
				$options[]	= JHTML::_('select.option', JFile::stripExt($file));
			}

			if (is_dir($path2) && $files = JFolder::files($path2, '^[^_]*\.php$'))
			{
				$options[]	= JHTML::_('select.optgroup', JText::_('JOption_From_Default'));
				foreach ($files as $file) {
					$options[]	= JHTML::_('select.option', JFile::stripExt($file));
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions($node), $options);

		return $options;
	}
}