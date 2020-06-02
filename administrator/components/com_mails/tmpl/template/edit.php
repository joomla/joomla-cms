<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mails\Administrator\Helper\MailsHelper;

$app = Factory::getApplication();
$doc = Factory::getDocument();

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'com_mails/admin-email-template-edit.min.js', ['version' => 'auto', 'relative' => true]);

$this->useCoreUI = true;

$input = $app->input;
list($component, $sub_id) = explode('.', $this->master->template_id, 2);
$sub_id = str_replace('.', '_', $sub_id);

$doc->addScriptOptions('com_mails', ['templateData' => $this->templateData]);

?>

<form action="<?php echo Route::_('index.php?option=com_mails&layout=edit&template_id=' . $this->item->template_id . '&language=' . $this->item->language); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_MAILS_MAIL_CONTENT')); ?>
		<div class="row">
			<div class="col-md-12">
				<h1><?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?> - <?php echo $this->escape($this->item->language); ?>
					<span class="small">(<?php echo $this->escape($this->master->template_id); ?>)</span>
				</h1>
				<p><?php echo Text::_($component . '_MAIL_' . $sub_id . '_DESC'); ?></p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('subject'); ?>
			</div>
			<div class="col-md-3">
				<?php echo $this->form->getField('subject_switcher')->label; ?>
				<?php echo $this->form->getField('subject_switcher')->input; ?>
			</div>
		</div>

		<?php if ($fieldBody = $this->form->getField('body')) : ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('body'); ?>
			</div>
			<div class="col-md-3">
				<?php echo $this->form->getField('body_switcher')->label; ?>
				<?php echo $this->form->getField('body_switcher')->input; ?>
				<div class="tags-container-body <?php echo $fieldBody->disabled ? 'hidden' : ''; ?>">
					<h2><?php echo Text::_('COM_MAILS_FIELDSET_TAGS_LABEL'); ?></h2>
					<?php echo MailsHelper::mailtags($this->master, 'body'); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($fieldHtmlBody = $this->form->getField('htmlbody')) : ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('htmlbody'); ?>
			</div>
			<div class="col-md-3">
				<?php echo $this->form->getField('htmlbody_switcher')->label; ?>
				<?php echo $this->form->getField('htmlbody_switcher')->input; ?>
				<div class="tags-container-htmlbody <?php echo $fieldHtmlBody->disabled ? 'hidden' : ''; ?>">
					<h2><?php echo Text::_('COM_MAILS_FIELDSET_TAGS_LABEL'); ?></h2>
					<?php echo MailsHelper::mailtags($this->master, 'htmlbody'); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($this->form->getField('attachments')) : ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('attachments'); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if (count($this->form->getFieldset('basic'))) : ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	<?php echo $this->form->renderField('template_id'); ?>
	<?php echo $this->form->renderField('language'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
