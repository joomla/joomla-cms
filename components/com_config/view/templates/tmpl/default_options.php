<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load chosen.css


?>
<?php

	$fieldSets = $this->form->getFieldsets('params');
?>

<legend><?php echo JText::_('COM_CONFIG_TEMPLATE_SETTINGS'); ?></legend>

<?php

	// Search for com_config field set
	if (!empty($fieldSets['com_config'])) : ?>

	<fieldset class="form-horizontal">
		<?php echo $this->form->renderFieldset('com_config'); ?>
	</fieldset>

<?php else :

	// Fall-back to display all in params
	foreach ($fieldSets as $name => $fieldSet) :
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONFIG_' . $name . '_FIELDSET_LABEL';

	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	endif;
	?>

<fieldset class="form-horizontal">
	<?php echo $this->form->renderFieldset($name); ?>
</fieldset>
	<?php endforeach;
	endif;
