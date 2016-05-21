<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "filter.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
	
	jQuery(document).ready(function($) {
		$("#rightbtn").on("click", function() {
			if($(this).text() == "' . JText::_('COM_FINDER_FILTER_SHOW_ALL') . '") {
				$(".collapse:not(.in)").each(function (index) {
					$(this).collapse("toggle");
				});
				$(this).text("' . JText::_('COM_FINDER_FILTER_HIDE_ALL') . '");
			} else {
				$(this).text("' . JText::_('COM_FINDER_FILTER_SHOW_ALL') . '");
				$(".collapse.in").each(function (index) {
				$(this).collapse("toggle");
			});
		}
		return false;
		});

		$(".filter-node").change(function() {
			$(\'input[id="jform_map_count"]\').val(document.querySelectorAll(\'input[type="checkbox"]:checked\').length);
		});


	});
');

JFactory::getDocument()->addStyleDeclaration(
	"
	.accordion-inner .control-group .controls {
		margin-left: 10px;
	}
	.accordion-inner > .control-group {
		margin-bottom: 0;
	}
	"
);
?>

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=filter&layout=edit&filter_id=' . (int) $this->item->filter_id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_FINDER_EDIT_FILTER')); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php if ($this->total > 0) : ?>
					<div class="well">
						<?php echo $this->form->getControlGroup('map_count'); ?>
					</div>
					<button class="btn btn-default" type="button" class="jform-rightbtn" onclick="jQuery('.filter-node').each(function () { this.click(); });">
						<span class="icon-checkbox-partial"></span> <?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?></button>

					<button class="btn btn-default pull-right" type="button" id="rightbtn" ><?php echo JText::_('COM_FINDER_FILTER_SHOW_ALL'); ?></button>
					<hr>
				<?php endif; ?>

				<?php echo JHtml::_('filter.slider', array('selected_nodes' => $this->filter->data)); ?>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->get('return', '', 'cmd');?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
