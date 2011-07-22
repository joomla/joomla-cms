<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once dirname(__FILE__).'/list.php';

/**
 * Parameter to display a list of the layouts for a module from the module or default template overrides.
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @deprecated  Use JFormFieldModuleLayout instead
 * @note        Note that JFormFieldModuleLayout does not end in s.
 */
class JElementModuleLayouts extends JElementList
{
	/**
	 * @var		string
	 */
	protected $_name = 'ModuleLayouts';

	/**
	 * Get the options for the list.
	 * @since   11.1
	 *
	 * @deprecated    12.1   Use JFormFieldModuleLayouts::getInput instead.
	 */
	protected function _getOptions(&$node)
	{
		// Deprecation warning.
		JLog::add('JElementModuleLayouts::_getOptions() is deprecated.', JLog::WARNING, 'deprecated');
		
		$clientId = ($v = $node->attributes('client_id')) ? $v : 0;

		$options	= array();
		$path1		= null;
		$path2		= null;

		// Load template entries for each menuid
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->select('template');
		$query->from('#__template_styles');
		$query->where('client_id = '.(int) $clientId);
		$query->where('home = 1');
		$db->setQuery($query);
		$template	= $db->loadResult();

		if ($module = $node->attributes('module')) {
			$base	= ($clientId == 1) ? JPATH_ADMINISTRATOR : JPATH_SITE;
			$module	= preg_replace('#\W#', '', $module);
			$path1	= $base . '/modules/' . $module . '/tmpl';
			$path2	= $base . '/templates/' . $template . '/html/' . $module;
			$options[]	= JHTML::_('select.option', '', '');
		}

		if ($path1 && $path2) {
			jimport('joomla.filesystem.file');
			$path1 = JPath::clean($path1);
			$path2 = JPath::clean($path2);

			$files	= JFolder::files($path1, '^[^_]*\.php$');
			foreach ($files as $file) {
				$options[]	= JHTML::_('select.option', JFile::stripExt($file));
			}

			if (is_dir($path2) && $files = JFolder::files($path2, '^[^_]*\.php$')) {
				$options[]	= JHTML::_('select.optgroup', JText::_('JOPTION_FROM_DEFAULT'));
				foreach ($files as $file) {
					$options[]	= JHTML::_('select.option', JFile::stripExt($file));
				}
				$options[]	= JHTML::_('select.optgroup', JText::_('JOPTION_FROM_DEFAULT'));
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions($node), $options);

		return $options;
	}
}