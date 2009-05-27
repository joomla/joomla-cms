<?php
/**
 * @version		$Id: separator.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a button separator
 *
 * @package 	Joomla.Framework
 * @subpackage		HTML
 * @since		1.5
 */
class JButtonSeparator extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_name = 'Separator';

	public function render(&$definition)
	{
		// Initialize variables
		$class	= null;
		$style	= null;

		// Separator class name
		$class = (empty($definition[1])) ? 'spacer' : $definition[1];
		// Custom width
		$style = (empty($definition[2])) ? null : ' style="width:' .  intval($definition[2]) . 'px;"';

		return '<td class="' . $class . '"' . $style . ">\n</td>\n";
	}

	/**
	 * Empty implementation (not required)
	 */
	public function fetchButton()
	{
	}
}
