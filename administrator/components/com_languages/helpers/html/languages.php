<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class working with languages
 *
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       1.6
 */
abstract class JHtmlLanguages
{
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
			return JHtml::_('image', 'menu/icon-16-default.png', JText::_('COM_LANGUAGES_HEADING_DEFAULT'), null, true);
		}
		else {
			return '&#160;';
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
	public static function id($rowNum, $language)
	{
		return '<input type="radio" id="cb' . $rowNum . '" name="cid" value="' . htmlspecialchars($language) . '" onclick="Joomla.isChecked(this.checked);" title="' . ($rowNum + 1) . '"/>';
	}

	public static function clients()
	{
		return array(
			JHtml::_('select.option', 0, JText::_('JSITE')),
			JHtml::_('select.option', 1, JText::_('JADMINISTRATOR'))
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
		$options[]	= JHtml::_('select.option', '1', 'JPUBLISHED');
		$options[]	= JHtml::_('select.option', '0', 'JUNPUBLISHED');
		$options[]	= JHtml::_('select.option', '-2', 'JTRASHED');
		$options[]	= JHtml::_('select.option', '*', 'JALL');

		return $options;
	}

}
