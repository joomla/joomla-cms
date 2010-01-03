<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).'/text.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldCalendar extends JFormFieldText
{
   /**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Calendar';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$format = ($this->_element->attributes('format') ? $this->_element->attributes('format') : '%Y-%m-%d');
		$filter = $this->_element->attributes('filter');
		$time	= $this->_element->attributes('time');

		if ($this->value == 'now') {
			$this->value = strftime($format);
		}

		// Get some system objects.
		$config = JFactory::getConfig();
		$user	= JFactory::getUser();

		switch (strtoupper($filter))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if (intval($this->value)) {
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');
					$date->setOffset($config->getValue('config.offset'));

					// Transform the date string.
					$this->value = $date->toMySQL(true);
				}
				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if (intval($this->value)) {
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');
					$date->setOffset($user->getParam('timezone', $config->getValue('config.offset')));

					// Transform the date string.
					$this->value = $date->toMySQL(true);
				}
				break;
		}

		return JHtml::_('calendar', $this->value, $this->inputName, $this->inputId, $format);
	}
}