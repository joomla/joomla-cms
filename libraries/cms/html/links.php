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
 * Utility class for icons.
 *
 * @since  3.2
 */
abstract class JHtmlLinks
{
	/**
	 * Method to generate html code for groups of lists of links
	 *
	 * @param   array  $groupsOfLinks  Array of links
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function linksgroups($groupsOfLinks)
	{
		$html = array();

		if (count($groupsOfLinks) > 0)
		{
			$layout = new JLayoutFile('joomla.links.groupsopen');
			$html[] = $layout->render('');

			foreach ($groupsOfLinks as $title => $links)
			{
				if (isset($links[0]['separategroup']))
				{
					$layout = new JLayoutFile('joomla.links.groupseparator');
					$html[] = $layout->render($title);
				}

				$layout = new JLayoutFile('joomla.links.groupopen');
				$htmlHeader = $layout->render($title);

				$htmlLinks  = JHtml::_('links.links', $links);

				if ($htmlLinks != '')
				{
					$html[] = $htmlHeader;
					$html[] = $htmlLinks;

					$layout = new JLayoutFile('joomla.links.groupclose');
					$html[] = $layout->render('');
				}
			}

			$layout = new JLayoutFile('joomla.links.groupsclose');
			$html[] = $layout->render('');
		}

		return implode($html);
	}

	/**
	 * Method to generate html code for a list of links
	 *
	 * @param   array  $links  Array of links
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function links($links)
	{
		$html = array();

		foreach ($links as $link)
		{
			$html[] = JHtml::_('links.link', $link);
		}

		return implode($html);
	}

	/**
	 * Method to generate html code for a single link
	 *
	 * @param   array  $link  link properties
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function link($link)
	{
		if (isset($link['access']))
		{
			if (is_bool($link['access']))
			{
				if ($link['access'] == false)
				{
					return '';
				}
			}
			else
			{
				// Get the user object to verify permissions
				$user = JFactory::getUser();

				// Take each pair of permission, context values.
				for ($i = 0, $n = count($link['access']); $i < $n; $i += 2)
				{
					if (!$user->authorise($link['access'][$i], $link['access'][$i + 1]))
					{
						return '';
					}
				}
			}
		}

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('joomla.links.link');

		return $layout->render($link);
	}
}
