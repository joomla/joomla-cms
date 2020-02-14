<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
?>
<div class="container-fluid blog-featured<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Blog">
<?php if ($this->params->get('show_page_heading') != 0) : ?>
<div class="page-header">
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
</div>
<?php endif; ?>

<?php $leadingcount = 0; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="row items-leading clearfix">
	<?php foreach ($this->lead_items as &$item) : ?>
		<div class="col leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?> clearfix"
			itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</div>
		<?php
			$leadingcount++;
		?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
	$introcount = count($this->intro_items);
	$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>

		<?php
		$key = ($key - $leadingcount) + 1;
		$rowcount = (((int) $key - 1) % (int) $this->columns) + 1;
		$row = $counter / $this->columns;

		if ($rowcount === 1) : ?>

		<div class="row items-row cols-<?php echo (int) $this->columns; ?> <?php echo 'row-' . $row; ?> row">
		<?php endif; ?>
			<div class="col-md-<?php echo round(12 / $this->columns); ?> item column-<?php echo $rowcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>"
				itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
			<?php
					$this->item = &$item;
					echo $this->loadTemplate('item');
			?>
			</div>
			<?php $counter++; ?>

			<?php if (($rowcount == $this->columns) or ($counter == $introcount)) : ?>

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
	<div class="w-100">
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter float-right pt-3 pr-2">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>

</div>
