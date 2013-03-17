<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<<<<<<< HEAD
<?php foreach ($this->fieldsets as $name => $fieldset) : ?>
	<?php if (!in_array($fieldset->name, array('description', 'basic'))) : ?>
		<div class="tab-pane" id="tab-<?php echo $name; ?>">
			<?php $label = !empty($fieldset->label) ? $fieldset->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL'; ?>
			<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
				<p class="tip"><?php echo $this->escape(JText::_($fieldset->description)); ?></p>
			<?php endif; ?>
			<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<?php if ($field->hidden) : ?>
					<?php echo $field->input; ?>
				<?php else : ?>
=======
<?php
	echo JHtml::_('bootstrap.startAccordion', 'moduleOptions', array('active' => 'collapse0'));
	$fieldSets = $this->form->getFieldsets('params');
	$i = 0;

	foreach ($fieldSets as $name => $fieldSet) :
		$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_'.$name.'_FIELDSET_LABEL';
		echo JHtml::_('bootstrap.addSlide', 'moduleOptions', JText::_($label), 'collapse' . $i++);
			if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
			endif;
			?>
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
>>>>>>> remotes/upstream/master
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
<<<<<<< HEAD
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
=======
				<?php endforeach;
		echo JHtml::_('bootstrap.endSlide');
	endforeach;
echo JHtml::_('bootstrap.endAccordion');
>>>>>>> remotes/upstream/master
