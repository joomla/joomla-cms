<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$app = Factory::getApplication();
$input = $app->input;
list($component, $sub_id) = explode('.', $this->master->mail_id, 2);

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

<form action="<?php echo Route::_('index.php?option=com_config&layout=edit&mail_id=' . $this->item->mail_id . '&language=' . $this->item->language); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<div class="row">
		<div class="col-md-12">
			<h1><?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?>
				<span class="small">(<?php echo $this->escape($this->master->mail_id); ?>)</span>
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

	<div class="row">
		<div class="col-md-9">
			<?php echo $this->form->renderField('body'); ?>
		</div>
		<div class="col-md-3">
			<?php echo $this->form->getField('body_switcher')->input; ?>
			<h2><?php echo Text::_('COM_CONFIG_FIELDSET_TAGS_LABEL'); ?></h2>
			<?php echo JHtml::_('config.mailtags', $this->master, 'body'); ?>
		</div>
	</div>

	<div class="row">
		<div class="col-md-9">
			<?php echo $this->form->renderField('htmlbody'); ?>
		</div>
		<div class="col-md-3">
			<?php echo $this->form->getField('htmlbody_switcher')->input; ?>
			<h2><?php echo Text::_('COM_CONFIG_FIELDSET_TAGS_LABEL'); ?></h2>
			<?php echo JHtml::_('config.mailtags', $this->master, 'htmlbody'); ?>
		</div>
	</div>

	<?php echo $this->form->renderField('mail_id'); ?>
	<?php echo $this->form->renderField('language'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
