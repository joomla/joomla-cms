<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

echo JHtml::_('bootstrap.startAccordion', 'templatestyleOptions', array('active' => 'collapse0'));
$fieldsets = $this->form->getFieldsets('params');
$i = 0;
?>
<?php foreach ($fieldsets as $name => $fieldset) : ?>
<?php if (!in_array($fieldset->name, array('description', 'basic'))) : ?>
	<?php
		$label = !empty($fieldset->label) ? $fieldset->label : 'COM_TEMPLATES_' . $name . '_FIELDSET_LABEL';
		echo JHtml::_('bootstrap.addSlide', 'templatestyleOptions', JText::_($label), 'collapse' . $i++);
	?>
	<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
		<p class="tip"><?php echo $this->escape(JText::_($fieldset->description)); ?></p>
		<?php endif; ?>
		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endif; ?>
<?php endforeach; ?>
<?php echo JHtml::_('bootstrap.endAccordion'); ?>
