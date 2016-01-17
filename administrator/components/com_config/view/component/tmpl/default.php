<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration(
	'
	Joomla.submitbutton = function(task)
	{
		if (task === "config.cancel.component" || document.formvalidator.isValid(document.getElementById("component-form")))
		{
			jQuery("#permissions-sliders select").attr("disabled", "disabled");
			Joomla.submitform(task, document.getElementById("component-form"));
		}
	};

	// Select first tab
	jQuery(document).ready(function() {
		jQuery("#configTabs a:first").tab("show");
	});'
);
?>

<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="span2">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<!-- End Sidebar -->
		<div class="span10">
			<ul class="nav nav-tabs" id="configTabs">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>
					<li><a href="#<?php echo $name; ?>" data-toggle="tab"><?php echo JText::_($label); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="tab-content">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php
						if (isset($fieldSet->description) && !empty($fieldSet->description))
						{
							echo '<p class="tab-description">' . JText::_($fieldSet->description) . '</p>';
						}
						?>
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php
							$datashowon = '';
							if ($showonstring = $field->getAttribute('showon'))							{
								JHtml::_('jquery.framework');
								JHtml::_('script', 'jui/cms.js', false, true);
								$showonarr = array();

								foreach (preg_split('%\[AND\]|\[OR\]%', $showonstring) as $showonfield)
								{
									$showon   = explode(':', $showonfield, 2);
									$showonarr[] = array(
										'field'  => $this->form->getFormControl() . '[' . $this->form->getFieldAttribute($showon[0], 'name') . ']',
										'values' => explode(',', $showon[1]),
										'op'     => (preg_match('%\[(AND|OR)\]' . $showonfield . '%', $showonstring, $matches)) ? $matches[1] : ''
									);
								}
								$datashowon = ' data-showon=\'' . json_encode($showonarr) . '\'';
							}
							?>
							<div class="control-group"<?php echo $datashowon; ?>>
								<?php if (!$field->hidden && $name != "permissions") : ?>
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
								<?php endif; ?>
								<div class="<?php if ($name != "permissions") : ?>controls<?php endif; ?>">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>" />
		<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
