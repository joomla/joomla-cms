<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$template = $app->getTemplate();

JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.framework');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.getElementById('component-form'))) {
			Joomla.submitform(task, document.getElementById('component-form'));
		}
	}
");
?>
<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate">
	<?php
	echo JHtml::_('tabs.start', 'config-tabs-' . $this->component->option . '_configuration', array('useCookie' => 1));
	$fieldSets = $this->form->getFieldsets();
	?>
	<?php foreach ($fieldSets as $name => $fieldSet) : ?>
		<?php
		$label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label;
		echo JHtml::_('tabs.panel', JText::_($label), 'publishing-details');
		if (isset($fieldSet->description) && !empty($fieldSet->description))
		{
		echo '<p class="tab-description">' . JText::_($fieldSet->description) . '</p>';
		}
		?>
		<ul class="config-option-list">
			<?php foreach ($this->form->getFieldset($name) as $field): ?>
				<li>
					<?php if (!$field->hidden) : ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="clr"></div>
	<?php endforeach; ?>
	<?php echo JHtml::_('tabs.end'); ?>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>" />
		<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
