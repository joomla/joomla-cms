<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php JHtml::_('stylesheet', 'poll_bars.css', 'components/com_poll/assets/'); ?>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->poll->title ? $this->escape($this->poll->title) : $this->escape($this->params->get('page_title')); ?>
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
