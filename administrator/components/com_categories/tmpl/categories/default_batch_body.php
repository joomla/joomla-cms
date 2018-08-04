<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$options = array(
	HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
	HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
);
$published = $this->state->get('filter.published');
$extension = $this->escape($this->state->get('filter.extension'));

HTMLHelper::_('formbehavior.chosen', '.chzn-custom-value');
?>

<div class="container-fluid">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.language'); ?>
			</div>
		</div>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.access'); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if ($published >= 0) : ?>
			<div class="col-md-6">
				<div class="control-group">
					<label id="batch-choose-action-lbl" for="batch-category-id" class="control-label">
						<?php echo Text::_('JLIB_HTML_BATCH_MENU_LABEL'); ?>
					</label>
					<div id="batch-choose-action" class="combo controls">
						<select class="chzn-custom-value" name="batch[category_id]" id="batch-category-id">
							<option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
							<?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.categories', $extension, array('filter.published' => $published))); ?>
						</select>
					</div>
				</div>
			<?php endif; ?>
			<div class="control-group col-md-6">
				<div class="controls">
					<?php echo HTMLHelper::_('batch.tag'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<div class="control-group">
				<label id="flip-ordering-id-lbl" for="flip-ordering-id" class="control-label">
					<?php echo Text::_('JLIB_HTML_BATCH_FLIPORDERING_LABEL'); ?>
				</label>
				<?php echo HTMLHelper::_('select.booleanlist', 'batch[flip_ordering]', array(), 0, 'JYES', 'JNO', 'flip-ordering-id'); ?>
			</div>
		</div>
	</div>
</div>

