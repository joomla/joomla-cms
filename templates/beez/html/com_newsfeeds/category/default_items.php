<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ( $this->params->get( 'show_limit' ) ) : ?>
<div class="display">
	<form action="index.php" method="post" name="adminForm">
		<label for="limit"><?php echo JText::_( 'Display Num' ); ?>&nbsp;</label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</form>
</div>
<?php endif; ?>


<table class="newsfeeds">

	<?php if ( $this->params->get( 'show_headings' ) ) : ?>
	<tr>

		<th class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="5" id="num">
			<?php echo JText::_( 'Num' ); ?>
		</th>

		<?php if ( $this->params->get( 'show_name' ) ) : ?>
		<th width="90%" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" id="name">
			<?php echo JText::_( 'Feed Name' ); ?>
		</th>
		<?php endif; ?>

		<?php if ( $this->params->get( 'show_articles' ) ) : ?>
		<th width="10%" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" nowrap="nowrap" id="num_a">
			<?php echo JText::_('Num Articles'); ?>
		</th>
		<?php endif; ?>

	</tr>
	<?php endif; ?>

	<?php foreach ( $this->items as $item ) : ?>
	<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">

		<td align="center" width="5" headers="num">
			<?php echo $item->count + 1; ?>
		</td>
		
		<td width="90%" headers="name">
			<a href="<?php echo $item->link; ?>" class="category<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<?php echo $item->name; ?>
			</a>
		</td>
	
		<?php if ( $this->params->get( 'show_articles' ) ) : ?>
		<td width="10%" headers="num_a"><?php echo $item->numarticles; ?></td>
		<?php endif; ?>

	</tr>
	<?php endforeach; ?>

</table>

<p class="counter">
	<?php echo $this->pagination->getPagesCounter(); ?>
</p>
<?php echo $this->pagination->getPagesLinks(); ?>
