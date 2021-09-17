<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (isset($fieldSet->description) && trim($fieldSet->description)) :
	echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
endif;
?>
<fieldset class="panelform">
	<legend class="element-invisible"><?php echo JText::_($fieldSet->label); ?></legend>
	<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('created_user_id'); ?>
			<?php echo $this->form->getInput('created_user_id'); ?></li>

			<li><?php echo $this->form->getLabel('created_by_alias'); ?>
			<?php echo $this->form->getInput('created_by_alias'); ?></li>

			<li><?php echo $this->form->getLabel('created_time'); ?>
			<?php echo $this->form->getInput('created_time'); ?></li>

			<li><?php echo $this->form->getLabel('publish_up'); ?>
			<?php echo $this->form->getInput('publish_up'); ?></li>

			<li><?php echo $this->form->getLabel('publish_down'); ?>
			<?php echo $this->form->getInput('publish_down'); ?></li>

			<li><?php echo $this->form->getLabel('modified_user_id'); ?>
			<?php echo $this->form->getInput('modified_user_id'); ?></li>

			<li><?php echo $this->form->getLabel('modified_time'); ?>
			<?php echo $this->form->getInput('modified_time'); ?></li>
			<li><?php echo $this->form->getLabel('version'); ?>
			<?php echo $this->form->getInput('version'); ?></li>


			</ul>
</fieldset>

<?php $fieldSets = $this->form->getFieldsets('params');
	foreach ($fieldSets as $name => $fieldSet) :
	echo JHtml::_('sliders.panel', JText::_($fieldSet->label), $name.'-params');
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>
	<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_($fieldSet->label); ?></legend>
		<ul class="adminformlist">
		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
			<li><?php echo $field->label; ?>
			<?php echo $field->input; ?></li>
		<?php endforeach; ?>
		</ul>
	</fieldset>
<?php endforeach; ?>
