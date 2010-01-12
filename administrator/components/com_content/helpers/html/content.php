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
		$html	= JHtml::_('image.administrator', $state[0], '/templates/bluestork/images/admin/', null, '/templates/bluestork/admin/images/', JText::_($state[2]));
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

		<div class="center">
			<ul id="legend articles">
				<li><img src="templates/bluestork/images/admin/publish_y.png" width="16" height="16" border="0" alt="<?php echo JText::_('Pending'); ?>" /></li>
				<li><?php echo JText::_('PUBLISHED_BUT_IS'); ?> <u><?php echo JText::_('Pending'); ?></u></li>
				<li><img src="templates/bluestork/images/admin/publish_g.png" width="16" height="16" border="0" alt="<?php echo JText::_('Visible'); ?>" /></li>
				<li><?php echo JText::_('PUBLISHED_AND_IS'); ?> <u><?php echo JText::_('Current'); ?></u></li>
				<li><img src="templates/bluestork/images/admin/publish_r.png" width="16" height="16" border="0" alt="<?php echo JText::_('Finished'); ?>" /></li>
				<li><?php echo JText::_('PUBLISHED_BUT_HAS'); ?> <u><?php echo JText::_('Expired'); ?></u></li>
				<li><img src="templates/bluestork/images/admin/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_('Finished'); ?>" /></li>
				<li><?php echo JText::_('NOT_PUBLISHED'); ?></li>
				<li><img src="templates/bluestork/images/admin/disabled.png" width="16" height="16" border="0" alt="<?php echo JText::_('Archived'); ?>" /></li>
				<li><?php echo JText::_('Archived'); ?></li>
			</ul>
			<p class="center"><?php echo JText::_('CLICK_ON_ICON_TO_TOGGLE_STATE'); ?></p>
		</div>
		<?php
	}
}