<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $displayData->get('form')->getFieldsets('metadata');
foreach ($fieldSets as $name => $fieldSet) :
	$metadatatabs = 'metadata-' . $name;
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="alert alert-info">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>
	<?php if ($name == 'jmetadata') : // Include the real fields in this panel.
	?>
		<div class="control-group">
			<div class="control-label"><?php echo $displayData->get('form')->getLabel('metadesc'); ?></div>
			<div class="controls"><?php echo $displayData->get('form')->getInput('metadesc'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $displayData->get('form')->getLabel('metakey'); ?></div>
			<div class="controls"><?php echo $displayData->get('form')->getInput('metakey'); ?></div>
		</div>
		<?php if ($displayData->get('form')->getLabel('xreference')):?>
			<div class="control-group">
				<div class="control-label"><?php echo $displayData->get('form')->getLabel('xreference'); ?></div>
				<div class="controls"><?php echo $displayData->get('form')->getInput('xreference'); ?></div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php foreach ($displayData->get('form')->getFieldset($name) as $field) : ?>
		<?php if ($field->name != 'jform[metadata][tags][]') :?>
		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endforeach; ?>
