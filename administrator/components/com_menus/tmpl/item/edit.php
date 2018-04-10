<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.core');
JHtml::_('behavior.tabstate');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

JText::script('ERROR');
JText::script('JGLOBAL_VALIDATION_FORM_FAILED');

JFactory::getDocument()->addScriptOptions('menu-item', ['itemId' => (int) $this->item->id]);
JHtml::_('script', 'com_menus/admin-item-edit.min.js', ['version' => 'auto', 'relative' => true]);

// Ajax for parent items
$script = "
jQuery(document).ready(function ($){
	// Menu type Login Form specific
	$('#item-form').on('submit', function() {
		if ($('#jform_params_login_redirect_url') && $('#jform_params_logout_redirect_url')) {
			// Login
			if ($('#jform_params_login_redirect_url').closest('.control-group').css('display') === 'block') {
				$('#jform_params_login_redirect_menuitem_id').val('');
			}
			if ($('#jform_params_login_redirect_menuitem_name').closest('.control-group').css('display') === 'block') {
				$('#jform_params_login_redirect_url').val('');

			}

			// Logout
			if ($('#jform_params_logout_redirect_url').closest('.control-group').css('display') === 'block') {
				$('#jform_params_logout_redirect_menuitem_id').val('');
			}
			if ($('#jform_params_logout_redirect_menuitem_id').closest('.control-group').css('display') === 'block') {
				$('#jform_params_logout_redirect_url').val('');
			}
		}
	});
});
";

$assoc = JLanguageAssociations::isEnabled();
$input = JFactory::getApplication()->input;

// In case of modal
$isModal  = $input->get('layout') == 'modal' ? true : false;
$layout   = $isModal ? 'modal' : 'edit';
$tmpl     = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
$clientId = $this->state->get('item.client_id', 0);
?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=item&client_id=' . $clientId . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>

		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_MENUS_ITEM_DETAILS')); ?>
		<div class="row">
			<div class="col-md-9">
				<?php
				echo $this->form->renderField('type');

				if ($this->item->type == 'alias')
				{
					echo $this->form->renderFieldset('aliasoptions');
				}

				if ($this->item->type == 'separator')
				{
					echo $this->form->renderField('text_separator', 'params');
				}

				echo $this->form->renderFieldset('request');

				if ($this->item->type == 'url')
				{
					$this->form->setFieldAttribute('link', 'readonly', 'false');
				}

				echo $this->form->renderField('link');

				echo $this->form->renderField('browserNav');
				echo $this->form->renderField('template_style_id');

				if (!$isModal && $this->item->type == 'container')
				{
					echo $this->loadTemplate('container');
				}
				?>
			</div>
			<div class="col-md-3">
				<div class="card card-light">
					<div class="card-body">
						<?php
						// Set main fields.
						$this->fields = array(
							'id',
							'client_id',
							'menutype',
							'parent_id',
							'menuordering',
							'published',
							'home',
							'access',
							'language',
							'note',
						);

						if ($this->item->type != 'component')
						{
							$this->fields = array_diff($this->fields, array('home'));
						}

						echo JLayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php
		$this->fieldsets = array();
		$this->ignore_fieldsets = array('aliasoptions', 'request', 'item_associations');
		echo JLayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if (!$isModal && $assoc && $this->state->get('item.client_id') != 1) : ?>
			<?php if ($this->item->type !== 'alias' && $this->item->type !== 'url'
				&& $this->item->type !== 'separator' && $this->item->type !== 'heading') : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
				<?php echo $this->loadTemplate('associations'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php elseif ($isModal && $assoc && $this->state->get('item.client_id') != 1) : ?>
			<div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
		<?php endif; ?>

		<?php if (!empty($this->modules)) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'modules', JText::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT')); ?>
			<?php echo $this->loadTemplate('modules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" id="fieldtype" name="fieldtype" value="">
</form>
