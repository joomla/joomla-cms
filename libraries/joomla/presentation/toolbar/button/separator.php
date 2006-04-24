<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a button separator
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Presentation
 * @since		1.5
 */
class JButton_Separator extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Separator';

	function render( &$definition )
	{
		/*
		 * Initialize variables
		 */
		$html	= null;
		$class	= null;
		$style	= null;

		// Separator class name
		if (empty($definition[1]))
		{
			$class = "spacer";
		}

		// Custom width
		if (!empty($definition[2]))
		{
			$style = " style=\"width: $definition[1] px;\"";
		}

		$html	 = "<td class=\"$class\"$style>\n";
		$html	.= "</td>\n";

		return $html;
	}
}
?>