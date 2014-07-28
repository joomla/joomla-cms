<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Generic field header, with text input (search) filter
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderFielddate extends FOFFormHeaderField
{
	/**
	 * Get the filter field
	 *
	 * @return  string  The HTML
	 */
	protected function getFilter()
	{
		// Initialize some field attributes.
		$format		 = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
		$attributes  = array();

		if ($this->element['size'])
		{
			$attributes['size'] = (int) $this->element['size'];
		}

		if ($this->element['maxlength'])
		{
			$attributes['maxlength'] = (int) $this->element['maxlength'];
		}

		if ($this->element['filterclass'])
		{
			$attributes['class'] = (string) $this->element['filterclass'];
		}

		if ((string) $this->element['readonly'] == 'true')
		{
			$attributes['readonly'] = 'readonly';
		}

		if ((string) $this->element['disabled'] == 'true')
		{
			$attributes['disabled'] = 'disabled';
		}

		if ($this->element['onchange'])
		{
			$attributes['onchange'] = (string) $this->element['onchange'];
		}
		else
		{
			$onchange = 'document.adminForm.submit()';
		}

		if ((string) $this->element['placeholder'])
		{
			$attributes['placeholder'] = JText::_((string) $this->element['placeholder']);
		}

		$name = $this->element['searchfieldname'] ? $this->element['searchfieldname'] : $this->name;

		if ($this->element['searchfieldname'])
		{
			$model       = $this->form->getModel();
			$searchvalue = $model->getState((string) $this->element['searchfieldname']);
		}
		else
		{
			$searchvalue = $this->value;
		}

		// Get some system objects.
		$config = FOFPlatform::getInstance()->getConfig();
		$user   = JFactory::getUser();

		// If a known filter is given use it.
		switch (strtoupper((string) $this->element['filter']))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ((int) $this->value)
				{
					// Get a date object based on the correct timezone.
					$date = FOFPlatform::getInstance()->getDate($searchvalue, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$searchvalue = $date->format('Y-m-d H:i:s', true, false);
				}
				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ((int) $searchvalue)
				{
					// Get a date object based on the correct timezone.
					$date = FOFPlatform::getInstance()->getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$searchvalue = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
		}

		return JHtml::_('calendar', $searchvalue, $name, $name, $format, $attributes);
	}

	/**
	 * Get the buttons HTML code
	 *
	 * @return  string  The HTML
	 */
	protected function getButtons()
	{
		return '';
	}
}
