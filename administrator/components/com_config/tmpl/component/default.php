<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$app = Factory::getApplication();
$template = $app->getTemplate();

Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

// Load the tooltip behavior.
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');

// @TODO delete this when custom elements modal is merged
HTMLHelper::_('script', 'com_config/admin-application-default.min.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="component-form" method="post" class="form-validate" name="adminForm" autocomplete="off" data-cancel-task="config.cancel.component">
	<div class="row">

		<?php // Begin Sidebar ?>
		<div class="col-md-2" id="sidebar">
            <button class="navbar-toggler options-menu d-md-none d-lg-none d-xl-non" type="button" data-toggle="collapse" data-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="Toggle navigation">
                 <span class="burger-toggler-icon">
                     <?php echo Text::_('TPL_ATUM_TOGGLE_SIDEBAR'); ?>
                  </span>
            </button>
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<?php // End Sidebar ?>

		<div class="col-md-10" id="config">

			<?php if ($this->fieldsets): ?>
			<ul class="nav nav-tabs" id="configTabs">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php $dataShowOn = ''; ?>
					<?php if (!empty($fieldSet->showon)) : ?>
						<?php HTMLHelper::_('script', 'system/showon.min.js', array('version' => 'auto', 'relative' => true)); ?>
						<?php $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
					<?php endif; ?>
					<?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>
					<li class="nav-item"<?php echo $dataShowOn; ?>><a class="nav-link" data-toggle="tab" href="#<?php echo $name; ?>"><?php echo Text::_($label); ?></a></li>
				<?php endforeach; ?>
			</ul>

			<div class="tab-content" id="configContent">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php echo $this->form->renderFieldset($name, $name === 'permissions' ? ['hiddenLabel' => true, 'class' => 'revert-controls'] : []); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php else: ?>
				<div class="alert alert-info">
					<span class="icon-info" aria-hidden="true"></span>
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
