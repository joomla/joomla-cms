<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

require_once dirname(__FILE__).DS.'list.php';

/**
 * Parameter to display a list of the layouts for a component view from the extension or default template overrides.
 *
 * @package		Joomla.Framework
 * @subpackage	Parameter
 */
class JElementComponentLayouts extends JElementList
{
	/**
	 * @var		string
	 */
	protected $_name = 'ComponentLayouts';

	/**
	 * Get the options for the list.
	 */
	protected function _getOptions(&$node)
	{
		$options	= array();
		$path1		= null;
		$path2		= null;

		// Load template entries for each menuid
		$db			=& JFactory::getDBO();
		$query		= 'SELECT template'
			. ' FROM #__template_styles'
			. ' WHERE client_id = 0 AND home = 1';
		$db->setQuery($query);
		$template	= $db->loadResult();

		if ($view = $node->attributes('view') && $extn = $node->attributes('extension'))
		{
			$view	= preg_replace('#\W#', '', $view);
			$extn	= preg_replace('#\W#', '', $extn);
			$path1	= JPATH_SITE.DS.'components'.DS.$extn.DS.'views'.DS.$view.DS.'tmpl';
			$path2	= JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.$extn.DS.$view;
			$options[]	= JHTML::_('select.option', '', JText::_('JOption_Use_Menu_Request_Setting'));
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
				$options[]	= JHTML::_('select.optgroup', JText::_('JOPTION_FROM_DEFAULT_TEMPLATE'));
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