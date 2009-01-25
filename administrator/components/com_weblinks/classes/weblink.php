<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package 	Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class JHtmlWeblink
{
	function state(&$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $imgR = 'report.png', $prefix='')
	{
		// State cannot be set to "Reported" here
		$alt 	= $row->state == 1 ? JText::_('Published') : ($row->state == -1 ? JText::_('Reported') : JText::_('Unpublished'));
		$img 	= JHtml::_('image.administrator', $row->state == 1 ? $imgY : ($row->state == -1 ? $imgR : $imgX), null, null, null, $alt);
		$task 	= $row->state == 1 ? 'unpublish' : 'publish';
		$action = $row->state == 1 ? JText::_('Unpublish Item') : JText::_('Publish item');

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
				'list.attr' => 'class="inputbox" size="1" onchange="submitform();"',
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
