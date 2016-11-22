<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content component fields helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentHelperFields
{

	/**
	 * Map the section for custom fields.
	 *
	 * @param   string  $section    The section to get the mapping for
	 * @param   string  $component  The component to get the mapping for
	 *
	 * @return  string  The new context
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getValidContext($section, $component)
	{
		if (JFactory::getApplication()->isClient('site'))
		{
			switch ($section)
			{
				case 'form':
				case 'category':
					$section = 'article';
			}
		}
		return $component . '.' . $section;
	}
}
