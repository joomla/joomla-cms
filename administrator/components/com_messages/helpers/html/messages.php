<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class JHtmlMessages extends JController
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	function state($value = 0, $i, $canChange)
	{
		// Array of image, task, title, action.
		$states	= array(
			-2	=> array('trash.png',		'messages.unpublish',	'JState_Trash',	'Messages_UnPublish_Item'),
			1	=> array('tick.png',		'messages.unpublish',	'Messages_Option_Read',		'Messages_UnPublish_Item'),
			0	=> array('publish_x.png',	'messages.publish',		'Messages_Option_unRead',	'Messages_Publish_Item')
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= JHtml::_('image.administrator', $state[0], '/templates/bluestork/images/admin/', null, '/templates/bluestork/images/admin/', JText::_($state[2]));
		if ($canChange) {
			$html = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
					.$html.'</a>';
		}

		return $html;
	}

}