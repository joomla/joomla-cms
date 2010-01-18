<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$template = JFactory::getApplication()->getTemplate();

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<form action="<?php echo JRoute::_('index.php?option=com_config');?>" method="post" name="adminForm" autocomplete="off">
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('component.save', this.form);window.top.setTimeout('window.parent.SqueezeBox.close()', 1400);">
				<?php echo JText::_('Save');?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('Cancel');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::_($this->component->option.'_configuration') ?>
		</div>
	</fieldset>

	<?php
	echo JHtml::_('tabs.start','config-tabs-'.$this->component->option.'_configuration', array('useCookie'=>1));
		$fieldSets = $this->form->getFieldsets();
		foreach ($fieldSets as $name => $fieldSet) :
			$label = isset($fieldSet['label']) ? $fieldSet['label'] : 'Config_'.$name;
			echo JHtml::_('tabs.panel',JText::_($label), 'publishing-details');
			if (isset($fieldSet['description'])) :
				echo '<p class="tab-description">'.JText::_($fieldSet['description']).'</p>';
			endif;
	?>

			<?php
			foreach ($this->form->getFields($name) as $field):
			?>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>

			<?php
			endforeach;
			?>


	<div class="clr"></div>
	<?php
		endforeach;
	echo JHtml::_('tabs.end');
	?>

	<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
	<input type="hidden" name="component" value="<?php echo $this->component->option;?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
