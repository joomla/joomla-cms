<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('calendar');

/**
 * Provides input for "Date of Birth" field
 *
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 * @since       3.3.7
 */
class JFormFieldDob extends JFormFieldCalendar
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.3.7
	 */
	protected $type = 'Dob';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.3.7
	 */
	protected function getLabel()
	{
		$label = parent::getLabel();

		// Get the info text from the XML element, defaulting to empty.
		$text  = $this->element['info'] ? (string) $this->element['info'] : '';
		$text  = $this->translateLabel ? JText::_($text) : $text;

		if ($text)
		{
			$app    = JFactory::getApplication();
			$layout = new JLayoutFile('plugins.user.profile.fields.dob');
			$view   = $app->input->getString('view', '');

			// Only display the tip when editing profile
			if ($app->isClient('administrator') || $view === 'profile' || $view === 'registration')
			{
				$layout = new JLayoutFile('plugins.user.profile.fields.dob');
				$info   = $layout->render(array('text' => $text));
				$label  = $info . $label;
			}
		}

		return $label;
	}
}
