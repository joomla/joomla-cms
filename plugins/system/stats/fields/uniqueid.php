<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Unique ID Field class for the Stats Plugin.
 *
 * @since  3.5
 */
class StatsFormFieldUniqueid extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $type = 'Uniqueid';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   3.5
	 */
	protected function getInput()
	{
		$onclick = ' onclick="document.getElementById(\'' . $this->id . '\').value=\'\';Joomla.submitbutton(\'plugin.apply\');"';

		return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" /> <a class="btn" ' . $onclick . '>'
			. '<span class="icon-refresh"></span> ' . JText::_('PLG_SYSTEM_STATS_RESET_UNIQUE_ID') . '</a>';
	}
}
