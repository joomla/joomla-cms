<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Utility class working with languages
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
abstract class JHtmlLanguages {
	/**
	 * method to generate an information about the default language
	 *
	 * @param	boolean	$published is the language the default?
	 *
	 * @return	string	html code
	 */
	public static function published($published)
	{
		if ($published) {
			$app = & JFactory::getApplication();
			return '<img src="templates/'.$app->getTemplate().'/images/menu/icon-16-default.png" alt="'.JText::_('Languages_Default').'" />';
		}
		else {
			return '&nbsp;';
		}
	}

	/**
	 * method to generate an input radio button
	 *
	 * @param	int		$rowNum the row number
	 * @param	string	language tag 
	 *
	 * @return	string	html code
	 */
	public static function id($rowNum,$language)
	{
		return '<input type="radio" id="cb'.$rowNum.'" name="cid[]" value="'.$language.'" onclick="isChecked(this.checked);" />';
	}
}

