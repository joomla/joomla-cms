<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');
jimport('joomla.form.field');

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
		$format = $this->_element->attributes('format');
		$time	= $this->_element->attributes('time');

		if ($this->value == 'now') {
			$this->value = strftime($format);
		}

		return JHtml::_('calendar', $this->value, $this->inputName, $this->inputId, $format);
	}
}