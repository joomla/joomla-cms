<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<ul id="archive-list" style="list-style: none;">
<?php foreach ($this->items as $item) : ?>
	<li class="row<?php echo ($item->odd +1 ); ?>">
		<h4 class="contentheading">
			<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</h4>

		<?php if (($this->params->get('show_section') && $item->sectionid) || ($this->params->get('show_category') && $item->catid)) : ?>
			<div>
			<?php if ($this->params->get('show_section') && $item->sectionid && isset($item->section)) : ?>
				<span>
				<?php if ($this->params->get('link_section')) : ?>
					<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($item->sectionid)).'">'; ?>
				<?php endif; ?>

				<?php echo $this->escape($item->section); ?>

				<?php if ($this->params->get('link_section')) : ?>
					<?php echo '</a>'; ?>
				<?php endif; ?>

				<?php if ($this->params->get('show_category')) : ?>
					<?php echo ' - '; ?>
				<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php if ($this->params->get('show_category') && $item->catid) : ?>
				<span>
				<?php if ($this->params->get('link_category')) : ?>
					<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug, $item->sectionid)).'">'; ?>
				<?php endif; ?>
				<?php echo $this->escape($item->category); ?>
				<?php if ($this->params->get('link_category')) : ?>
					<?php echo '</a>'; ?>
				<?php endif; ?>
				</span>
			<?php endif; ?>
			</div>
		<?php endif; ?>

		<h5 class="metadata">
		<?php if ($this->params->get('show_create_date')) : ?>
			<span class="created-date">
				<?php echo JText::_('Created') .': '.  JHTML::_( 'date', $item->created, JText::_('DATE_FORMAT_LC2')) ?>
			</span>
			<?php endif; ?>
			<?php if ($this->params->get('show_author')) : ?>
			<span class="author">
				<?php echo JText::_('Author').': '; echo $this->escape($item->created_by_alias) ? $this->escape($item->created_by_alias) : $this->escape($item->author); ?>
			</span>
		<?php endif; ?>
		</h5>
		<div class="intro">
			<?php echo substr(strip_tags($item->introtext), 0, 255);  ?>...
		</div>
	</li>
<?php endforeach; ?>
</ul>
<div id="navigation">
	<span><?php echo $this->pagination->getPagesLinks(); ?></span>
	<span><?php echo $this->pagination->getPagesCounter(); ?></span>
</div>
