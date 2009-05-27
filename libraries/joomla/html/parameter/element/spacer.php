<?php
/**
 * @version		$Id: spacer.php 11371 2008-12-30 01:31:50Z ian $
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a spacer element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementSpacer extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Spacer';

	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return '&nbsp;';
	}

	public function fetchElement($name, $value, &$node, $control_name)
	{
		if ($value) {
			return JText::_($value);
		} else {
			return '<hr />';
		}
	}
}
