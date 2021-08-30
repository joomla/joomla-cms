<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<div class="accordion d-none d-sm-block" id="accordion1">
	<div class="accordion-heading pb-3">
		<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#batch" aria-expanded="false" aria-controls="batch">
			<?php echo Text::_('COM_REDIRECT_BATCH_UPDATE_WITH_NEW_URL'); ?>
		</button>
	</div>
	<div class="collapse card" id="batch">
		<div class="card-body">
			<fieldset>
				<div class="control-group">
					<div class="control-label">
						<label id="new_url-lbl" for="new_url">
							<?php echo Text::_('COM_REDIRECT_FIELD_NEW_URL_LABEL'); ?>
						</label>
					</div>
					<div class="controls">
						<input class="form-control" type="text" name="new_url" id="new_url" value="" size="50">
						<small id="new_url-desc" class="form-text">
							<?php echo Text::_('COM_REDIRECT_FIELD_NEW_URL_DESC'); ?>
						</small>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="comment-lbl" for="comment">
							<?php echo Text::_('COM_REDIRECT_FIELD_COMMENT_LABEL'); ?>
						</label>
					</div>
					<div class="controls">
						<input class="form-control" type="text" name="comment" id="comment" value="" size="50">
					</div>
				</div>
				<button class="btn btn-primary" type="button" onclick="this.form.task.value='links.duplicateUrls';this.form.submit();">
					<?php echo Text::_('COM_REDIRECT_BUTTON_UPDATE_LINKS'); ?>
				</button>
			</fieldset>
		</div>
	</div>
</div>

