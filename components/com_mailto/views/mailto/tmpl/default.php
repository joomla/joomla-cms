<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');

?>
<div id="mailto-window">
	<h2>
		<?php echo JText::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?>
	</h2>
	<div class="mailto-close">
		<a href="javascript: void window.close()" title="<?php echo JText::_('COM_MAILTO_CLOSE_WINDOW'); ?>">
			<span>
				<?php echo JText::_('COM_MAILTO_CLOSE_WINDOW'); ?>
			</span>
		</a>
	</div>
	<form action="<?php echo JRoute::_('index.php?option=com_mailto&task=send'); ?>" method="post" class="form-validate form-horizontal well">
		<fieldset>
			<?php foreach ($this->form->getFieldset('') as $field) : ?>
				<?php if (!$field->hidden) : ?>
					<?php echo $field->renderField(); ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary validate">
						<?php echo JText::_('COM_MAILTO_SEND'); ?>
					</button>
					<button type="button" class="button" onclick="window.close();return false;">
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
</div>
