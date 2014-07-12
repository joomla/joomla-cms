<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$parent = $displayData->parent;
$items = $displayData->items;
$params = $displayData->params;
$extension = $displayData->extension;
$maxLevelcat = $displayData->maxLevelcat;

$extensionName = substr($extension, 4);
$routerClass = $extensionName . 'HelperRoute';

$class = ' class="first"';
if (count($items[$parent->id]) > 0 && $maxLevelcat != 0) :
?>
	<?php foreach($items[$parent->id] as $id => $item) : ?>
		<?php
		if ($params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) :
			if (!isset($items[$parent->id][$id + 1]))
			{
				$class = ' class="last"';
			}
			?>
			<div <?php echo $class; ?> >
			<?php $class = ''; ?>
				<h3 class="page-header item-title">
					<?php if (class_exists($routerClass)) : ?>
						<?php $route = $routerClass::getCategoryRoute($item->id); ?>
					<?php else : ?>
						<?php // We can't find a routing class to get the URL so just go ahead without a link ?>
						<?php $route = '#'; ?>
					<?php endif; ?>
					<a href="<?php echo JRoute::_($route); ?>">
					<?php // We don't have the JViewLegacy::escape() function available so use default htmlspecialchars ?>
					<?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?></a>
					<?php
						// The second param check here is for newsfeeds which has a different name for the same parameter
						//@todo fix so it has a consistent param name
					?>
					<?php if ($params->get('show_cat_num_articles_cat') == 1 || $params->get('show_cat_items_cat') == 1) : ?>
						<span class="badge badge-info tip hasTooltip" title="<?php echo JHtml::tooltipText('COM_' . strtoupper($extensionName) . '_NUM_ITEMS'); ?>">
							<?php echo $item->numitems; ?>
						</span>
					<?php endif; ?>
					<?php if (count($item->getChildren()) > 0) : ?>
						<a href="#category-<?php echo $item->id;?>" data-toggle="collapse" data-toggle="button" class="btn btn-mini pull-right"><span class="icon-plus"></span></a>
					<?php endif;?>
				</h3>
				<?php if (isset($item->event->afterDisplayTitle)) : ?>
					<?php echo $item->event->afterDisplayTitle; ?>
				<?php endif; ?>
				<?php if ($params->get('show_subcat_desc_cat') == 1) :?>
					<?php if ($item->description) : ?>
						<div class="category-desc">
							<?php echo JHtml::_('content.prepare', $item->description, '', $extension . '.categories'); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if (isset($item->event->beforeDisplayContent)) : ?>
					<?php echo $item->event->beforeDisplayContent; ?>
				<?php endif; ?>
				<?php if (count($item->getChildren()) > 0) :?>
					<div class="collapse fade" id="category-<?php echo $item->id;?>">
						<?php
						$items[$item->id] = $item->getChildren();
						$parent = $item;
						$maxLevelcat--;

						$childDisplayData = new stdClass;
						$childDisplayData->parent = $parent;
						$childDisplayData->extension = $displayData->extension;
						$childDisplayData->items = $items;
						$childDisplayData->params = $params;
						$childDisplayData->maxLevelcat = $maxLevelcat;
						echo JLayoutHelper::render('joomla.content.categories_default_items', $childDisplayData);

						$parent = $item->getParent();
						$maxLevelcat++;
						?>
					</div>
				<?php endif; ?>
				<?php if (isset($item->event->afterDisplayContent)) : ?>
					<?php echo $item->event->afterDisplayContent; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
