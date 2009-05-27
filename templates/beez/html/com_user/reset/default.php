<?php // @version $Id: default.php 11786 2009-04-24 17:48:08Z kdevine $
defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_users&task=requestreset'); ?>" method="post" class="josForm form-validate">
	<p><?php echo JText::_('RESET_PASSWORD_REQUEST_DESCRIPTION'); ?></p>

	<label for="email" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_EMAIL_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_EMAIL_TIP_TEXT'); ?>"><?php echo JText::_('Email Address'); ?>:</label>
	<input id="email" name="email" type="text" class="required validate-email" />

	<button type="submit" class="validate"><?php echo JText::_('Submit'); ?></button>
	<?php echo JHtml::_('form.token'); ?>
</form>
