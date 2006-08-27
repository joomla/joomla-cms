<form id="jForm" action="<?php JURI::resolve('index.php')?>" method="post">
<?php if ($header) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $header; ?></div>
<?php endif; ?>
	<p>
		<?php if ($this->params->get('filter')) : ?>
		<?php echo JText::_('Filter').'&nbsp;'; ?>
		<input type="text" name="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.jForm.submit();" />
		<?php endif; ?>
		<?php echo $this->form->monthField; ?>
		<?php echo $this->form->yearField; ?>
		<?php echo $this->form->limitField; ?>
		<button type="submit" class="button"><?php echo JText::_('Filter'); ?></button>
	</p>

<?php $this->_loadTemplate('_list_items'); ?>

	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	<input type="hidden" name="view" value="archive" />
	<input type="hidden" name="option" value="com_content" />
</form>
