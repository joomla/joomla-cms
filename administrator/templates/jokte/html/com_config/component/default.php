<?php
/**
 * @version		$Id: default.php 19529 2010-11-17 14:13:43Z chdemko $
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
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.id('component-form'))) {
			Joomla.submitform(task, document.getElementById('component-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_config');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate">
	<fieldset>
		<div id="toolbar" class="fltrt toolbar-list">
		<ul class="clean">
			<li id="toolbar-save">
				<a onclick="Joomla.submitform('component.save', this.form);">
					<?php echo JText::_('JSAVE');?>
				</a>
			</li>
			<li id="toolbar-apply">	
				<a type="button" onclick="Joomla.submitform('component.apply', this.form);">
					<?php echo JText::_('JAPPLY');?>
				</a>
			</li>
			<li id="toolbar-cancel">
				<a onclick="window.parent.SqueezeBox.close();">
					<?php echo JText::_('JCANCEL');?>
				</a>
			</li>
		</ul>
		</div>
		<div class="pagetitle icon-48-config" >
			<h2><?php echo JText::_($this->component->option.'_configuration') ?></h2>
		</div>
	</fieldset>

	<?php
	echo JHtml::_('tabs.start','config-tabs-'.$this->component->option.'_configuration', array('useCookie'=>1));
		$fieldSets = $this->form->getFieldsets();
		foreach ($fieldSets as $name => $fieldSet) :
			$label = empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
			echo JHtml::_('tabs.panel',JText::_($label), 'publishing-details');
			if (isset($fieldSet->description) && !empty($fieldSet->description)) :
				echo '<p class="tab-description">'.JText::_($fieldSet->description).'</p>';
			endif;
	?>
			<ul class="config-option-list">
			<?php
			foreach ($this->form->getFieldset($name) as $field):
			?>
				<li>
				<?php if (!$field->hidden) : ?>
				<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
				</li>
			<?php
			endforeach;
			?>
			</ul>


	<div class="clr"></div>
	<?php
		endforeach;
	echo JHtml::_('tabs.end');
	?>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	<br />
</form>
