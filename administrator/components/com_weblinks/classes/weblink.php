<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */

class JHtmlWeblink
{
	function state( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $imgR = 'report.png', $prefix='' )
	{
		// State cannot be set to "Reported" here
		$alt 	= $row->state == 1 ? JText::_( 'Published' ) : ($row->state == -1 ? JText::_( 'Reported' ) : JText::_( 'Unpublished' ));
		$img 	= JHtml::_('image.administrator', $row->state == 1 ? $imgY : ($row->state == -1 ? $imgR : $imgX), null, null, null, $alt);
		$task 	= $row->state == 1 ? 'unpublish' : 'publish';
		$action = $row->state == 1 ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $prefix.$task .'\')" title="'. $action .'">'
		. $img .'</a>'
		;

		return $href;
	}

	function statefilter($filter_state = '')
	{
		$state = array(
			'' => '- ' . JText::_('Select State') . ' -',
			'P' => JText::_('Published'),
			'U' => JText::_('Unpublished'),
			'R' => JText::_('Reported')
		);
		return JHtml::_(
			'select.genericlist',
			$state,
			'filter_state',
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="submitform( );"',
				'list.select' => $filter_state,
				'option.key' => null
			)
		);
	}

	/**
	* Select list of weblink states
	*/
	function statelist($name, $active = null, $javascript = null)
	{
		$state = array(
			1 => JText::_('Published'),
			0 => JText::_('Unpublished'),
			-1 => JText::_('Reported')
		);

		return JHtml::_(
			'select.genericlist',
			$state,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="1"'. $javascript,
				'list.select' => $active,
				'option.key' => null
			)
		);
	}
}
