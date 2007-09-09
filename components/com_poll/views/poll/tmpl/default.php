<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
?>

<?php JHTML::_('stylesheet', 'poll_bars', 'components/com_poll/assets/'); ?>

<form action="index.php" method="post" name="poll" id="poll">
<?php if ($this->params->get( 'show_page_title')) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->poll->title ? $this->poll->title : $this->params->get('page_title'); ?>
</div>
<?php endif; ?>
<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<label for="id">
		<?php echo JText::_('Select Poll'); ?>
		<?php echo $this->lists['polls']; ?>
	</label>
</div>
<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
<?php echo $this->loadTemplate('graph'); ?>
</div>
</form>