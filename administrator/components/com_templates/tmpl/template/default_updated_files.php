<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$input = Factory::getApplication()->input;
?>

<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="updateForm" id="updateForm">
	<div class="row">
		<div class="col-md-12">
			<?php if(count($this->updatedList) !== 0) : ?>
				<table class="table">
					<thead>
						<tr>
							<th style="width:5%" class="nowrap text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</th>
							<th style="width:7%" class="nowrap">
								<?php echo Text::_('JSTATUS'); ?>
							</th>
							<th style="width:30%">
								<?php echo Text::_('COM_TEMPLATES_OVERRIDE_TEMPLATE_FILE'); ?>
							</th>
							<th>
								<?php echo Text::_('COM_TEMPLATES_OVERRIDE_CREATED_DATE'); ?>
							</th>
							<th>
								<?php echo Text::_('COM_TEMPLATES_OVERRIDE_MODIFIED_DATE'); ?>
							</th>
							<th>
								<?php echo Text::_('COM_TEMPLATES_OVERRIDE_ACTION'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->updatedList as $i => $value) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $value->hash_id, false, 'cid', 'cb', '', 'updateForm'); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('jgrid.published', $value->state, $i, 'template.', 1, 'cb', null, null, 'updateForm'); ?>
								</td>
								<td>
									<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $value->extension_id . '&file=' . $value->hash_id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>"><?php echo base64_decode($value->hash_id); ?></a>
								</td>
								<td>
									<?php echo $value->created_date; ?>
								</td>
								<td>
									<?php if ($value->modified_date === '0000-00-00 00:00:00') : ?>
										<span class="badge badge-warning"><?php echo Text::_('COM_TEMPLATES_OVERRIDE_CORE_REMOVED'); ?></span>
									<?php else : ?>
										<?php echo $value->modified_date; ?>
									<?php endif; ?>
								</td>
								<td>
									<span class="badge badge-info"><?php echo $value->action; ?></span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			<?php else : ?>
				<joomla-alert type="success" role="alert" class="joomla-alert--show">
					<span class="icon-info" aria-hidden="true"></span>
					<?php echo Text::_('COM_TEMPLATES_OVERRIDE_UPTODATE'); ?>
				</joomla-alert>
			<?php endif; ?>
		</div>
	</div>
</form>
