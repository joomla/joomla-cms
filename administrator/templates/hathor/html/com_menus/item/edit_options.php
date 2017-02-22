<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$assoc = JLanguageAssociations::isEnabled();

?>
<?php
	$fieldSets = $this->form->getFieldsets('request');

	if (!empty($fieldSets))
	{
		$fieldSet = array_shift($fieldSets);
		$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_'.$fieldSet->name.'_FIELDSET_LABEL';
		echo JHtml::_('sliders.panel', JText::_($label), 'request-options');
		if (isset($fieldSet->description) && trim($fieldSet->description)) :
			echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
		endif;
	?>
		<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_($label) ?></legend>
			<?php $hidden_fields = ''; ?>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('request') as $field) : ?>
				<?php if (!$field->hidden) : ?>
				<li>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
				<?php else : $hidden_fields .= $field->input; ?>
				<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php echo $hidden_fields; ?>
		</fieldset>
<?php
	}

	$fieldSets = $this->form->getFieldsets('params');

	foreach ($fieldSets as $name => $fieldSet) :
		$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_'.$name.'_FIELDSET_LABEL';
		echo JHtml::_('sliders.panel', JText::_($label), $name.'-options');
			if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
			endif;
			?>
		<div class="clr"></div>
		<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_($label) ?></legend>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endforeach;?>

	<?php if ($assoc) : ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_MENUS_ITEM_ASSOCIATIONS_FIELDSET_LABEL'), '-options');?>
		<?php echo $this->loadTemplate('associations'); ?>
	<?php endif; ?>
