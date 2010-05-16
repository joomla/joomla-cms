<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
			0	=> array('disabled.png',	'articles.featured',	'COM_CONTENT_UNFEATURED',	'COM_CONTENT_TOGGLE_TO_FEATURE'),
			1	=> array('featured.png',		'articles.unfeatured',	'COM_CONTENT_FEATURED',		'COM_CONTENT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= JHTML::_('image','admin/'.$state[0], JText::_($state[2]), NULL, true);
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
				<li><?php echo JHTML::_('image','admin/publish_y.png', JText::_('Pending'), array('width' => 16, 'height' => 16, 'border' => 0), true)?></li>
				<li><?php echo JText::_('PUBLISHED_BUT_IS'); ?> <u><?php echo JText::_('Pending'); ?></u></li>
				<li><?php echo JHTML::_('image','admin/publish_g.png', JText::_('Visible'), array('width' => 16, 'height' => 16, 'border' => 0), true)?></li>
				<li><?php echo JText::_('PUBLISHED_AND_IS'); ?> <u><?php echo JText::_('Current'); ?></u></li>
				<li><?php echo JHTML::_('image','admin/publish_r.png', JText::_('Finished'), array('width' => 16, 'height' => 16, 'border' => 0), true)?></li>
				<li><?php echo JText::_('PUBLISHED_BUT_HAS'); ?> <u><?php echo JText::_('Expired'); ?></u></li>
				<li><?php echo JHTML::_('image','admin/publish_x.png', JText::_('Finished'), array('width' => 16, 'height' => 16, 'border' => 0), true)?></li>
				<li><?php echo JText::_('NOT_PUBLISHED'); ?></li>
				<li><?php echo JHTML::_('image','admin/disabled.png', JText::_('JARCHIVED'), array('width' => 16, 'height' => 16, 'border' => 0), true)?></li>
				<li><?php echo JText::_('JARCHIVED'); ?></li>
			</ul>
			<p class="center"><?php echo JText::_('JGLOBAL_CLICK_TO_TOGGLE_STATE'); ?></p>
		</div>
		<?php
	}
}