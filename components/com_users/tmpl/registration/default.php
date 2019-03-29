<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

?>
<div class="com-users-registration registration">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>

	<form id="member-registration" action="<?php echo Route::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="com-users-registration__form form-validate" enctype="multipart/form-data">
		<?php // Iterate through the form fieldsets and display each one. ?>
		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
			<?php $fields = $this->form->getFieldset($fieldset->name); ?>
			<?php if (count($fields)) : ?>
				<fieldset>
					<?php // If the fieldset has a label set, display it as the legend. ?>
					<?php if (isset($fieldset->label)) : ?>
						<legend><?php echo Text::_($fieldset->label); ?></legend>
					<?php endif; ?>
					<?php // Iterate through the fields in the set and display them. ?>
					<?php foreach ($fields as $field) : ?>
						<?php // If the field is hidden, just display the input. ?>
						<?php if ($field->hidden) : ?>
							<?php echo $field->input; ?>
						<?php else : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
									<?php if (!$field->required && $field->type !== 'Spacer') : ?>
										<span class="optional"><?php echo Text::_('COM_USERS_OPTIONAL'); ?></span>
									<?php endif; ?>
								</div>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="com-users-registration__submit control-group">
			<div class="controls">
				<button type="submit" class="com-users-registration__register btn btn-primary validate">
					<?php echo Text::_('JREGISTER'); ?>
				</button>
				<a class="com-users-registration__cancel btn btn-danger" href="<?php echo Route::_(''); ?>">
					<?php echo Text::_('JCANCEL'); ?>
				</a>
				<input type="hidden" name="option" value="com_users">
				<input type="hidden" name="task" value="registration.register">
			</div>
		</div>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
<script type="text/javascript">
	$('input[type="password"]').eq(0).parent().parent().append('<div id="password-strength"> <div class="box box1"> <div class="bar-text"></div><div class="bar"></div></div><div class="box box2"> <div class="bar"></div></div><div class="box box3"> <div class="bar"></div></div><div class="box box4"> <div class="bar"></div></div></div>');
	var result = $("#password-strength");

	$('input[type="password"]').eq(0).keyup(function() {
		$(".bar-text").html(checkStrength($('input[type="password"]').val()));
	});

	function checkStrength(password) {
		//initial strength
		var strength = 0;

		if (password.length == 0) {
			result.removeClass();
			return "";
		}
		//if the password length is less than 4, return message, in line with existing validation.
		if (password.length < 4) {
			result.removeClass();
			result.addClass("invalid");
			return "Invalid";
		}

		//length is ok, lets continue.

		//if length is 8 characters or more, increase strength value
		if (password.length > 4) strength += 1;

		//if password contains both lower and uppercase characters, increase strength value
		if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;

		//if it has one special character, increase strength value
		if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;

		//if it has two special characters, increase strength value
		if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/))
		strength += 1;

		//now we have calculated strength value, we can return messages

		//if value is less than 2
		if (strength < 2) {
			result.removeClass();
			result.addClass("weak");
			return "Weak";
		} else if (strength == 2) {
			result.removeClass();
			result.addClass("medium");
			return "Medium";
		} else {
			result.removeClass();
			result.addClass("strong");
			return "Strong";
		}
	}
</script>
