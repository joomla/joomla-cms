<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form id="jForm" action="<?php JRoute::_('index.php')?>" method="post">
<?php if ($this->params->get('show_page_title')) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->params->get('page_title'); ?></div>
<?php endif; ?>
	<p>
		<?php if ($this->params->get('filter')) : ?>
		<?php echo JText::_('Filter').'&nbsp;'; ?>
		<input type="text" name="filter" value="<?php echo $this->filter;?>" class="inputbox" onchange="document.jForm.submit();" />
		<?php endif; ?>
		<?php echo $this->form->monthField; ?>
		<?php echo $this->form->yearField; ?>
		<?php echo $this->form->limitField; ?>
		<button type="submit" class="button"><?php echo JText::_('Filter'); ?></button>
	</p>

<?php echo $this->loadTemplate('items'); ?>

	<input type="hidden" name="view" value="archive" />
	<input type="hidden" name="option" value="com_content" />
</form>
