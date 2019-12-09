<?php
	defined('_JEXEC') or die;

	JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$doc = JFactory::getDocument();
$app = JFactory::getApplication();

$templatePath = JURI::root() . 'templates/' . JFactory::getApplication()->getTemplate();

$doc->addScript( $templatePath . '/js/isotope.pkgd.min.js' );
$doc->addScript( $templatePath . '/js/isotope-layout.js' );
?>
<div class="acorn-portfolio <?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
			<div class="headline">
				<h4><?php echo $this->escape($this->params->get('page_heading')); ?></h4>
			</div>
		<?php endif; ?>
	<?php if (!empty($this->children[$this->category->id]) && $this->maxLevel != 0) : ?>
			<div class="portfolio-nav">
				<div class="acorn btn-group pull-right">
					<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
						<?php echo $this->escape($this->params->get('all_text')); ?>
					</a>
					<ul id="filters" class="option-set clearfix dropdown-menu" data-option-key="filter">
						<li class="btn btn-primary selected"><a href="#" data-option-value="*" class="selected"><?php echo $this->escape($this->params->get('all_text')); ?></a></li>
						<?php echo $this->loadTemplate('children'); ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<?php endif; ?>
	<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
			<div class="page-header">
				<h2>
					<?php echo $this->escape($this->params->get('page_subheading')); ?>
					<?php if ($this->params->get('show_category_title')) : ?>
						<span class="subheading-category"><?php echo $this->category->title; ?></span>
					<?php endif; ?>
				</h2>
			</div>
		<?php endif; ?>
	<?php if ($this->params->get('show_tags', 1) && !empty($this->category->tags->itemTags)) : ?>
			<?php $this->category->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
			<?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
		<?php endif; ?>
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
			<div class="category-desc">
				<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
					<img src="<?php echo $this->category->getParams()->get('image'); ?>">
				<?php endif; ?>
				<?php if ($this->params->get('show_description') && $this->category->description) : ?>
					<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
				<?php endif; ?>
				<div class="clr"></div>
			</div>
		<?php endif; ?>
	<div class="clearfix"></div>
	<div class="isotope">
		<div id="isotope-container" class="clearfix">
			<!-- begin portfolio items -->
			<?php if (!empty($this->lead_items)) : ?>
					<?php foreach ($this->lead_items as &$item) : ?>
						<?php $this->item = &$item;
						echo $this->loadTemplate('item'); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php if (!empty($this->intro_items)) : ?>
					<?php foreach ($this->intro_items as &$item) : ?>
						<?php $this->item = &$item;
						echo $this->loadTemplate('item'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
			<!-- end portfolio items -->
		</div>
	</div>
	<div class="clearfix"></div>
		<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
			<div class="pagination">
				<?php if ($this->params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?></p>
			<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
	<?php endif; ?>
</div>
