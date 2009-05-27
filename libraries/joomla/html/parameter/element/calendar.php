<?php
/**
 * @version		$Id: calendar.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Renders a calendar element
 *
 * @package 	Joomla.Framework
 * @subpackage	Parameter
 * @since		1.5
 */
class JElementCalendar extends JElement
{
	/**
	* Element name
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Calendar';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		JHtml::_('behavior.calendar'); //load the calendar behavior

		$format	= ($node->attributes('format') ? $node->attributes('format') : '%Y-%m-%d');
		$class	= $node->attributes('class') ? $node->attributes('class') : 'inputbox';

		$id   = $control_name.$name;
		$name = $control_name.'['.$name.']';

		return JHtml::_('calendar', $value, $name, $id, $format, array('class' => $class));
	}
}
