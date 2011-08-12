<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once dirname(__FILE__) . '/list.php';

/**
 * Parameter to display a list of the layouts for a component view from the extension or default template overrides.
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @deprecated  User JForm instead
 */
class JElementComponentLayouts extends JElementList
{
	/**
	 * @var    string
	 */
	protected $_name = 'ComponentLayouts';

	/**
	 * Get the options for the list.
	 *
	 * @return  array
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	protected function _getOptions(&$node)
	{
		$options	= array();
		$path1		= null;
		$path2		= null;

		// Load template entries for each menuid
		$db			= JFactory::getDBO();
		$query		= 'SELECT template'
			. ' FROM #__template_styles'
			. ' WHERE client_id = 0 AND home = 1';
		$db->setQuery($query);
		$template	= $db->loadResult();

		if ($view = $node->attributes('view') && $extn = $node->attributes('extension'))
		{
			$view	= preg_replace('#\W#', '', $view);
			$extn	= preg_replace('#\W#', '', $extn);
			$path1	= JPATH_SITE . '/components/' . $extn . '/views/' . $view . '/tmpl';
			$path2	= JPATH_SITE . '/templates/' . $template . '/html/' . $extn . '/' . $view;
			$options[]	= JHtml::_('select.option', '', JText::_('JOPTION_USE_MENU_REQUEST_SETTING'));
		}

		if ($path1 && $path2)
		{
			jimport('joomla.filesystem.file');
			$path1 = JPath::clean($path1);
			$path2 = JPath::clean($path2);

			$files	= JFolder::files($path1, '^[^_]*\.php$');
			foreach ($files as $file) {
				$options[]	= JHtml::_('select.option', JFile::stripExt($file));
			}

			if (is_dir($path2) && $files = JFolder::files($path2, '^[^_]*\.php$'))
			{
				$options[]	= JHtml::_('select.optgroup', JText::_('JOPTION_FROM_DEFAULT_TEMPLATE'));
				foreach ($files as $file) {
					$options[]	= JHtml::_('select.option', JFile::stripExt($file));
				}
				$options[]	= JHtml::_('select.optgroup', JText::_('JOPTION_FROM_DEFAULT_TEMPLATE'));
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions($node), $options);

		return $options;
	}
}