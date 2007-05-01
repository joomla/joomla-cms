<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script type = "text/javascript">
<!--
	var link = document.createElement('link');
	link.setAttribute('href', 'components/com_poll/assets/poll_bars.css');
	link.setAttribute('rel', 'stylesheet');
	link.setAttribute('type', 'text/css');
	var head = document.getElementsByTagName('head').item(0);
	head.appendChild(link);
//-->
</script>
<form action="index.php" method="post" name="poll" id="poll">
<?php if ($this->params->get( 'show_page_title')) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->poll->title; ?>
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