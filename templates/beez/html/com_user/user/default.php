<?php // @version $Id: default.php 11623 2009-02-15 15:23:08Z kdevine $
defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>
<h1 class="componentheading">
	<?php echo JText::_('Welcome!'); ?>
</h1>

<div class="contentdescription">
	<?php echo $this->params->get('welcome_desc', JText::_('WELCOME_DESC'));; ?>
</div>
