<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	function submitbutton(task)
	{
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('articletext')->save(); ?>
			submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div class="article-edit">

<form action="<?php JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="col main-section">
	<fieldset class="adminform">
	<legend><?php echo empty($this->item->id) ? JText::_('COM_CONTENT_NEW_ARTICLE') : JText::sprintf('COM_CONTENT_EDIT_ARTICLE', $this->item->id); ?></legend>
	<ul class="adminformlist">
		<li><?php echo $this->form->getLabel('title'); ?>
		<?php echo $this->form->getInput('title'); ?></li>

		<li><?php echo $this->form->getLabel('alias'); ?>
		<?php echo $this->form->getInput('alias'); ?></li>

		<li><?php echo $this->form->getLabel('catid'); ?>
		<?php echo $this->form->getInput('catid'); ?></li>

		<li><?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?></li>

		<li><?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?></li>

		<li><?php echo $this->form->getLabel('language'); ?>
		<?php echo $this->form->getInput('language'); ?></li>

		<li><?php echo $this->form->getLabel('featured'); ?>
		<?php echo $this->form->getInput('featured'); ?></li>
				
		<li><?php echo $this->form->getLabel('id'); ?>
		<?php echo $this->form->getInput('id'); ?></li>
		
	</ul>

		<div class="clr"></div>
		<?php echo $this->form->getLabel('articletext'); ?>
		<div class="clr"></div>
		<?php echo $this->form->getInput('articletext'); ?>
		<div class="clr"></div>

	</fieldset>
</div>

<div class="col options-section">

		<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

		<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('COM_CONTENT_FIELDSET_PUBLISHING'); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?></li>

				<li><?php echo $this->form->getLabel('created_by_alias'); ?>
				<?php echo $this->form->getInput('created_by_alias'); ?></li>

				<li><?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?></li>

				<li><?php echo $this->form->getLabel('publish_up'); ?>
				<?php echo $this->form->getInput('publish_up'); ?></li>

				<li><?php echo $this->form->getLabel('publish_down'); ?>
				<?php echo $this->form->getInput('publish_down'); ?></li>

				<li><?php echo $this->form->getLabel('modified'); ?>
				<?php echo $this->form->getInput('modified'); ?></li>

				<li><?php echo $this->form->getLabel('version'); ?>
				<?php echo $this->form->getInput('version'); ?></li>

				<li><?php echo $this->form->getLabel('hits'); ?>
				<?php echo $this->form->getInput('hits'); ?></li>

			</ul>

		</fieldset>

		<?php
		$fieldSets = $this->form->getFieldsets('attribs');
		foreach ($fieldSets as $name => $fieldSet) :
			echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options');
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

		<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_RULES'), 'access-rules'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('COM_CONTENT_FIELDSET_RULES'); ?></legend>
			<?php // echo $this->form->getLabel('rules'); ?>
			<?php echo $this->form->getInput('rules'); ?>
		</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
		<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('metadesc'); ?>
			<?php echo $this->form->getInput('metadesc'); ?></li>

			<li><?php echo $this->form->getLabel('metakey'); ?>
			<?php echo $this->form->getInput('metakey'); ?></li>
		</ul>

			<?php
			$fieldSets = $this->form->getFieldsets('metadata');

			foreach ($fieldSets as $name => $fieldSet) :
				echo JHtml::_('sliders.panel',JText::_($label), $name.'-options');
					if (isset($fieldSet->description) && trim($fieldSet->description)) :
						echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
					endif;
					?>
				<fieldset class="panelform">
				<legend class="element-invisible"><?php echo JText::_($label); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
							<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>

		<ul>
			<li><?php echo $this->form->getLabel('xreference'); ?>
			<?php echo $this->form->getInput('xreference'); ?></li>
		</ul>
		</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
</div>


	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
</div>
