<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->params->get('page_title'); ?>
</h1>
<?php endif; ?>

<div class="blog<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php $i = $this->pagination->limitstart;
	$rowcount = $this->params->def('num_leading_articles', 1);
	for ($y = 0; $y < $rowcount && $i < $this->total; $y++, $i++) : ?>
		<div class="leading<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php $this->item =& $this->getItem($i, $this->params);
			echo $this->loadTemplate('item'); ?>
		</div>
		<span class="leading_separator<?php echo $this->params->get('pageclass_sfx'); ?>">&nbsp;</span>
	<?php endfor; ?>

	<?php $introcount = $this->params->def('num_intro_articles', 4);
	if ($introcount) :
		$colcount = $this->params->def('num_columns', 2);
		if ($colcount == 0) :
			$colcount = 1;
		endif;
		$rowcount = (int) $introcount / $colcount;
		$ii = 0;
		for ($y = 0; $y < $rowcount && $i < $this->total; $y++) : ?>
			<div class="article_row<?php echo $this->params->get('pageclass_sfx'); ?>">
				<?php for ($z = 0; $z < $colcount && $ii < $introcount && $i < $this->total; $z++, $i++, $ii++) : ?>
					<div class="article_column column<?php echo $z + 1; ?> cols<?php echo $colcount; ?>" >
						<?php $this->item =& $this->getItem($i, $this->params);
						echo $this->loadTemplate('item'); ?>
					</div>
					<span class="article_separator">&nbsp;</span>
				<?php endfor; ?>
				<span class="row_separator<?php echo $this->params->get('pageclass_sfx'); ?>">&nbsp;</span>
			</div>
		<?php endfor;
	endif; ?>

	<?php $numlinks = $this->params->def('num_links', 4);
	if ($numlinks && $i < $this->total) : ?>
	<div class="blog_more<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php $this->links = array_slice($this->items, $i - $this->pagination->limitstart, $i - $this->pagination->limitstart + $numlinks);
		echo $this->loadTemplate('links'); ?>
	</div>
	<?php endif; ?>

	<?php if ($this->params->def('show_pagination_results', 1)) : ?>
	<p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</p>
	<?php endif; ?>

	<?php if ($this->params->def('show_pagination', 2)) :
		echo $this->pagination->getPagesLinks();
	endif; ?>

</div>
