<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.combobox');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$langs = isset($app->languages_enabled);

$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';

// Get Params Fieldsets
$this->fieldsets = $this->form->getFieldsets('params');


$script = "Joomla.submitbutton = function(task)
	{
			if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {";
if ($hasContent)
{
	$script .= $this->form->getField('content')->save();
}
$script .= "	Joomla.submitform(task, document.getElementById('module-form'));
				if (self != top)
				{
					window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
				}
			}
	}";

JFactory::getDocument()->addScriptDeclaration($script);

// Set main fields.
$this->fields = array(
	'showtitle',
	'position',
	'ordering',
	'published',
	'publish_up',
	'publish_down',
	'access',
	'language',
	'note'
);

if ((string) $this->item->xml->name == 'mod_login')
{
	$this->fields = array_diff($this->fields, array('published', 'publish_up', 'publish_down'));
}

?>
<form action="<?php echo JRoute::_('index.php?option=com_modules&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_MODULES_MODULE', true)); ?>

		<div class="row-fluid">
			<div class="span9">
				<?php if ($this->item->xml) : ?>
					<?php if ($this->item->xml->description) : ?>
						<h4>
							<?php
							if ($this->item->xml)
							{
								echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module;
							}
							else
							{
								echo JText::_('COM_MODULES_ERR_XML');
							}
							?>
							<span class="label"><?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?></span>
						</h4>
						<div>
							<?php echo JText::_($this->item->xml->description); ?>
						</div>
						<hr />
					<?php endif; ?>
				<?php else : ?>
					<div class="alert alert-error"><?php echo JText::_('COM_MODULES_ERR_XML'); ?></div>
				<?php endif; ?>
				<?php echo $this->form->getControlGroups('basic'); ?>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.main', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if ($hasContent) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'content', JText::_('COM_MODULES_CUSTOM_OUTPUT', true)); ?>
			<?php echo $this->loadTemplate('content'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->item->client_id == 0) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'assignment', JText::_('COM_MODULES_MENU_ASSIGNMENT', true)); ?>
			<?php echo $this->loadTemplate('assignment'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php $this->ignore_fieldsets = array('basic'); ?>
		<?php echo JLayoutHelper::render('joomla.edit.options', $this); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		<?php echo $this->form->getInput('module'); ?>
		<?php echo $this->form->getInput('client_id'); ?>
	</div>
</form>
