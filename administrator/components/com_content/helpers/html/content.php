<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
abstract class JHtmlContent
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png',	'articles.featured',	'Content_Unfeatured',	'Content_Toggle_To_Feature'),
			1	=> array('tick.png',		'articles.unfeatured',	'Content_Featured',		'Content_Toggle_To_Unfeature'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= JHtml::_('image.administrator', $state[0], '/images/', null, '/images/', JText::_($state[2]));
		if ($canChange) {
			$html	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
					. $html.'</a>';
		}

		return $html;
	}

	/**
	 * Displays the publishing state legend for articles
	 */
	function Legend()
	{
		?>
		<table class="center">
		<tr>
			<td>
			<img src="images/publish_y.png" width="16" height="16" border="0" alt="<?php echo JText::_('Pending'); ?>" />
			</td>
			<td>
			<?php echo JText::_('Published, but is'); ?> <u><?php echo JText::_('Pending'); ?></u> |
			</td>
			<td>
			<img src="images/publish_g.png" width="16" height="16" border="0" alt="<?php echo JText::_('Visible'); ?>" />
			</td>
			<td>
			<?php echo JText::_('Published and is'); ?> <u><?php echo JText::_('Current'); ?></u> |
			</td>
			<td>
			<img src="images/publish_r.png" width="16" height="16" border="0" alt="<?php echo JText::_('Finished'); ?>" />
			</td>
			<td>
			<?php echo JText::_('Published, but has'); ?> <u><?php echo JText::_('Expired'); ?></u> |
			</td>
			<td>
			<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_('Finished'); ?>" />
			</td>
			<td>
			<?php echo JText::_('Not Published'); ?> |
			</td>
			<td>
			<img src="images/disabled.png" width="16" height="16" border="0" alt="<?php echo JText::_('Archived'); ?>" />
			</td>
			<td>
			<?php echo JText::_('Archived'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="10" class="center">
			<?php echo JText::_('Click on icon to toggle state.'); ?>
			</td>
		</tr>
		</table>
		<?php
	}
}