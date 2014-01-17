<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
?>
<div class="unotes">
	<h1><?php echo JText::sprintf('COM_USERS_NOTES_FOR_USER', $this->user->name, $this->user->id); ?></h1>
<?php if (empty($this->items)) : ?>
	<?php echo JText::_('COM_USERS_NO_NOTES'); ?>
<?php else : ?>
	<ol class="alternating">
	<?php foreach ($this->items as $item) : ?>
		<li>
			<div class="fltlft utitle">
				<?php if ($item->subject) : ?>
					<h4><?php echo JText::sprintf('COM_USERS_NOTE_N_SUBJECT', (int) $item->id, $this->escape($item->subject)); ?></h4>
				<?php else : ?>
					<h4><?php echo JText::sprintf('COM_USERS_NOTE_N_SUBJECT', (int) $item->id, JText::_('COM_USERS_EMPTY_SUBJECT')); ?></h4>
				<?php endif; ?>
			</div>

			<div class="fltlft utitle">
				<?php echo JHtml::date($item->created_time, 'D d M Y H:i'); ?>
			</div>

			<?php $category_image = $item->cparams->get('image'); ?>

			<?php if ($item->catid && isset($category_image)) : ?>
			<div class="fltlft utitle">
				<?php echo JHtml::_('users.image', $category_image); ?>
			</div>

			<div class="fltlft utitle">
				<em><?php echo $this->escape($item->category_title); ?></em>
			</div>
			<?php endif; ?>

			<div class="clr"></div>
			<div class="ubody">
				<?php echo $item->body; ?>
			</div>
		</li>
	<?php endforeach; ?>
	</ol>
<?php endif; ?>
</div>
