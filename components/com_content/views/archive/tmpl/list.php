<form id="jForm" action="<?php JURI::resolve('index.php')?>" method="post">
<?php if ($header) : ?>
	<div class="componentheading<?php echo $params->get('pageclass_sfx')?>"><?php echo $header; ?></div>
<?php endif; ?>
	<p>
		<?php if ($params->get('filter')) : ?>
		<?php echo JText::_('Filter').'&nbsp;'; ?>
		<input type="text" name="filter" value="<?php echo $lists['filter'];?>" class="inputbox" onchange="document.jForm.submit();" />
		<?php endif; ?>
		<?php echo $form->monthField; ?>
		<?php echo $form->yearField; ?>
		<?php echo $form->limitField; ?>
		<button type="submit" class="button"><?php echo JText::_('Filter'); ?></button>
	</p>

<?php $this->loadTemplate('items'); ?>

	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	<input type="hidden" name="view" value="archive" />
	<input type="hidden" name="option" value="com_content" />
</form>
