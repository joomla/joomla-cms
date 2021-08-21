<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Utility class working with languages
 *
 * @since  1.6
 */
class Languages
{
	/**
	 * Method to generate an information about the default language.
	 *
	 * @param   boolean  $published  True if the language is the default.
	 *
	 * @return  string	HTML code.
	 */
	public function published($published)
	{
		if (!$published)
		{
			return '&#160;';
		}

		return HTMLHelper::_('image', 'menu/icon-16-default.png', Text::_('COM_LANGUAGES_HEADING_DEFAULT'), null, true);
	}

	/**
	 * Method to generate an input radio button.
	 *
	 * @param   integer  $rowNum    The row number.
	 * @param   string   $language  Language tag.
	 *
	 * @return  string	HTML code.
	 */
	public function id($rowNum, $language)
	{
		return '<input'
			. ' class="form-check-input"'
			. ' type="radio"'
			. ' id="cb' . $rowNum . '"'
			. ' name="cid"'
			. ' value="' . htmlspecialchars($language, ENT_COMPAT, 'UTF-8') . '"'
			. ' onclick="Joomla.isChecked(this.checked);"'
			. ' title="' . ($rowNum + 1) . '"'
			. '>';
	}

	/**
	 * Method to generate an array of clients.
	 *
	 * @return  array of client objects.
	 */
	public function clients()
	{
		return array(
			HTMLHelper::_('select.option', 0, Text::_('JSITE')),
			HTMLHelper::_('select.option', 1, Text::_('JADMINISTRATOR'))
		);
	}

	/**
	 * Returns an array of published state filter options.
	 *
	 * @return  string  	The HTML code for the select tag.
	 *
	 * @since   1.6
	 */
	public function publishedOptions()
	{
		// Build the active state filter options.
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '1', 'JPUBLISHED');
		$options[] = HTMLHelper::_('select.option', '0', 'JUNPUBLISHED');
		$options[] = HTMLHelper::_('select.option', '-2', 'JTRASHED');
		$options[] = HTMLHelper::_('select.option', '*', 'JALL');

		return $options;
	}
}
