<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
			return JHTML::_('image', 'menu/icon-16-default.png', JText::_('Langs_Default'), NULL, true);
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

	public static function clients()
	{
		return array(
			JHtml::_('select.option', 0, JText::_('Langs_Option_Client_Site')),
			JHtml::_('select.option', 1, JText::_('Langs_Option_Client_Administrator'))
		);
	}

	/**
	 * Returns an array of published state filter options.
	 *
	 * @return	string			The HTML code for the select tag
	 * @since	1.6
	 */
	public static function publishedOptions()
	{
		// Build the active state filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '1', 'JOption_Published');
		$options[]	= JHtml::_('select.option', '0', 'JOption_Unpublished');
		$options[]	= JHtml::_('select.option', '-1', 'Langs_Option_Disabled');
		$options[]	= JHtml::_('select.option', '-2', 'JOption_Trash');
		$options[]	= JHtml::_('select.option', '*', 'JOption_All');

		return $options;
	}

}

