<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Renders a password element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementPassword extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Password';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$size = ($node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '');
		$class = ($node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"');

		return '<input type="password" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' '.$size.' />';
	}
}
