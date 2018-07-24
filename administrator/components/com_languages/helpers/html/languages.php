<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class working with languages
 *
 * @since  1.6
 */
abstract class JHtmlLanguages
{
	/**
	 * Method to generate an information about the default language.
	 *
	 * @param   boolean  $published  True if the language is the default.
	 *
	 * @return  string	HTML code.
	 */
	public static function published($published)
	{
		if (!$published)
		{
			return '&#160;';
		}

		return JHtml::_('image', 'menu/icon-16-default.png', JText::_('COM_LANGUAGES_HEADING_DEFAULT'), null, true);
	}

	/**
	 * Method to generate an input radio button.
	 *
	 * @param   integer  $rowNum    The row number.
	 * @param   string   $language  Language tag.
	 *
	 * @return  string	HTML code.
	 */
	public static function id($rowNum, $language)
	{
		return '<input'
			. ' type="radio"'
			. ' id="cb' . $rowNum . '"'
			. ' name="cid"'
			. ' value="' . htmlspecialchars($language, ENT_COMPAT, 'UTF-8') . '"'
			. ' onclick="Joomla.isChecked(this.checked);"'
			. ' title="' . ($rowNum + 1) . '"'
			. '/>';
	}

	/**
	 * Method to generate an array of clients.
	 *
	 * @return  array of client objects.
	 */
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
	 * @return  string  	The HTML code for the select tag.
	 *
	 * @since   1.6
	 */
	public static function publishedOptions()
	{
		// Build the active state filter options.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', 'JPUBLISHED');
		$options[] = JHtml::_('select.option', '0', 'JUNPUBLISHED');
		$options[] = JHtml::_('select.option', '-2', 'JTRASHED');
		$options[] = JHtml::_('select.option', '*', 'JALL');

		return $options;
	}
}
