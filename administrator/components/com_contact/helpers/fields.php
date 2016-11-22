<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact component fields helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContactHelperFields
{

	/**
	 * Map the section for custom fields.
	 *
	 * @param   string  $section    The section to get the mapping for
	 *
	 * @return  string  The new section
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getRealSection($section)
	{
		if (JFactory::getApplication()->isClient('site') && $section == 'contact')
		{
			$section = 'mail';
		}
		return $section;
	}
}
