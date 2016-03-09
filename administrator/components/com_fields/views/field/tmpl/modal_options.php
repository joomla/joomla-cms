<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo JHtml::_('bootstrap.startAccordion', 'fieldOptions', array('active' => 'collapse0'));
$fieldSets = $this->form->getFieldsets('params');
$i = 0;
?>
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_FIELDS_' . $name . '_FIELDSET_LABEL';
	echo JHtml::_('bootstrap.addSlide', 'fieldOptions', JText::_($label), 'collapse' . $i++);
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

	<?php if ($name == 'basic'): ?>
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
