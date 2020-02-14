<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');

$data = $this->get('data');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.getElementById('mailtoForm');

		// do field validation
		if (form.mailto.value == '' || form.from.value == '')
		{
			alert('" . JText::_('COM_MAILTO_EMAIL_ERR_NOINFO', true) . "');
			return false;
		}
		form.submit();
	}
");
?>

<div id="mailto-window" class="p-2">
	<a href="javascript: void window.close()" title="<?php echo JText::_('COM_MAILTO_CLOSE_WINDOW'); ?>" class="close" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</a>
	
	<h4 class="mt-0"><?php echo JText::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?></h4>

	<form action="<?php echo JUri::base() ?>index.php" id="mailtoForm" method="post">
		<div class="form-group">
			<label for="mailto_field">
				<?php echo JText::_('COM_MAILTO_EMAIL_TO'); ?>
			</label>
			<input type="text" id="mailto_field" name="mailto" class="form-control" value="<?php echo $this->escape($data->mailto); ?>">
		</div>
		<div class="form-group">
			<label for="sender_field">
				<?php echo JText::_('COM_MAILTO_SENDER'); ?>
			</label>
			<input type="text" id="sender_field" name="sender" class="form-control" value="<?php echo $this->escape($data->sender); ?>">
		</div>
		<div class="form-group">
			<label for="from_field">
				<?php echo JText::_('COM_MAILTO_YOUR_EMAIL'); ?>
			</label>
			<input type="text" id="from_field" name="from" class="form-control" value="<?php echo $this->escape($data->from); ?>">
		</div>
		<div class="form-group">
			<label for="subject_field">
                <?php echo JText::_('COM_MAILTO_SUBJECT'); ?>
            </label>
			<input type="text" id="subject_field" name="subject" class="form-control" value="<?php echo $this->escape($data->subject); ?>">
		</div>
		<div class="form-group">
			<button class="btn btn-secondary" onclick="window.close();return false;">
				<?php echo JText::_('COM_MAILTO_CANCEL'); ?>
			</button>
			<button class="btn btn-success" onclick="return Joomla.submitbutton('send');">
				<?php echo JText::_('COM_MAILTO_SEND'); ?>
			</button>
		</div>

		<input type="hidden" name="layout" value="<?php echo htmlspecialchars($this->getLayout(), ENT_COMPAT, 'UTF-8'); ?>">
		<input type="hidden" name="option" value="com_mailto">
		<input type="hidden" name="task" value="send">
		<input type="hidden" name="tmpl" value="component">
		<input type="hidden" name="link" value="<?php echo $data->link; ?>">
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
