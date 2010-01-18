<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * HTML behavior class for JXtended Comments
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		2.0
 */
class JHtmlCommentModeration
{

	function action($item)
	{
		$html = array();
		$html[] = '<ul class="published_selector">';

		// Defer is only an option if a state has not yet been set.
		if ($item->published == 0)
		{
			$html[] = '<li class="defer"><input type="radio" id="moderate_defer_'.$item->id.'" name="moderate['.$item->id.']" value="0" checked="checked" />';
			$html[] = '	<label for="moderate_defer_'.$item->id.'">'.JText::_('COMMENTS_DEFER').'</label></li>';
		}

		// Add the publish state.
		$html[] = '<li class="publish"><input type="radio" id="moderate_publish_'.$item->id.'" name="moderate['.$item->id.']" value="1"'.(($item->published == 1) ? ' checked="checked"' : null).' />';
		$html[] = '	<label for="moderate_publish_'.$item->id.'">'.JText::_('COMMENTS_PUBLISH').'</label></li>';

		// Add the spam state.
		$html[] = '<li class="spam"><input type="radio" id="moderate_spam_'.$item->id.'" name="moderate['.$item->id.']" value="2"'.(($item->published == 2) ? ' checked="checked"' : null).' />';
		$html[] = '	<label for="moderate_spam_'.$item->id.'">'.JText::_('COMMENTS_SPAM').'</label></li>';

		// Add the delete state.
		$html[] = '<li class="delete"><input type="radio" id="moderate_delete_'.$item->id.'" name="moderate['.$item->id.']" value="-1"'.(($item->published == -1) ? ' checked="checked"' : null).' />';
		$html[] = '	<label for="moderate_delete_'.$item->id.'">'.JText::_('COMMENTS_DELETE').'</label></li>';

		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Method to render a given parameters form.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	$name	The name of the array for form elements.
	 * @param	string	$ini	An INI formatted string.
	 * @param	string	$file	The XML file to render.
	 * @return	string	A HTML rendered parameters form.
	 */
	function params($name, $ini, $file)
	{
		jimport('joomla.html.parameter');

		// Load and render the parameters
		$path	= JPATH_COMPONENT.DS.$file;
		$params	= new JParameter($ini, $path);
		$output	= $params->render($name);

		return $output;
	}
}
