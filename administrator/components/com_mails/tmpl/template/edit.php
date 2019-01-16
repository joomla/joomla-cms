<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$this->useCoreUI = true;

$app = Factory::getApplication();
$input = $app->input;
list($component, $sub_id) = explode('.', $this->master->template_id, 2);

$doc = Factory::getDocument();
$doc->addScriptDeclaration('
document.addEventListener(\'DOMContentLoaded\', () => {
	var templateData = ' . json_encode($this->templateData) . ';
	document.querySelectorAll(\'#item-form joomla-field-switcher\').forEach(function (el) {
		el.addEventListener(\'joomla.switcher.on\', function() {
			var el2 = document.getElementById(this.id.substring(0, this.id.length - 9));
			el2.disabled = false;
			el2.value = templateData[this.id.slice(6, -9)].translated;
		});
		el.addEventListener(\'joomla.switcher.off\', function() {
			var el2 = document.getElementById(this.id.substring(0, this.id.length - 9));
			el2.disabled = true;
			el2.value = templateData[this.id.slice(6, -9)].master;
		});
	});
});');
?>

<form action="<?php echo Route::_('index.php?option=com_mails&layout=edit&template_id=' . $this->item->template_id . '&language=' . $this->item->language); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_MAILS_MAIL_CONTENT')); ?>
		<div class="row">
			<div class="col-md-12">
				<h1><?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?>
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
				<?php echo $this->form->getField('subject_switcher')->input; ?>
			</div>
		</div>

		<?php if ($this->form->getField('body')) : ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('body'); ?>
			</div>
			<div class="col-md-3">
				<?php echo $this->form->getField('body_switcher')->input; ?>
				<h2><?php echo Text::_('COM_MAILS_FIELDSET_TAGS_LABEL'); ?></h2>
				<?php echo MailsHelper::mailtags($this->master, 'body'); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($this->form->getField('htmlbody')) : ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('htmlbody'); ?>
			</div>
			<div class="col-md-3">
				<?php echo $this->form->getField('htmlbody_switcher')->input; ?>
				<h2><?php echo Text::_('COM_MAILS_FIELDSET_TAGS_LABEL'); ?></h2>
				<?php echo MailsHelper::mailtags($this->master, 'htmlbody'); ?>
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

		<?php echo JHtml::_('uitab.endTab'); ?>

		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo JHtml::_('uitab.endTabSet'); ?>
	</div>
	<?php echo $this->form->renderField('template_id'); ?>
	<?php echo $this->form->renderField('language'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
