<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags helper.
 *
 * @since       3.1
 * @deprecated  4.0
 */
class TagsHelper extends JHelperContent
{
	/**
	 * Configure the Submenu links.
	 *
	 * @param   string  $extension  The extension.
	 *
	 * @return  void
	 *
	 * @since       3.1
	 * @deprecated  4.0
	 */
	public static function addSubmenu($extension)
	{
		$parts     = explode('.', $extension);
		$component = $parts[0];

		// Avoid nonsense situation.
		if ($component == 'tags')
		{
			return;
		}

		// Try to find the component helper.
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_tags/helpers/tags.php');

		if (file_exists($file))
		{
			require_once $file;

			$cName = 'TagsHelper';

			if (class_exists($cName))
			{
				if (is_callable(array($cName, 'addSubmenu')))
				{
					$lang = JFactory::getLanguage();

					// Loading language file from administrator/language directory then administrator/components/<extension>/language
					$lang->load($component, JPATH_BASE, null, false, true)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);
				}
			}
		}
	}
}
