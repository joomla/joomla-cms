<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$fieldsets = $this->form->getFieldsets('params');

foreach ($fieldsets as $name => $fieldset) :
	echo '<div class="tab-pane" id="options-'.$name.'">';
	
	$label = !empty($fieldset->label) ? $fieldset->label : 'COM_TEMPLATES_'.$name.'_FIELDSET_LABEL';
		if (isset($fieldset->description) && trim($fieldset->description)) :
			echo '<p class="tip">'.$this->escape(JText::_($fieldset->description)).'</p>';
		endif;
		?>
		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
			<div class="control-group">
			<?php if (!$field->hidden) : ?>
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
			<?php endif; ?>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		
	<?php echo '</div>'; // .tab-pane div ?>
<?php endforeach;  ?>
