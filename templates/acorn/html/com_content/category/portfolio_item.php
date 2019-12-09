<?php
	defined('_JEXEC') or die;

// Create a shortcut for params.
	$params = &$this->item->params;
	$images = json_decode($this->item->images);
	$canEdit = $this->item->params->get('access-edit');
	JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
	$info = $this->item->params->get('info_block_position', 0);

// columns
	$item_cols = round((12 / $this->columns));

// cateogry class
	$cateogry_title = $this->escape($this->item->category_title);
	$category_class = str_replace(' ', '', $cateogry_title);
?>

<div class="col-md-<?php echo $item_cols; ?> portfolio-element <?php echo $category_class; ?>" data-category="transition">

	<div class="portfolio-item">

		<?php if (isset($images->image_intro) && !empty($images->image_intro)) : ?>
				<!-- Article Image -->

				<?php $imgfloat = (empty($images->float_intro)) ? $params->get('float_intro') : $images->float_intro; ?>
				<div class="img-intro-<?php echo htmlspecialchars($imgfloat); ?>">
					<div class="img-wrapper">
						<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>">
							<img
							<?php if ($images->image_intro_caption): echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_intro_caption) . '"';
							endif; ?>
								src="<?php echo htmlspecialchars($images->image_intro); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>">
							<div class="image-backdrop"></div>
							<div class="img-intro-btn"></div>
						</a>
					</div>
				</div>

			<?php endif; ?>

		<?php if ($params->get('show_print_icon') || $params->get('show_email_icon') || $canEdit) : ?>
				<!-- Tooblar -->
			<div class="acorn btn-group pull-right">
					<a class="btn dropdown-toggle" data-toggle="dropdown" href="#" role="button">
						<span class="icon-align-justify"></span>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<?php if ($params->get('show_print_icon')) : ?>
							<li class="print-icon"> <?php echo JHtml::_('icon.print_popup', $this->item, $params); ?> </li>
						<?php endif; ?>
						<?php if ($params->get('show_email_icon')) : ?>
							<li class="email-icon"> <?php echo JHtml::_('icon.email', $this->item, $params); ?> </li>
						<?php endif; ?>
						<?php if ($canEdit) : ?>
							<li class="edit-icon"> <?php echo JHtml::_('icon.edit', $this->item, $params); ?> </li>
						<?php endif; ?>
					</ul>
				</div>

			<?php endif; ?>

		<?php if ($params->get('show_title') || $this->item->state == 0 || ($params->get('show_author') && !empty($this->item->author))) : ?>
				<!-- Article Title -->

				<?php if ($params->get('show_title')) : ?>
					<div class="page-header">
						<h3>
							<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
								<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>"> <?php echo $this->escape($this->item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($this->item->title); ?>
							<?php endif; ?>
						</h3>
					</div>
				<?php endif; ?>

				<?php if ($this->item->state == 0) : ?>
					<!-- Unpublished Label -->
					<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>

			<?php endif; ?>


		<?php if (!$params->get('show_intro')) : ?>
				<?php echo $this->item->event->afterDisplayTitle; ?>
			<?php endif; ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>


		<?php
			if (
					$params->get('show_author') or
					$params->get('show_publish_date') or
					$params->get('show_create_date') or
					$params->get('show_modify_date') or
					$params->get('show_hits') or
					$params->get('show_category')
			) :
				?>

				<!-- Article Info -->
				<dl class="article-info">
					<dt class="article-info-term"></dt>

						<?php if ($params->get('show_author') && !empty($this->item->author)) : ?>
						<dd class="createdby">
							<?php $author = $this->item->author; ?>
							<?php $author = ($this->item->created_by_alias ? $this->item->created_by_alias : $author); ?>
							<?php if (!empty($this->item->contactid) && $params->get('link_author') == true) : ?>
								<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link', JRoute::_('index.php?option=com_contact&view=contact&id=' . $this->item->contactid), $author)); ?>
							<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
						<?php endif; ?>
						</dd>
					<?php endif; ?>

						<?php if ($params->get('show_publish_date')) : ?>
						<dd  class="published">
							<span class="icon-time"></span>
						<?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC3'))); ?>
						</dd>
					<?php endif; ?>

						<?php if ($params->get('show_create_date')) : ?>
						<dd class="create">
							<span class="icon-calendar"></span>
						<?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC3'))); ?>
						</dd>
					<?php endif; ?>

						<?php if ($params->get('show_modify_date')) : ?>
						<dd class="modified">
							<span class="icon-calendar"></span>
						<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC3'))); ?>
						</dd>
					<?php endif; ?>

						<?php if ($params->get('show_hits')) : ?>
						<dd  class="hits">
							<span class="icon-eye-open"></span>
						<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
						</dd>
					<?php endif; ?>

						<?php if ($params->get('show_category')) : ?>
						<dd class="category-name">
							<?php $title = $this->escape($this->item->category_title);
							$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)) . '">' . $title . '</a>';
							?>
							<?php if ($params->get('link_category') && $this->item->catslug) : ?>
								<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
						<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
			<?php endif; ?>
						</dd>
				<?php endif; ?>

				</dl>

			<?php endif; ?>

<?php if ($params->get('show_intro') != 0) : ?>
		<?php echo $this->item->introtext; ?>
			<?php endif; ?>



		<?php
			if ($params->get('show_readmore') && $this->item->readmore) :

				if ($params->get('access-view')) :
					$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
				else :
					$menu      = JFactory::getApplication()->getMenu();
					$active    = $menu->getActive();
					$itemId    = $active->id;
					$link1     = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
					$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
					$link      = new JURI($link1);
					$link->setVar('return', base64_encode($returnURL));
				endif;
				?>

				<p><a class="btn" href="<?php echo $link; ?>">
						<?php
						if (!$params->get('access-view')) :
							echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
						elseif ($readmore = $this->item->alternative_readmore) :
							echo $readmore;
							if ($params->get('show_readmore_title', 0) != 0) :
								echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
							endif;
						elseif ($params->get('show_readmore_title', 0) == 0) :
							echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
						else :
							echo JText::_('COM_CONTENT_READ_MORE');
							echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
						endif;
						?></a></p>

	<?php endif; ?>


	</div>

</div>

<?php echo $this->item->event->afterDisplayContent; ?>
