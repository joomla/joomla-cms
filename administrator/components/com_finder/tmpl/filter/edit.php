<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.core');
JHtml::_('behavior.tabstate');

Text::script('COM_FINDER_FILTER_SHOW_ALL', true);
Text::script('COM_FINDER_FILTER_HIDE_ALL', true);

JHtml::_('script', 'com_finder/finder-edit.min.js', array('version' => 'auto', 'relative' => true));
?>

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=filter&layout=edit&filter_id=' . (int) $this->item->filter_id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_FINDER_EDIT_FILTER')); ?>
	<div class="row">
		<div class="col-md-9">
			<?php if ($this->total > 0) : ?>
				<div class="well">
					<?php echo $this->form->renderField('map_count'); ?>
				</div>
				<button class="btn btn-secondary filter-toggle-all" type="button">
					<span class="fa fa-square" aria-hidden="true"></span> <?php echo Text::_('JGLOBAL_SELECTION_INVERT'); ?></button>

				<button class="btn btn-secondary float-right" type="button" id="expandAccordion"><?php echo Text::_('COM_FINDER_FILTER_SHOW_ALL'); ?></button>
				<hr>
			<?php endif; ?>

			<?php echo JHtml::_('filter.slider', array('selected_nodes' => $this->filter->data)); ?>
		</div>
		<div class="col-md-3">
			<div class="card card-light">
				<div class="card-body">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
	<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->get('return', '', 'cmd'); ?>">
	<?php echo JHtml::_('form.token'); ?>
</form>
