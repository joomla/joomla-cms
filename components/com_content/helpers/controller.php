<?php
/**
 * @version		$Id: content.php 7054 2007-03-28 23:54:44Z louis $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component Helper Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentHelperController
{
	//TODO :: Move this function in the JTableContent class ??
	function saveContentPrep(& $row)
	{
		//Get submitted text from the request variables
		$text = JRequest::getVar('text', '', 'post', 'string', JREQUEST_ALLOWRAW);

		//Clean text for xhtml transitional compliance
		jimport('joomla.filter.output');
		$text = str_replace('<br>', '<br />', $text);
		$row->title = JOutputFilter::ampReplace($row->title);

		// Search for the {readmore} tag and split the text up accordingly.
		$tagPos = JString::strpos($text, '<hr id="system-readmore" />');

		if ($tagPos === false)	{
			$row->introtext = $text;
		} else 	{
			$row->introtext = JString::substr($text, 0, $tagPos);
			$row->fulltext = JString::substr($text, $tagPos +27);
		}

		return true;
	}
}
?>
