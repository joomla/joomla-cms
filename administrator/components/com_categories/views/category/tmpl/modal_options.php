<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

echo JHtml::_('bootstrap.startAccordion', 'categoryOptions', array('active' => 'collapse0'));
$fieldSets = $this->form->getFieldsets('params');
$i = 0;
?>
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CATEGORIES_' . $name . '_FIELDSET_LABEL';
	echo JHtml::_('bootstrap.addSlide', 'categoryOptions', JText::_($label), 'collapse' . ($i++));
	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
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

	<?php if ($name == 'basic') : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('note'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('note'); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php endforeach; ?>
<?php echo JHtml::_('bootstrap.endAccordion'); ?>
