<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$template = $app->getTemplate();

Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

// Load the tooltip behavior.
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

if ($this->fieldsets)
{
	HTMLHelper::_('bootstrap.framework');
}

// @TODO delete this when custom elements modal is merged
HTMLHelper::_('script', 'com_config/admin-application-default.min.js', ['version' => 'auto', 'relative' => true]);

$xml = $this->form->getXml();
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="component-form" method="post" class="form-validate" name="adminForm" autocomplete="off" data-cancel-task="config.cancel.component">
	<div class="row">

		<?php // Begin Sidebar ?>
		<div class="col-md-3" id="sidebar">
			<button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-toggle="collapse" data-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
				 <span class="fas fa-align-justify" aria-hidden="true"></span>
				 <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
			</button>
			<div class="sidebar-nav bg-light p-2 my-2">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<?php // End Sidebar ?>

		<div class="col-md-9" id="config">
			<?php if ($this->fieldsets): ?>

				<?php echo HTMLHelper::_('uitab.startTabSet', 'configTabs'); ?>

				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>

					<?php
					// Only show first level fieldsets as tabs
					if ($xml->xpath('//fieldset/fieldset[@name="' . $name . '"]')) :
						continue;
					endif;
					?>

					<?php $dataShowOn = ''; ?>
					<?php if (!empty($fieldSet->showon)) : ?>
						<?php HTMLHelper::_('script', 'system/showon.min.js', array('version' => 'auto', 'relative' => true)); ?>
						<?php $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
					<?php endif; ?>

					<?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>
					<?php $hasChildren = $xml->xpath('//fieldset[@name="' . $name . '"]/fieldset'); ?>

					<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', $name, Text::_($label)); ?>


					<fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-grid-form options-grid-form-full options-menu">
						<legend><?php echo Text::_($fieldSet->label); ?></legend>
						<div>
							<?php if (!empty($fieldSet->description)) : ?>
								<div class="tab-description alert alert-info">
									<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
									<?php echo Text::_($fieldSet->description); ?>
								</div>
							<?php endif; ?>

							<?php if (!$hasChildren) : ?>
								<?php echo $this->form->renderFieldset($name, $name === 'permissions' ? ['hiddenLabel' => true, 'class' => 'revert-controls'] : []); ?>
							<?php endif; ?>
						</div>
					</fieldset>

					<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php endforeach; ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<?php else: ?>
				<div class="alert alert-info">
					<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('COM_CONFIG_COMPONENT_NO_CONFIG_FIELDS_MESSAGE'); ?>
				</div>
			<?php endif; ?>
		</div>

		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>">
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>">
		<input type="hidden" name="return" value="<?php echo $this->return; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
