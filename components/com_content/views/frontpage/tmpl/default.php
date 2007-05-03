<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->params->get('show_page_title')) : ?>
<div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->params->get('page_title'); ?>
</div>
<?php endif; ?>
<table class="blog<?php echo $this->params->get('pageclass_sfx') ?>" cellpadding="0" cellspacing="0">
<?php if (isset($this->frontpage->description)) : ?>
<tr>
	<td valign="top">
	<?php if ($this->params->get('show_description_image') && $this->frontpage->description->image) : ?>
		<img src="<?php echo $this->frontpage->description->link ?>" align="<?php echo $this->frontpage->description->image_position ?>" hspace="6" alt="" />
	<?php endif; ?>
	<?php if ($this->params->get('show_description') && $this->frontpage->description->text) : ?>
		<?php echo $this->frontpage->description->text; ?>
	<?php endif; ?>
	<br/><br/>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('num_leading_articles', 1)) : ?>
<tr>
	<td valign="top">
	<?php for ($i = $this->pagination->limitstart; $i < $this->params->get('num_leading_articles'); $i++) : ?>
		<?php if ($i >= $this->total) : break; endif; ?>
		<div>
		<?php
			$this->item =& $this->getItem($i, $this->params);
			echo $this->loadTemplate('item');
		?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else : $i = $this->pagination->limitstart; endif; ?>

<?php if ($this->params->def('num_intro_articles', 4) && ($i < $this->total)) : ?>
<tr>
	<td valign="top">
		<table width="100%"  cellpadding="0" cellspacing="0">
		<tr>
		<?php
			$divider = '';
			for ($z = 0; $z < $this->params->def('num_columns', 2); $z ++) :
				if ($z > 0) : $divider = " column_separator"; endif; ?>
				<td valign="top" width="<?php echo intval(100 / $this->params->get('num_columns')) ?>%" class="article_column<?php echo $divider ?>">
				<?php for ($y = 0; $y < $this->params->get('num_intro_articles') / $this->params->get('num_columns'); $y ++) :
					if ($i < $this->total) :
						$this->item =& $this->getItem($i, $this->params);
						echo $this->loadTemplate('item');
						$i ++;
					endif;
				endfor; ?>
				</td>
		<?php endfor; ?>
		</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('num_links', 4) && ($i < $this->total)) : ?>
<tr>
	<td valign="top">
		<div class="blog_more<?php echo $this->params->get('pageclass_sfx') ?>">
			<?php
				$this->links = array_splice($this->items, $i);
				echo $this->loadTemplate('links');
			?>
		</div>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<br /><br />
	</td>
</tr>
<?php if ($this->params->def('show_pagination_results', 1)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
<?php endif; ?>
</table>