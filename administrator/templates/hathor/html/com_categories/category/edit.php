<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$input = JFactory::getApplication()->input;

$saveHistory = $this->state->get('params')->get('save_history', 0);

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			" . $this->form->getField('description')->save() . "
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
");
$assoc = JLanguageAssociations::isEnabled();

?>

<div class="category-edit">
	<form action="<?php echo JRoute::_('index.php?option=com_categories&extension=' . $input->getCmd('extension', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
		<div class="col main-section">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_CATEGORIES_FIELDSET_DETAILS'); ?></legend>
				<ul class="adminformlist">
					<li>
						<?php echo $this->form->getLabel('title'); ?>
						<?php echo $this->form->getInput('title'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('alias'); ?>
						<?php echo $this->form->getInput('alias'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('extension'); ?>
						<?php echo $this->form->getInput('extension'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('parent_id'); ?>
						<?php echo $this->form->getInput('parent_id'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('published'); ?>
						<?php echo $this->form->getInput('published'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('access'); ?>
						<?php echo $this->form->getInput('access'); ?>
					</li>
					<?php if ($this->canDo->get('core.admin')) : ?>
						<li>
							<span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
							<button type="button" onclick="document.location.href='#access-rules';">
								<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?></button>

						</li>
					<?php endif; ?>
					<li>
						<?php echo $this->form->getLabel('language'); ?>
						<?php echo $this->form->getInput('language'); ?>
					</li>
					<!-- Tag field -->
					<li>
						<?php if ($this->checkTags) : ?>
							<?php echo $this->form->getLabel('tags'); ?>
							<div class="is-tagbox">
								<?php echo $this->form->getInput('tags'); ?>
							</div>
						<?php endif; ?>
					</li>
					<?php if ($saveHistory) : ?>
						<li><?php echo $this->form->getLabel('version_note'); ?>
						<?php echo $this->form->getInput('version_note'); ?></li>
					<?php endif; ?>
					<li>
						<?php echo $this->form->getLabel('id'); ?>
						<?php echo $this->form->getInput('id'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('hits'); ?>
						<?php echo $this->form->getInput('hits'); ?>
					</li>
				</ul>

				<div class="clr"></div>
				<?php echo $this->form->getLabel('description'); ?>
				<div class="clr"></div>
				<?php echo $this->form->getInput('description'); ?>
				<div class="clr"></div>
			</fieldset>
		</div>

		<div class="col options-section">

			<?php echo JHtml::_('sliders.start', 'categories-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo $this->loadTemplate('options'); ?>
			<div class="clr"></div>

			<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
			<fieldset class="panelform">
				<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
				<?php echo $this->loadTemplate('metadata'); ?>
			</fieldset>

			<?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
					<?php
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CATEGORIES_' . $name . '_FIELDSET_LABEL';
					echo JHtml::_('sliders.panel', JText::_($label), $name . '-options');
					if (isset($fieldSet->description) && trim($fieldSet->description))
					{
						echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
					}
					?>
					<div class="clr"></div>
					<fieldset class="panelform">
						<ul class="adminformlist">
							<?php foreach ($this->form->getFieldset($name) as $field) : ?>
								<li>
									<?php echo $field->label; ?>
									<?php echo $field->input; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</fieldset>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ($assoc) : ?>
				<?php echo JHtml::_('sliders.panel', JText::_('COM_CATEGORIES_ITEM_ASSOCIATIONS_FIELDSET_LABEL'), '-options');?>
				<?php echo $this->loadTemplate('associations'); ?>
			<?php endif; ?>

			<?php echo JHtml::_('sliders.end'); ?>
		</div>
		<div class="clr"></div>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<div class="col rules-section">

				<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

				<?php echo JHtml::_('sliders.panel', JText::_('COM_CATEGORIES_FIELDSET_RULES'), 'access-rules'); ?>
				<fieldset class="panelform">
					<legend class="element-invisible"><?php echo JText::_('COM_CATEGORIES_FIELDSET_RULES'); ?></legend>
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>

				<?php echo JHtml::_('sliders.end'); ?>
			</div>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
	<div class="clr"></div>
</div>
