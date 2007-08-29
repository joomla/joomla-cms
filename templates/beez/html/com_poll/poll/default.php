<?php
/**
 * @version $Id$
 */
defined('_JEXEC') or die('Restricted access');

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
	$templateParams = new JParameter($content);
} else {
	$templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$ptLevel = $templateParams->get('pageTitleHeaderLevel', '1');
$headerOpen = '<h' . $ptLevel . ' class="componentheading' . $this->params->get( 'pageclass_sfx' ).'">';
$headerClose = '</h' . $ptLevel . '>';

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

<?php echo $headerOpen; ?>
	<?php if ($this->params->get( 'title' )) : ?>
	<?php echo $this->poll->title ? $this->poll->title : JText::_( 'Select Poll' ); ?>
	<?php else : ?>
	<?php echo JText::_('Poll'); ?>
	<?php endif; ?>
<?php echo $headerClose; ?>

<div class="poll<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<form action="index.php" method="post" name="poll" id="poll">
		<label for="id">
			<?php echo JText::_( 'Select Poll' ); ?>&nbsp;<?php echo $this->lists['polls']; ?>
		</label>
	</form>
	<?php if (count($this->votes)) : ?>
	<?php echo $this->loadTemplate( 'graph' ); ?>
	<?php endif; ?>
</div>
