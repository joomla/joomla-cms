<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for the Joomla core UI tips.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlUiTips
{
	/**
	 * Helper for rendering tooltips
	 *
	 * @param   string   $title      Unused, kept for B/C
	 * @param   string   $content    The content to tooltip.
	 * @param   boolean  $translate  If true will pass texts through JText.
	 * @param   boolean  $escape     If true will pass texts through htmlspecialchars.
	 *
	 * @return  string  The tooltip string
	 *
	 * @since   4.0.0
	 */
	public static function tipText($title = null, $content = '', $translate = true, $escape = true)
	{
		// Don't process empty strings
		if ($content !== '')
		{
			$content = preg_replace('$(\&lt;?\/|<?\/)*[a-zA-Z]*(\&lt;|\/?\&gt;|\/?>)$', '', $content);

			// Pass texts through JText if required.
			if ($translate)
			{
				$content = Text::_($content);
			}

			// Escape everything, if required.
			if ($escape)
			{
				$result = htmlspecialchars($content);
			}

			return $result;
		}

		return '';
	}
}
