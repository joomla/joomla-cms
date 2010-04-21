<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * HTML helper class for rendering manipulated strings.
 *
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.6
 */
class JHtmlString
{
	/**
	 * Truncates text blocks over the specified character limit and closes
	 * all open HTML tags. The behavior will not truncate an individual
	 * word, it will find the first space that is within the limit and
	 * truncate at that point. This method is UTF-8 safe.
	 *
	 * @static
	 * @param	string	$text		The text to truncate.
	 * @param	int		$length		The maximum length of the text.
	 * @return	string	The truncated text.
	 */
	function truncate($text, $length = 0)
	{
		// Truncate the item text if it is too long.
		if ($length > 0 && JString::strlen($text) > $length)
		{
			// Find the first space within the allowed length.
			$tmp = JString::substr($text, 0, $length);
			$offset = JString::strrpos($tmp, ' ');
			if(JString::strrpos($tmp, '<') > JString::strrpos($tmp, '>'))
			{
				$offset = JString::strrpos($tmp, '<');
			}
			$tmp = JString::substr($tmp, 0, $offset);

			// If we don't have 3 characters of room, go to the second space within the limit.
			if (JString::strlen($tmp) >= $length - 3) {
				$tmp = JString::substr($tmp, 0, JString::strrpos($tmp, ' '));
			}

			//put all opened tags into an array
			preg_match_all ( "#<([a-z][a-z0-9]?)( .*)?(?!/)>#iU", $tmp, $result );
			$openedtags = $result[1];
			$openedtags = array_diff($openedtags, array("img", "hr", "br"));
			$openedtags = array_values($openedtags);

			//put all closed tags into an array
			preg_match_all ( "#</([a-z]+)>#iU", $tmp, $result );
			$closedtags = $result[1];
			$len_opened = count ( $openedtags );
			//all tags are closed
			if( count ( $closedtags ) == $len_opened )
			{
				return $tmp.'...';
			}
			$openedtags = array_reverse ( $openedtags );
			// close tags
			for( $i = 0; $i < $len_opened; $i++ )
			{
				if ( !in_array ( $openedtags[$i], $closedtags ) )
				{
					$tmp .= "</" . $openedtags[$i] . ">";
				} else {
					unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
				}
			}
			$text = $tmp.'...';
		}

		return $text;
	}

	/**
	 * Abridges text strings over the specified character limit. The
	 * behavior will insert an ellipsis into the text replacing a section
	 * of variable size to ensure the string does not exceed the defined
	 * maximum length. This method is UTF-8 safe.
	 *
	 *	eg. Transform "Really long title" to "Really...title"
	 *
	 * @static
	 * @param	string	$text		The text to abridge.
	 * @param	int		$length		The maximum length of the text.
	 * @param	int		$intro		The maximum length of the intro text.
	 * @return	string	The abridged text.
	 */
	function abridge($text, $length = 50, $intro = 30)
	{
		// Abridge the item text if it is too long.
		if (JString::strlen($text) > $length) {
			// Determine the remaining text length.
			$remainder = $length - ($intro + 3);

			// Extract the beginning and ending text sections.
			$beg = JString::substr($text, 0, $intro);
			$end = JString::substr($text, JString::strlen($text)-$remainder);

			// Build the resulting string.
			$text = $beg.'...'.$end;
		}

		return $text;
	}
}