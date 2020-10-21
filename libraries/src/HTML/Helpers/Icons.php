<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for icons.
 *
 * @since  2.5
 */
abstract class Icons
{
	/**
	 * Method to write a `<span>` element for an icon
	 *
	 * @param   string        $icon        The funcitonal name for an icon.
	 * @param   string        $srOnly      Screen Reader text if no visible text is placed
	 * @param   array         $attribs     Attributes to be added to the wrapping element
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function icon($icon = 'default', $srOnly = null, $attribs = [])
	{
		if (isset($attribs['class']))
		{
			$icon = $icon . ' ' . $attribs['class'];
		}

		$attribs['class'] = $icon;

		if (!isset($attribs['aria-hidden']))
		{
			$attribs['aria-hidden'] = 'true';
		}

		if (isset($srOnly))
		{
			$text = htmlspecialchars($srOnly, ENT_COMPAT, 'UTF-8');
			$srOnly = '<span class="sr-only">' . $text . '</span>';
		}

		return '<span ' . trim(ArrayHelper::toString($attribs)) . '></span>' . $srOnly;
	}

	/**
	 * Method to generate html code for a list of buttons
	 *
	 * @param   array  $buttons  Array of buttons
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function buttons($buttons)
	{
		if (empty($buttons))
		{
			return '';
		}

		$html = array();

		foreach ($buttons as $button)
		{
			$html[] = HTMLHelper::_('icons.button', $button);
		}

		return implode($html);
	}

	/**
	 * Method to generate html code for a list of buttons
	 *
	 * @param   array  $button  Button properties
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function button($button)
	{
		if (isset($button['access']))
		{
			if (is_bool($button['access']))
			{
				if ($button['access'] == false)
				{
					return '';
				}
			}
			else
			{
				// Get the user object to verify permissions
				$user = Factory::getUser();

				// Take each pair of permission, context values.
				for ($i = 0, $n = count($button['access']); $i < $n; $i += 2)
				{
					if (!$user->authorise($button['access'][$i], $button['access'][$i + 1]))
					{
						return '';
					}
				}
			}
		}

		// Instantiate a new FileLayout instance and render the layout
		$layout = new FileLayout('joomla.quickicons.icon');

		return $layout->render($button);
	}
}
