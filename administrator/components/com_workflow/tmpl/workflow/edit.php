<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '.advancedSelect', null, array('disable_search_threshold' => 0 ));

$app = JFactory::getApplication();
$input = $app->input;

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
$this->form->setFieldAttribute('category_id', 'extension', 'com_content');
?>

<form action="<?php echo JRoute::_('index.php?option=com_workflow&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="workflow-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_WORKFLOW_BASIC_TAB') : JText::_('COM_WORKFLOW_EDIT_TAB')); ?>
		<div class="row">
			<div class="col-md-9">
				<div class="row">
					<div class="col-md-6">
						<?php echo $this->form->renderField('description'); ?>
						<?php echo $this->form->renderField('category_id'); ?>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card card-block card-light">
					<?php echo $this->form->renderField('default'); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<input type="hidden" name="task" value="item.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<script>
	jQuery('#jform_user_switch').find('input[type="radio"]').change(function()
	{
		var $email = jQuery('#jform_email');
		var $user = jQuery('#jform_user_id');
		if(parseInt(jQuery('input[name="jform[user_switch]"]:checked').val()))
		{
			$email.removeAttr("required").removeAttr("aria-required").removeAttr("aria-invalid");
			$user.attr({'required': true, 'aria-required': true});
			if($user.val() !== '')
			{
				return;
			}
		}
		else
		{
			$email.attr({'required': true, 'aria-required': true});
			$user.removeAttr("required").removeAttr("aria-required").removeAttr("aria-invalid");
			if($email.val() !== '')
			{
				return;
			}
		}
		$user.val('');
		jQuery('#jform_user_id_id').val(0);
		$email.val('');
	});
	jQuery(document).ready(function()
	{
		if(jQuery('#jform_user_id').val() !== '')
		{
			jQuery('#jform_user_switch1').click();
		}
	});
	function jSelectUser(element)
	{
		jQuery('#jform_user_id').val(jQuery(element).data('user-name'));
		jQuery('#jform_user_id_id').val(jQuery(element).data('user-value'));
		jQuery('.modal').modal('hide');
		jQuery('.modal-body').empty();
		jQuery('body').removeClass('modal-open');
	}
</script>
