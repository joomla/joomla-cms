<?php
/**
 * @version
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
abstract class JHtmlContact
{
	/**
	 * @param	int $value	The featured value
	 * @param	int $i
	 */

	function featured($value = 0, $i)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png',	'contact.featured',	'Contact_Toggle_Featured',	'Contact_Toggle_Featured'),
			1	=> array('tick.png',		'contact.unfeatured',	'Contact_Toggle_Featured',	'Contact_Toggle_Featured'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
				. JHtml::_('image.administrator', $state[0], '/templates/bluestork/admin/images/', null, '/templates/bluestork/admin/images/', JText::_($state[2])).'</a>';

		return $html;
	}


	/**
	 * Displays the publishing state legend for contacts
	 */
	function Legend()
	{
		?>
		<table cellspacing="0" cellpadding="4" border="0" align="center">
		<tr align="center">			<td>
			<td>
			<img src="templates/bluestork/admin/publish_y.png" width="16" height="16" border="0" alt="<?php echo JText::_('Pending'); ?>" />
			</td>
			<td>
			<?php echo JText::_('PUBLISHED_BUT_IS'); ?> <u><?php echo JText::_('Pending'); ?></u> |
			</td>
			<td>
			<img src="templates/bluestork/admin/publish_g.png" width="16" height="16" border="0" alt="<?php echo JText::_('Visible'); ?>" />
			</td>
			<td>
			<?php echo JText::_('PUBLISHED_AND_IS'); ?> <u><?php echo JText::_('Current'); ?></u> |
			</td>
			<td>
			<img src="templates/bluestork/admin/publish_r.png" width="16" height="16" border="0" alt="<?php echo JText::_('Finished'); ?>" />
			</td>
			<td>
			<?php echo JText::_('PUBLISHED_BUT_HAS'); ?> <u><?php echo JText::_('Expired'); ?></u> |
			</td>
			<td>
			<img src="templates/bluestork/admin/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_('Finished'); ?>" />
			</td>
			<td>
			<?php echo JText::_('NOT_PUBLISHED'); ?> |
			</td>
			<td>
			<img src="templates/bluestork/admin/disabled.png" width="16" height="16" border="0" alt="<?php echo JText::_('Archived'); ?>" />
			</td>
			<td>
			<?php echo JText::_('Archived'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="10" align="center">
			<?php echo JText::_('CLICK_ON_ICON_TO_TOGGLE_STATE'); ?>
			</td>
		</tr>
		</table>
		<?php
	}
}

