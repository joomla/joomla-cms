<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
HTMLHelper::_('formbehavior.chosen', 'select');
$this->tab_name         = 'com-contact-form';
$this->ignore_fieldsets = array('details', 'item_associations');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "contact.cancel" || document.formvalidator.isValid(document.getElementById("contact-form")))
		{
			' . $this->form->getField('misc')->save() . '
			Joomla.submitform(task, document.getElementById("contact-form"));
		}
	};
');
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<form action="<?php echo Route::_('index.php?option=com_contact&id=' . (int) $this->item->id); ?>" method="post"
		  name="adminForm" id="adminForm" class="form-validate form-vertical">
		<fieldset>
			<?php echo HTMLHelper::_('bootstrap.startTabSet', $this->tab_name, array('active' => 'details')); ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', $this->tab_name, 'details', empty($this->item->id) ? Text::_('COM_CONTACT_NEW_CONTACT') : Text::_('COM_CONTACT_EDIT_CONTACT')); ?>
			<?php echo $this->form->renderField('name'); ?>

			<?php if (is_null($this->item->id)) : ?>
				<?php echo $this->form->renderField('alias'); ?>
			<?php endif; ?>

			<?php echo $this->form->renderFieldset('details'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', $this->tab_name, 'misc', Text::_('COM_CONTACT_FIELDSET_MISCELLANEOUS')); ?>
			<?php echo $this->form->renderField('misc'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</fieldset>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('contact.save')">
					<span class="icon-ok" aria-hidden="true"></span><?php echo Text::_('JSAVE'); ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('contact.cancel')">
					<span class="icon-cancel" aria-hidden="true"></span><?php echo Text::_('JCANCEL'); ?>
				</button>
			</div>
			<?php if ($this->params->get('save_history', 0) && $this->item->id) : ?>
				<div class="btn-group">
					<?php echo $this->form->getInput('contenthistory'); ?>
				</div>
			<?php endif; ?>
		</div>
	</form>
</div>
