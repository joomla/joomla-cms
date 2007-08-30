<?php
/**
 * @version $Id$
 */
defined('_JEXEC') or die('Restricted access');

?>

<script type = "text/javascript">
//<![CDATA[
	var link = document.createElement('link');
	link.setAttribute('href', 'components/com_poll/assets/poll_bars.css');
	link.setAttribute('rel', 'stylesheet');
	link.setAttribute('type', 'text/css');
	var head = document.getElementsByTagName('head').item(0);
	head.appendChild(link);
//]]>
</script>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->poll->title ? $this->poll->title : $this->params->get('page_title'); ?>
</h1>
<?php endif; ?>

<div class="poll<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<form action="index.php" method="post" name="poll" id="poll">
		<label for="id">
			<?php echo JText::_( 'Select Poll' ); ?>&nbsp;<?php echo $this->lists['polls']; ?>
		</label>
	</form>
	<?php if (count($this->votes)) :
		echo $this->loadTemplate( 'graph' );
	endif; ?>
</div>
