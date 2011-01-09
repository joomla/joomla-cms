<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<div class="login<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<?php if ($this->params->get('logindescription_show') == 1 || $this->params->get('login_image') != '') : ?>
	<div class="login-description">
	<?php endif ; ?>

		<?php if($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>

		<?php if (($this->params->get('login_image')!='')) :?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
		<?php endif; ?>

	<?php if ($this->params->get('logindescription_show') == 1 || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif ; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post">

		<fieldset>
			<?php foreach ($this->form->getFieldset('credentials') as $field): ?>
				<?php if (!$field->hidden): ?>
					<div class="login-fields"><?php echo $field->label; ?>
					<?php echo $field->input; ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
			<button type="submit" class="button"><?php echo JText::_('JLOGIN'); ?></button>
			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url',$this->form->getValue('return'))); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
