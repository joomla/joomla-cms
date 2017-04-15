<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));

JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function ($){
		$("#jform_type").on("change", function (a, params) {

			var v = typeof(params) !== "object" ? $("#jform_type").val() : params.selected;

			var img_url = $("#image, #url");
			var custom  = $("#custom");

			switch (v) {
				case "0":
					// Image
					img_url.show();
					custom.hide();
					break;
				case "1":
					// Custom
					img_url.hide();
					custom.show();
					break;
			}
		}).trigger("change");
	});
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_banners&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="banner-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_BANNERS_BANNER_DETAILS')); ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('type'); ?>
				<div id="image">
					<?php echo $this->form->renderFieldset('image'); ?>
				</div>
				<div id="custom">
					<?php echo $this->form->renderField('custombannercode'); ?>
				</div>
				<?php
				echo $this->form->renderField('clickurl');
				echo $this->form->renderField('description');
				?>
			</div>
			<div class="col-md-3">
				<div class="card card-block card-light">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'otherparams', JText::_('COM_BANNERS_GROUP_LABEL_BANNER_DETAILS')); ?>
		<?php echo $this->form->renderFieldset('otherparams'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo $this->form->renderFieldset('metadata'); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo JHtml::_('form.token'); ?>
</form>
