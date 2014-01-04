<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');

?>

<section class="blog-featured<?php echo $this->pageclass_sfx;?>">
<?php if ( $this->params->get('show_page_heading') != 0) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>
<?php $leadingcount = 0; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="items-leading">
	<?php foreach ($this->lead_items as &$item) : ?>
		<article class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</article>
		<?php
			$leadingcount++;
		?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
	$introcount = (count($this->intro_items));
	$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>

	<?php
		$key = ($key - $leadingcount) + 1;
		$rowcount = (((int) $key - 1) % (int) $this->columns) + 1;
		$row = $counter / $this->columns;

		if ($rowcount == 1) : ?>

			<div class="items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row; ?>">
		<?php endif; ?>
		<article class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished"' : null; ?>">
			<?php
					$this->item = &$item;
					echo $this->loadTemplate('item');
			?>
		</article>
		<?php $counter++; ?>
			<?php if (($rowcount == $this->columns) or ($counter == $introcount)) : ?>
				<span class="row-separator"></span>
				</div>

			<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
	<div class="items-more">
	<?php echo $this->loadTemplate('links'); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php  endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>
</section>


