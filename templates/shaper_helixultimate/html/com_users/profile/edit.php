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


// Load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

?>
<div class="profile-edit<?php echo $this->pageclass_sfx; ?>">
	<div class="row justify-content-center">
		<div class="col-md-10 col-lg-7">
			<?php if ($this->params->get('show_page_heading')) : ?>
				<div class="page-header">
					<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
				</div>
			<?php endif; ?>

			<script type="text/javascript">
			Joomla.twoFactorMethodChange = function(e)
			{
				var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

				jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el) {
					if (el.id != selectedPane)
					{
						jQuery('#' + el.id).hide(0);
					}
					else
					{
						jQuery('#' + el.id).show(0);
					}
				});
			}
			</script>

			<form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
				<?php // Iterate through the form fieldsets and display each one. ?>
				<?php foreach ($this->form->getFieldsets() as $group => $fieldset) : ?>
					<?php $fields = $this->form->getFieldset($group); ?>
					<?php if (count($fields)) : ?>
						<fieldset>
							<?php if (isset($fieldset->label)) : ?>
								<legend>
									<?php echo JText::_($fieldset->label); ?>
								</legend>
							<?php endif; ?>
							<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
								<?php echo '<p>' . $this->escape(JText::_($fieldset->description)) . '</p>'; ?>
							<?php endif; ?>
							<?php // Iterate through the fields in the set and display them. ?>
							<div class="row mb-3">
								<?php foreach ($fields as $field) : ?>
									<?php // If the field is hidden, just display the input. ?>
									<?php if ($field->hidden) : ?>
										<?php echo $field->input; ?>
									<?php else : ?>
										<?php if(($field->fieldname == 'name') || ($field->fieldname == 'username')) : ?>
											<div class="col-md-12">
											<?php else: ?>
												<div class="col-md-6">
												<?php endif; ?>
												<div class="form-group">
													<?php echo $field->label; ?>
													<?php if ($field->fieldname === 'password1') : ?>
														<input type="password" style="display:none">
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

					<?php if (count($this->twofactormethods) > 1) : ?>
						<fieldset>
							<legend><?php echo JText::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH'); ?></legend>

							<div class="form-group">
								<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
								title="<?php echo '<strong>' . JText::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') . '</strong><br>' . JText::_('COM_USERS_PROFILE_TWOFACTOR_DESC'); ?>">
								<?php echo JText::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
							</label>
							<?php echo JHtml::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
						</div>
						<div id="com_users_twofactor_forms_container">
							<?php foreach ($this->twofactorform as $form) : ?>
								<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
								<div id="com_users_twofactor_<?php echo $form['method']; ?>" style="<?php echo $style; ?>">
									<?php echo $form['form']; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</fieldset>

					<fieldset>
						<legend>
							<?php echo JText::_('COM_USERS_PROFILE_OTEPS'); ?>
						</legend>
						<div class="alert alert-info">
							<?php echo JText::_('COM_USERS_PROFILE_OTEPS_DESC'); ?>
						</div>
						<?php if (empty($this->otpConfig->otep)) : ?>
							<div class="alert alert-warning">
								<?php echo JText::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC'); ?>
							</div>
						<?php else : ?>
							<?php foreach ($this->otpConfig->otep as $otep) : ?>
								<span class="col-md-3">
									<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
								</span>
							<?php endforeach; ?>
							<div class="clearfix"></div>
						<?php endif; ?>
					</fieldset>
				<?php endif; ?>

				<div class="form-group">
					<button type="submit" class="btn btn-primary validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
					<a class="btn btn-secondary" href="<?php echo JRoute::_('index.php?option=com_users&view=profile'); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
					<input type="hidden" name="option" value="com_users">
					<input type="hidden" name="task" value="profile.save">
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
