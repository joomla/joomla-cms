<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "group.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<div class="groups-edit">
<form action="<?php echo JRoute::_('index.php?option=com_fields&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_FIELDS_VIEW_FIELD_FIELDSET_GENERAL'); ?></legend>
		<ul class="adminformlist">
			<li>
				<?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('description'); ?>
				<?php echo $this->form->getInput('description'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('note'); ?>
				<?php echo $this->form->getInput('note'); ?>
			</li>
		</ul>
		<div class="clr"></div>
		</fieldset>
	</div>

	<div class="col options-section">
		<?php echo JHtml::_('sliders.start', 'groups-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
		<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('created_by'); ?>
					<?php echo $this->form->getInput('created_by'); ?>
				</li>
				<?php if ((int) $this->item->created) : ?>
					<li>
						<?php echo $this->form->getLabel('created'); ?>
						<?php echo $this->form->getInput('created'); ?>
					</li>
				<?php endif; ?>
				<?php if ($this->item->modified_by) : ?>
					<li>
						<?php echo $this->form->getLabel('modified_by'); ?>
						<?php echo $this->form->getInput('modified_by'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('modified'); ?>
						<?php echo $this->form->getInput('modified'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('id'); ?>
						<?php echo $this->form->getInput('id'); ?>
					</li>
				<?php endif; ?>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
		<div class="clr"></div>

		<?php $this->set('ignore_fieldsets', array('fieldparams')); ?>
	</div>
	<div class="clr"></div>

	<?php if ($this->canDo->get('core.admin')) : ?>
		<div class="col rules-section">
			<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'), 'access-rules'); ?>
			<fieldset class="panelform">
				<legend class="element-invisible"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></legend>
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
			</fieldset>

				<?php echo JHtml::_('sliders.end'); ?>
		</div>
	<?php endif; ?>

		<?php echo $this->form->getInput('context'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>
</div>