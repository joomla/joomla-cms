<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
?>
<div class="registration<?php echo $this->pageclass_sfx; ?>">
	<div class="row justify-content-center">
		<div class="col-md-9 col-lg-6">
			<?php if ($this->params->get('show_page_heading')) : ?>
				<div class="page-header">
					<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
				</div>
			<?php endif; ?>

			<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
				<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
					<?php $fields = $this->form->getFieldset($fieldset->name); ?>
					<?php if (count($fields)) : ?>
						<fieldset>
							<?php if (isset($fieldset->label)) : ?>
								<legend><?php echo JText::_($fieldset->label); ?></legend>
							<?php endif; ?>
							<div class="row">
								<?php foreach ($fields as $field) : ?>
									<?php if ($field->hidden) : ?>
										<?php echo $field->input; ?>
									<?php else : ?>
										<?php $fieldName = $field->getAttribute('name'); ?>
										<?php if(($fieldName == 'password1') || ($fieldName == 'password2') || ($fieldName == 'email1') || ($fieldName == 'email2')) : ?>
											<div class="col-md-6">
										<?php else: ?>
											<div class="col-lg-12">
										<?php endif; ?>
										<div class="form-group">
											<?php echo $field->label; ?>
											<?php if (!$field->required && $field->type !== 'Spacer') : ?>
												<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
											<?php endif; ?>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>
			<?php endforeach; ?>
			<div>
				<button type="submit" class="btn btn-primary validate"><?php echo JText::_('JREGISTER'); ?></button>
				<a class="btn btn-secondary" href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
				<input type="hidden" name="option" value="com_users">
				<input type="hidden" name="task" value="registration.register">
			</div>
			<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
