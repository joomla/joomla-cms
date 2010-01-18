<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'comment.cancel' || document.formvalidator.isValid(document.id('comment-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php echo JRoute::_('index.php?option=com_newsfeeds'); ?>" method="post" name="adminForm" id="newsfeed-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend>
				<?php echo JText::_('COMMENTS_COMMENT'); ?>: <?php echo $this->item->id; ?>
			</legend>

			<div class="comment-referrer">
				<span><?php echo JText::_('COMMENTS_PAGE'); ?>:</span>
				<a href="<?php echo $this->getContentRoute($this->thread->page_route); ?>" target="_blank">
					<?php echo $this->escape($this->thread->page_title); ?></a>
			</div>

			<?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?>

			<?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?>

			<?php echo $this->form->getLabel('address'); ?>
			<?php echo $this->form->getInput('address'); ?>

			<?php echo $this->form->getLabel('email'); ?>
			<?php echo $this->form->getInput('email'); ?>

			<?php echo $this->form->getLabel('url'); ?>
			<?php echo $this->form->getInput('url'); ?>

			<?php echo $this->form->getLabel('created_date'); ?>
			<?php echo $this->form->getInput('created_date'); ?>

			<?php echo $this->form->getLabel('subject'); ?>
			<?php echo $this->form->getInput('subject'); ?>

			<?php echo $this->form->getLabel('body'); ?>
			<?php echo $this->form->getInput('body'); ?>
		</fieldset>
	</div>

	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
