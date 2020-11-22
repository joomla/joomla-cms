<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Tags\Site\Helper\RouteHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_tags.tags-default');

// Get the user object.
$app = Factory::getApplication();
$user = $app->getIdentity();

// Check if user is allowed to add/edit based on tags permissions.
$canEdit      = $user->authorise('core.edit', 'com_tags');
$canCreate    = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');

// Column sizing
$columns = $this->params->get('tag_columns', 0);
$columnsize = $columns === 0 ? "display: flex;" : "grid-template-columns: repeat(" . $columns . "," . floor(100 / $columns) . "%);";

$n         = count($this->items);
?>

<div class="com-tags__items">
	<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ($this->params->get('filter_field') || $this->params->get('show_pagination_limit')) : ?>
			<?php if ($this->params->get('filter_field')) : ?>
				<div class="com-tags-tags__filter btn-group">
					<label class="filter-search-lbl sr-only" for="filter-search">
						<?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>
					</label>
					<input
						type="text"
						name="filter-search"
						id="filter-search"
						value="<?php echo $this->escape($this->state->get('list.filter')); ?>"
						class="inputbox" onchange="document.adminForm.submit();"
						placeholder="<?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>"
					>
					<span class="input-group-append">
						<button type="submit" name="filter_submit" class="btn btn-primary"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
						<button type="reset" name="filter-clear-button" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
					</span>
				</div>
			<?php endif; ?>
			<?php if ($this->params->get('show_pagination_limit')) : ?>
				<div class="btn-group float-right">
					<label for="limit" class="sr-only">
						<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>

			<input type="hidden" name="limitstart" value="">
			<input type="hidden" name="task" value="">
		<?php endif; ?>
	</form>

	<!-- Clear floated content -->
	<div class="clearfix"></div>

	<?php if ($this->items == false || $n === 0) : ?>
		<div class="alert alert-info">
			<span class="icon-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_TAGS_NO_TAGS'); ?>
		</div>
	<?php else : ?>
		<ul class="com-tags__category category" style="<?php echo $columnsize ?>">
		<?php foreach ($this->items as $i => $item) : ?>
				<li class="tag-list btn border-gray">
					<?php if ((!empty($item->access)) && in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
						<div class="tag-item mb-0">
							<a href="<?php echo Route::_(RouteHelper::getTagRoute($item->id . ':' . $item->alias)); ?>">
								<?php echo $this->escape($item->title); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php if ($this->params->get('all_tags_show_tag_image') && !empty($item->images)) : ?>
						<?php $images = json_decode($item->images); ?>
						<span class="tag-body">
						<?php if (!empty($images->image_intro)) : ?>
							<?php $imgfloat = empty($images->float_intro) ? $this->params->get('float_intro') : $images->float_intro; ?>
							<div class="float-<?php echo htmlspecialchars($imgfloat); ?> item-image">
								<img
									<?php if ($images->image_intro_caption) : ?>
										<?php echo 'class="caption" title="' . htmlspecialchars($images->image_intro_caption) . '"'; ?>
									<?php endif; ?>
									src="<?php echo $images->image_intro; ?>"
									alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>">
							</div>
						<?php endif; ?>
						</span>
					<?php endif; ?>

					<?php if (($this->params->get('all_tags_show_tag_description', 1) && !empty($item->description)) || $this->params->get('all_tags_show_tag_hits')) : ?>
						<div class="caption">
							<?php if ($this->params->get('all_tags_show_tag_description', 1) && !empty($item->description)) : ?>
								<span class="tag-body">
									<?php echo HTMLHelper::_('string.truncate', $item->description, $this->params->get('all_tags_tag_maximum_characters')); ?>
								</span>
							<?php endif; ?>
							<?php if ($this->params->get('all_tags_show_tag_hits')) : ?>
								<span class="list-hits badge badge-info">
									<?php echo Text::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php // Add pagination links ?>
	<?php if (!empty($this->items)) : ?>
		<?php if (($this->params->def('show_pagination', 2) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
			<div class="com-tags__pagination w-100">
				<?php if ($this->params->def('show_pagination_results', 1)) : ?>
					<p class="counter float-right pt-3 pr-2">
						<?php echo $this->pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
