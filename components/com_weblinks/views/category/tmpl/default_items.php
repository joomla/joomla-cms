<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.framework');

// Create a shortcut for params.
$params = &$this->item->params;

// Get the user object.
$user = JFactory::getUser();

// Check if user is allowed to add/edit based on weblinks permissinos.
$canEdit = $user->authorise('core.edit', 'com_weblinks.category.' . $this->category->id);
$canCreate = $user->authorise('core.create', 'com_weblinks');
$canEditState = $user->authorise('core.edit.state', 'com_weblinks');

$n = count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_WEBLINKS_NO_WEBLINKS'); ?></p>
<?php else : ?>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit')) :?>
	<fieldset class="filters btn-toolbar">
		<?php if ($this->params->get('filter_field') != 'hide') :?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_WEBLINKS_FILTER_LABEL') . '&#160;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_WEBLINKS_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_WEBLINKS_FILTER_SEARCH_DESC'); ?>" />
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>
	</fieldset>
	<?php endif; ?>
		<ul class="category list-striped list-condensed">

			<?php foreach ($this->items as $i => $item) : ?>
				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<?php if ($this->items[$i]->state == 0) : ?>
						<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else: ?>
						<li class="cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>
					<?php if ($this->params->get('show_link_hits', 1)) : ?>
						<span class="list-hits badge badge-info pull-right">
							<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
						</span>
					<?php endif; ?>

					<?php if ($canEdit) : ?>
						<span class="list-edit pull-left width-50">
							<?php echo JHtml::_('icon.edit', $item, $params); ?>
						</span>
					<?php endif; ?>

					<div class="list-title">
						<?php if ($this->params->get('icons', 1) == 0) : ?>
							 <?php echo JText::_('COM_WEBLINKS_LINK'); ?>
						<?php elseif ($this->params->get('icons', 1) == 1) : ?>
							<?php if (!$this->params->get('link_icons')) : ?>
								<?php echo JHtml::_('image', 'system/weblink.png', JText::_('COM_WEBLINKS_LINK'), null, true); ?>
							<?php else: ?>
								<?php echo '<img src="' . $this->params->get('link_icons') . '" alt="' . JText::_('COM_WEBLINKS_LINK') . '" />'; ?>
							<?php endif; ?>
						<?php endif; ?>
						<?php
							// Compute the correct link
							$menuclass = 'category' . $this->pageclass_sfx;
							$link = $item->link;
							$width	= $item->params->get('width');
							$height	= $item->params->get('height');
							if ($width == null || $height == null)
							{
								$width	= 600;
								$height	= 500;
							}
							if ($this->items[$i]->state == 0) : ?>
								<span class="label label-warning">Unpublished</span>
							<?php endif; ?>

							<?php switch ($item->params->get('target', $this->params->get('target')))
							{
								case 1:
									// Open in a new window
									echo '<a href="' . $link . '" target="_blank" class="' . $menuclass . '" rel="nofollow">' .
										$this->escape($item->title) . '</a>';
									break;

								case 2:
									// Open in a popup window
									$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' . $this->escape($width) . ',height=' . $this->escape($height) . '';
									echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '" . $attribs . "'); return false;\">" .
										$this->escape($item->title) . '</a>';
									break;
								case 3:
									// Open in a modal window
									JHtml::_('behavior.modal', 'a.modal');
									echo '<a class="modal" href="' . $link . '"  rel="{handler: \'iframe\', size: {x:' . $this->escape($width) . ', y:' . $this->escape($height) . '}}">' .
										$this->escape($item->title) . ' </a>';
									break;

								default:
									// Open in parent window
									echo '<a href="' . $link . '" class="' . $menuclass . '" rel="nofollow">' .
										$this->escape($item->title) . ' </a>';
									break;
							}
						?>
						</div>
						<?php $tagsData = $item->tags->getItemTags('com_weblinks.weblink', $item->id); ?>
						<?php if ($this->params->get('show_tags', 1)) : ?>
							<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
							<?php echo $this->item->tagLayout->render($tagsData); ?>
						<?php endif; ?>

						<?php if (($this->params->get('show_link_description')) and ($item->description != '')) : ?>
						<?php $images = json_decode($item->images); ?>
						<?php  if (isset($images->image_first) and !empty($images->image_first)) : ?>
						<?php $imgfloat = (empty($images->float_first)) ? $this->params->get('float_first') : $images->float_first; ?>
						<div class="img-intro-<?php echo htmlspecialchars($imgfloat); ?>"> <img
							<?php if ($images->image_first_caption):
								echo 'class="caption"'.' title="' .htmlspecialchars($images->image_first_caption) .'"';
							endif; ?>
							src="<?php echo htmlspecialchars($images->image_first); ?>" alt="<?php echo htmlspecialchars($images->image_first_alt); ?>"/> </div>
						<?php endif; ?>
						<?php  if (isset($images->image_second) and !empty($images->image_second)) : ?>
						<?php $imgfloat = (empty($images->float_second)) ? $this->params->get('float_second') : $images->float_second; ?>
						<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image"> <img
						<?php if ($images->image_second_caption):
							echo 'class="caption"'.' title="' .htmlspecialchars($images->image_second_caption) .'"';
						endif; ?>
						src="<?php echo htmlspecialchars($images->image_second); ?>" alt="<?php echo htmlspecialchars($images->image_second_alt); ?>"/> </div>
						<?php endif; ?>

						<?php echo $item->description; ?>
						<?php endif; ?>

						</li>
				<?php endif;?>
			<?php endforeach; ?>
		</ul>

		<?php // Code to add a link to submit a weblink. ?>
		<?php /* if ($canCreate) : // TODO This is not working due to some problem in the router, I think. Ref issue #23685 ?>
			<?php echo JHtml::_('icon.create', $item, $item->params); ?>
		<?php  endif; */ ?>
		<?php if ($this->params->get('show_pagination')) : ?>
		 <div class="pagination">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
				<p class="counter">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</p>
			<?php endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php endif; ?>
	</form>
<?php endif; ?>
