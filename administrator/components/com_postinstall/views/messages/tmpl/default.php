<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php if (empty($this->items)): ?>
<div class="hero-unit">
	<h2><?php echo JText::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE') ?></h2>
	<p><?php echo JText::_('COM_POSTINSTALL_LBL_NOMESSAGES_DESC') ?></p>
	<a href="index.php?option=com_postinstall&view=messages&task=reset&eid=<?php echo $this->eid; ?>&<?php echo $this->token ?>=1" class="btn btn-warning btn-large">
		<span class="icon icon-eye-open"></span>
		<?php echo JText::_('COM_POSTINSTALL_BTN_RESET') ?>
	</a>
</div>
<?php else: ?>
<?php if ($this->eid == 700): ?>
<div class="row-fluid">
	<div class="span8">
<?php endif; ?>
	<h2><?php echo JText::_('COM_POSTINSTALL_LBL_MESSAGES') ?></h2>
	<?php foreach($this->items as $item): ?>
	<fieldset>
		<legend><?php echo JText::_($item->title_key) ?></legend>
		<p class="small">
			<?php echo JText::sprintf('COM_POSTINSTALL_LBL_SINCEVERSION', $item->version_introduced) ?>
		</p>
		<p><?php echo JText::_($item->description_key) ?></p>

		<div>
			<?php if ($item->type !== 'message'): ?>
			<a href="index.php?option=com_postinstall&view=messages&task=action&id=<?php echo $item->postinstall_message_id ?>&<?php echo $this->token ?>=1" class="btn btn-primary">
				<?php echo JText::_($item->action_key) ?>
			</a>
			<?php endif; ?>
			<?php if (JFactory::getUser()->authorise('core.edit.state', 'com_postinstall')) : ?>
			<a href="index.php?option=com_postinstall&view=message&task=unpublish&id=<?php echo $item->postinstall_message_id ?>&<?php echo $this->token ?>=1" class="btn btn-inverse btn-small">
				<?php echo JText::_('COM_POSTINSTALL_BTN_HIDE') ?>
			</a>
			<?php endif; ?>
		</div>
	</fieldset>
	<?php endforeach; ?>
<?php if ($this->eid == 700): ?>
	</div>
	<div class="span4">
		<h2><?php echo JText::_('COM_POSTINSTALL_LBL_RELEASENEWS') ?></h2>
		<iframe width="100%" height="1000" src="http://www.joomla.org/announcements/release-news">
		</iframe>
	</div>
</div>
<?php endif; ?>
<?php endif; ?>
