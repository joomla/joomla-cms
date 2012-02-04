<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML helper class for rendering manipulated strings.
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JHtmlString
{
	/**
	 * Truncates text blocks over the specified character limit and closes
	 * all open HTML tags. The method will optionally not truncate an individual
	 * word, it will find the first space that is within the limit and
	 * truncate at that point. This method is UTF-8 safe.
	 *
	 * @param   string   $text       The text to truncate.
	 * @param   integer  $length     The maximum length of the text.
	 * @param   boolean  $noSplit    Don't split a word if that is where the cutoff occurs (default: true).
	 * @param   boolean  $allowHtml  Allow HTML tags in the output, and close any open tags (default: true).
	 *
	 * @return  string   The truncated text.
	 *
	 * @since   11.1
	 */
	public static function truncate($text, $length = 0, $noSplit = true, $allowHtml = true)
	{
		// Check if HTML tags are allowed.
		if (!$allowHtml)
		{
			// Deal with spacing issues in the input.
			$text = str_replace('>', '> ', $text);
			$text = str_replace(array('&nbsp;', '&#160;'), ' ', $text);
			$text = JString::trim(preg_replace('#\s+#mui', ' ', $text));

			// Strip the tags from the input and decode entities.
			$text = strip_tags($text);
			$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

			// Remove remaining extra spaces.
			$text = str_replace('&nbsp;', ' ', $text);
			$text = JString::trim(preg_replace('#\s+#mui', ' ', $text));
		}

		// Truncate the item text if it is too long.
		if ($length > 0 && JString::strlen($text) > $length)
		{
			// Find the first space within the allowed length.
			$tmp = JString::substr($text, 0, $length);

			if ($noSplit)
			{
				$offset = JString::strrpos($tmp, ' ');
				if (JString::strrpos($tmp, '<') > JString::strrpos($tmp, '>'))
				{
					$offset = JString::strrpos($tmp, '<');
				}
				$tmp = JString::substr($tmp, 0, $offset);

				// If we don't have 3 characters of room, go to the second space within the limit.
				if (JString::strlen($tmp) > $length - 3)
				{
					$tmp = JString::substr($tmp, 0, JString::strrpos($tmp, ' '));
				}
			}

			if ($allowHtml)
			{
				// Put all opened tags into an array
				preg_match_all("#<([a-z][a-z0-9]*)\b.*?(?!/)>#i", $tmp, $result);
				$openedTags = $result[1];
				$openedTags = array_diff($openedTags, array("img", "hr", "br"));
				$openedTags = array_values($openedTags);

				// Put all closed tags into an array
				preg_match_all("#</([a-z]+)>#iU", $tmp, $result);
				$closedTags = $result[1];

				$numOpened = count($openedTags);

				// All tags are closed
				if (count($closedTags) == $numOpened)
				{
					return $tmp . '...';
				}

				$openedTags = array_reverse($openedTags);

				// Close tags
				for ($i = 0; $i < $numOpened; $i++)
				{
					if (!in_array($openedTags[$i], $closedTags))
					{
						$tmp .= "</" . $openedTags[$i] . ">";
					}
					else
					{
						unset($closedTags[array_search($openedTags[$i], $closedTags)]);
					}
				}
			}

			$text = $tmp . '...';
		}

		return $text;
	}

	/**
	 * Abridges text strings over the specified character limit. The
	 * behavior will insert an ellipsis into the text replacing a section
	 * of variable size to ensure the string does not exceed the defined
	 * maximum length. This method is UTF-8 safe.
	 *
	 * For example, it transforms "Really long title" to "Really...title".
	 *
	 * Note that this method does not scan for HTML tags so will potentially break them.
	 *
	 * @param   string   $text    The text to abridge.
	 * @param   integer  $length  The maximum length of the text (default is 50).
	 * @param   integer  $intro   The maximum length of the intro text (default is 30).
	 *
	 * @return  string   The abridged text.
	 *
	 * @since   11.1
	 */
	public static function abridge($text, $length = 50, $intro = 30)
	{
		// Abridge the item text if it is too long.
		if (JString::strlen($text) > $length)
		{
			// Determine the remaining text length.
			$remainder = $length - ($intro + 3);

			// Extract the beginning and ending text sections.
			$beg = JString::substr($text, 0, $intro);
			$end = JString::substr($text, JString::strlen($text) - $remainder);

			// Build the resulting string.
			$text = $beg . '...' . $end;
		}

		return $text;
	}
}
