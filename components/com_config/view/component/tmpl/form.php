<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task === "config.cancel.component" || document.formvalidator.isValid(document.getElementById("component-form")))
		{
			Joomla.submitform(task, document.getElementById("component-form"));
		}
	};
');
?>
<form action="<?php echo JRoute::_($this->formUrl); ?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="row-fluid">
			<!-- Begin Content -->
			<div class="btn-toolbar">
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('store')">
						<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn" onclick="Joomla.submitbutton('cancel')">
						<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
					</button>
				</div>
			</div>

			<hr class="hr-condensed" />
			<div class="row-fluid">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php if($name === 'permissions'):?>
						<?php continue;?>
					<?php endif;?>

					<?php if (isset($fieldSet->description) && !empty($fieldSet->description)):?>
						<p class="tab-description"><?php echo JText::_($fieldSet->description); ?></p>
					<?php endif;?>

					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php
							$class = '';
							$rel = '';
							if ($showon = $field->getAttribute('showon'))
							{
								JHtml::_('jquery.framework');
								JHtml::_('script', 'jui/cms.js', false, true);
								$id = $this->form->getFormControl();
								$showon = explode(':', $showon, 2);
								$class = ' showon_' . implode(' showon_', explode(',', $showon[1]));
								$rel = ' rel="showon_' . $id . '[' . $showon[0] . ']"';
							}
							?>
							<div class="control-group<?php echo $class; ?>"<?php echo $rel; ?>>
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
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->item->extension_id; ?>" />
		<input type="hidden" name="component" value="<?php echo $this->item->element; ?>" />
		<input type="hidden" name="return" value="<?php echo $this->config['return']; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<script type="text/javascript">
	jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>
