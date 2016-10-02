<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="accordion hidden-phone" id="accordion1">
	<div class="accordion-group">
		<div class="accordion-heading">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#batch">
				<?php echo JText::_('COM_REDIRECT_FIELD_NEW_URL_LABEL');?>
			</a>
		</div>
		<div id="batch" class="accordion-body collapse">
			<div class="accordion-inner">
				<fieldset class="batch form-inline">
					<div class="control-group">
						<label for="new_url" class="control-label"><?php echo JText::_('COM_REDIRECT_FIELD_NEW_URL_LABEL'); ?></label>
						<div class="controls">
							<input type="text" name="new_url" id="new_url" value="" size="50" title="<?php echo JText::_('COM_REDIRECT_FIELD_NEW_URL_DESC'); ?>" />
						</div>
					</div>
					<div class="control-group">
						<label for="comment" class="control-label"><?php echo JText::_('COM_REDIRECT_FIELD_COMMENT_LABEL'); ?></label>
						<div class="controls">
							<input type="text" name="comment" id="comment" value="" size="50" title="<?php echo JText::_('COM_REDIRECT_FIELD_COMMENT_DESC'); ?>" />
						</div>
					</div>
					<button class="btn btn-primary" type="button" onclick="this.form.task.value='links.duplicateUrls';this.form.submit();"><?php echo JText::_('COM_REDIRECT_BUTTON_UPDATE_LINKS'); ?></button>
				</fieldset>
			</div>
		</div>
	</div>
</div>
