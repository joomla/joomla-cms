<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');

$data	= $this->get('data');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.getElementById('mailtoForm');

		// do field validation
		if (form.mailto.value == '' || form.from.value == '')
		{
			alert('" . JText::_('COM_MAILTO_EMAIL_ERR_NOINFO') . "');
			return false;
		}
		form.submit();
	}
");

?>

<div id="mailto-window">
	<h2>
		<?php echo JText::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?>
	</h2>
	
	<?php if(version_compare(JVERSION, '3.8.8', 'ge')) { ?>
	<form action="<?php echo JRoute::_('index.php?option=com_mailto&task=send'); ?>" method="post" class="form-validate">
		<fieldset>
			<?php foreach ($this->form->getFieldset('') as $field) : ?>
				<?php if (!$field->hidden) : ?>
					<?php echo $field->renderField(); ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-success validate">
						<?php echo JText::_('COM_MAILTO_SEND'); ?>
					</button>
					<button type="button" class="btn btn-danger" onclick="window.close();return false;">
						<?php echo JText::_('COM_MAILTO_CANCEL'); ?>
					</button>
				</div>
			</div>
		</fieldset>
		<input type="hidden" name="layout" value="<?php echo htmlspecialchars($this->getLayout(), ENT_COMPAT, 'UTF-8'); ?>" />
		<input type="hidden" name="option" value="com_mailto" />
		<input type="hidden" name="task" value="send" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="link" value="<?php echo $this->link; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php } else {?>
		<form action="<?php echo JUri::base() ?>index.php" id="mailtoForm" method="post">

			<div class="control-group">
				<label for="mailto_field" class="control-label"><?php echo JText::_('COM_MAILTO_SENDER'); ?></label>
				<div class="controls">
					<input type="text" id="mailto_field" name="mailto" value="<?php echo $this->escape($data->mailto); ?>">
				</div>
			</div>

			<div class="control-group">
				<label for="sender_field" class="control-label"><?php echo JText::_('COM_MAILTO_EMAIL_TO'); ?></label>
				<div class="controls">
					<input type="text" id="sender_field" name="sender" value="<?php echo $this->escape($data->sender); ?>">
				</div>
			</div>

			<div class="control-group">
				<label for="from_field" class="control-label"><?php echo JText::_('COM_MAILTO_YOUR_EMAIL'); ?></label>
				<input type="text" id="from_field" name="from" value="<?php echo $this->escape($data->from); ?>">
			</div>

			<div class="control-group">
				<label for="subject_field" class="control-label"><?php echo JText::_('COM_MAILTO_SUBJECT'); ?></label>
				<input type="text" id="subject_field" name="subject" value="<?php echo $this->escape($data->subject); ?>">
			</div>

			<div class="control-group">
				<button class="btn btn-success" onclick="return Joomla.submitbutton('send');">
					<?php echo JText::_('COM_MAILTO_SEND'); ?>
				</button>
				<button class="btn btn-danger" onclick="window.close();return false;">
					<?php echo JText::_('COM_MAILTO_CANCEL'); ?>
				</button>
			</div>

			<input type="hidden" name="layout" value="<?php echo $this->getLayout();?>" />
			<input type="hidden" name="option" value="com_mailto" />
			<input type="hidden" name="task" value="send" />
			<input type="hidden" name="tmpl" value="component" />
			<input type="hidden" name="link" value="<?php echo $data->link; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php } ?>
</div>