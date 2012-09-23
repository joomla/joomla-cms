<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('metadata');
foreach ($fieldSets as $name => $fieldSet) :
	?>
	<div class="tab-pane" id="metadata-<?php echo $name;?>">
	<?php
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="alert alert-info">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>
			<?php if ($name == 'jmetadata') : // Include the real fields in this panel.
			?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('metadesc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('metadesc'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('metakey'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('metakey'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('xreference'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('xreference'); ?></div>
				</div>
			<?php endif; ?>
			<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endforeach; ?>
	</div>
<?php endforeach; ?>
