<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php
	echo JHtml::_('bootstrap.startAccordion', 'menuOptions', array('active' => 'collapse0'));
	$fieldSets = $this->form->getFieldsets('params');
	$i = 0;

	foreach ($fieldSets as $name => $fieldSet) :
		if (!(($this->item->link == 'index.php?option=com_wrapper&view=wrapper') && $fieldSet->name == 'request')
				&& !($this->item->link == 'index.php?Itemid=' && $fieldSet->name == 'aliasoptions')) :
			$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
			echo JHtml::_('bootstrap.addSlide', 'menuOptions', JText::_($label), 'collapse' . ($i++));
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
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
					<?php endforeach;
			echo JHtml::_('bootstrap.endSlide');
		endif;
	endforeach; ?>
<?php

echo JHtml::_('bootstrap.endAccordion');
