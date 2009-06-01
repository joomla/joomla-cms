<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

abstract class QuickIconHelper
{
	/**
	 * Helper method to generate a button in administrator panel
	 *
	 * @param	array	A named array with properties link, image, text and access
	 * @return	string	HTML for button
	 */
	public static function button($button)
	{
		if (!empty($button['access'])) {
			if (!JFactory::getUser()->authorize($button['access'])) {
				return '';
			}
		}

		$float		= JFactory::getLanguage()->isRTL() ? 'right' : 'left';
		$template	= JFactory::getApplication()->getTemplate();
		ob_start();
		require JModuleHelper::getLayoutPath('mod_quickicon', 'button');
		$html = ob_get_clean();
		return $html;
	}
}
