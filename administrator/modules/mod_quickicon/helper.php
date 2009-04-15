<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class IconHelper
{
	public static function button($link, $image, $text, $accessCondition = null)
	{
		if (!empty($accessCondition)) {
			if (!JFactory::getUser()->authorize($accessCondition)) {
				return '';
			}
		}

		$float		= JFactory::getLanguage()->isRTL() ? 'right' : 'left';
		$template	= JFactory::getApplication()->getTemplate();
		ob_start();
		include JModuleHelper::getLayoutPath('mod_quickicon', 'button');
		$html = ob_get_clean();
		return $html;
	}
}