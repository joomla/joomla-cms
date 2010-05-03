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
?>

<script type="text/javascript">
<!--
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
// -->
</script>

<div class="article-edit">

<form action="<?php JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="col main-section">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_ARTICLE_DETAILS'); ?></legend>

	<div>
		<?php echo $this->form->getLabel('title'); ?>
		<?php echo $this->form->getInput('title'); ?>
	</div>
	<div>
		<?php echo $this->form->getLabel('alias'); ?>
		<?php echo $this->form->getInput('alias'); ?>
	</div>
	<div>
		<?php echo $this->form->getLabel('catid'); ?>
		<?php echo $this->form->getInput('catid'); ?>
	</div>
	<div>
		<?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?>
	</div>

		<?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?>

		<?php echo $this->form->getLabel('language'); ?>
		<?php echo $this->form->getInput('language'); ?>

		<?php echo $this->form->getLabel('featured'); ?>
		<?php echo $this->form->getInput('featured'); ?>


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
			<div>
				<?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('created_by_alias'); ?>
				<?php echo $this->form->getInput('created_by_alias'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('publish_up'); ?>
				<?php echo $this->form->getInput('publish_up'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('publish_down'); ?>
				<?php echo $this->form->getInput('publish_down'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('modified'); ?>
				<?php echo $this->form->getInput('modified'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('version'); ?>
				<?php echo $this->form->getInput('version'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('hits'); ?>
				<?php echo $this->form->getInput('hits'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?>
			</div>

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
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<div>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</div>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>

		<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_RULES'), 'access-rules'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('COM_CONTENT_FIELDSET_RULES'); ?></legend>
			<?php // echo $this->form->getLabel('rules'); ?>
			<?php echo $this->form->getInput('rules'); ?>
		</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_METADATA'), 'meta-options'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('COM_CONTENT_FIELDSET_METADATA'); ?></legend>
		<div>
			<?php echo $this->form->getLabel('metadesc'); ?>
			<?php echo $this->form->getInput('metadesc'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('metakey'); ?>
			<?php echo $this->form->getInput('metakey'); ?>
		</div>

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
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<div>
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					</div>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>

		<div>
			<?php echo $this->form->getLabel('xreference'); ?>
			<?php echo $this->form->getInput('xreference'); ?>
		</div>
		</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
</div>


	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
</div>
