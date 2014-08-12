<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('calendar');

/**
 * Provides input for "Date of Birth" field
 *
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 * @since       3.3.3
 */
class JFormFieldDob extends JFormFieldCalendar
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5.5
	 */
	protected $type = 'Dob';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.3.3
	 */
	protected function getLabel()
	{
		$label = parent::getLabel();

		// Get the info text from the XML element, defaulting to empty.
		$text = $this->element['info'] ? (string) $this->element['info'] : '';
		$text = $this->translateLabel ? JText::_($text) : $text;

		if ($text)
		{
			// Closing the opening control-label div so we can add out info text on own line
			$info = '</div><div class="controls">' . $text . '</div></div>';

			// Creating new control-group for the actual field
			$info .= '<div class="control-group"><div class="control-label">';

			$label = $info . $label;
		}

		return $label;
	}
}
