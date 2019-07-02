<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR')));
JHtml::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')));
JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'fp.ordering';
$columns   = 10;

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
else
{
	$orderingColumn = 'created';
}

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_content&task=featured.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_content&view=featured'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<?php
			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped" id="articleList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'fp.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width:55px" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_CONTENT_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
						</th>
						<?php if ($this->vote) : ?>
							<?php $columns++; ?>
							<th width="1%" class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_VOTES', 'rating_count', $listDirn, $listOrder); ?>
							</th>
							<?php $columns++; ?>
							<th width="1%" class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_RATINGS', 'rating', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo $columns; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php $count = count($this->items); ?>
					<?php foreach ($this->items as $i => $item) :
						$item->max_ordering = 0;
						$ordering         = ($listOrder == 'fp.ordering');
						$assetId          = 'com_content.article.' . $item->id;
						$canCreate        = $user->authorise('core.create', 'com_content.category.' . $item->catid);
						$canEdit          = $user->authorise('core.edit', 'com_content.article.' . $item->id);
						$canCheckin       = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canChange        = $user->authorise('core.edit.state', 'com_content.article.' . $item->id) && $canCheckin;
						$canEditCat       = $user->authorise('core.edit',       'com_content.category.' . $item->catid);
						$canEditOwnCat    = $user->authorise('core.edit.own',   'com_content.category.' . $item->catid) && $item->category_uid == $userId;
						$canEditParCat    = $user->authorise('core.edit',       'com_content.category.' . $item->parent_category_id);
						$canEditOwnParCat = $user->authorise('core.edit.own',   'com_content.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';

								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
								<span class="icon-menu" aria-hidden="true"></span>
							</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'articles.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									<?php echo JHtml::_('contentadministrator.featured', $item->featured, $i, $canChange); ?>
									<?php // Create dropdown items and render the dropdown list.
									if ($canChange)
									{
										JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'articles');
										JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'articles');
										echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
									}
									?>
								</div>
							</td>
							<td class="has-context">
								<div class="pull-left break-word">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_content&task=article.edit&return=featured&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
									<span class="small break-word">
									<?php if (empty($item->note)) : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									<?php else : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
									<?php endif; ?>
									</span>
									<div class="small">
										<?php
										$ParentCatUrl = JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->parent_category_id . '&extension=com_content');
										$CurrentCatUrl = JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->catid . '&extension=com_content');
										$EditCatTxt = JText::_('JACTION_EDIT') . ' ' . JText::_('JCATEGORY');

										echo JText::_('JCATEGORY') . ': ';

										if ($item->category_level != '1') :
											if ($item->parent_category_level != '1') :
												echo ' &#187; ';
											endif;
										endif;

										if (JFactory::getLanguage()->isRtl())
										{
											if ($canEditCat || $canEditOwnCat) :
												echo '<a class="hasTooltip" href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
											endif;
											echo $this->escape($item->category_title);
											if ($canEditCat || $canEditOwnCat) :
												echo '</a>';
											endif;

											if ($item->category_level != '1') :
												echo ' &#171; ';
												if ($canEditParCat || $canEditOwnParCat) :
													echo '<a class="hasTooltip" href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
												endif;
												echo $this->escape($item->parent_category_title);
												if ($canEditParCat || $canEditOwnParCat) :
													echo '</a>';
												endif;
											endif;
										}
										else
										{
											if ($item->category_level != '1') :
												if ($canEditParCat || $canEditOwnParCat) :
													echo '<a class="hasTooltip" href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
												endif;
												echo $this->escape($item->parent_category_title);
												if ($canEditParCat || $canEditOwnParCat) :
													echo '</a>';
												endif;
												echo ' &#187; ';
											endif;
											if ($canEditCat || $canEditOwnCat) :
												echo '<a class="hasTooltip" href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
											endif;
											echo $this->escape($item->category_title);
											if ($canEditCat || $canEditOwnCat) :
												echo '</a>';
											endif;
										}
										?>
									</div>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="small hidden-phone">
								<?php if ((int) $item->created_by != 0) : ?>
									<?php if ($item->created_by_alias) : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?></a>
										<div class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
									<?php else : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?></a>
									<?php endif; ?>
								<?php else : ?>
									<?php if ($item->created_by_alias) : ?>
										<?php echo JText::_('JNONE'); ?>
										<div class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
									<?php else : ?>
										<?php echo JText::_('JNONE'); ?>
									<?php endif; ?>
								<?php endif; ?>
							</td>
							<td class="small hidden-phone">
								<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php
								$date = $item->{$orderingColumn};
								echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
								?>
							</td>
							<td class="center hidden-phone">
								<span class="badge badge-info">
								<?php echo (int) $item->hits; ?>
								</span>
							</td>
							<?php if ($this->vote) : ?>
								<td class="hidden-phone">
									<span class="badge badge-success" >
									<?php echo (int) $item->rating_count; ?>
									</span>
								</td>
								<td class="hidden-phone">
									<span class="badge badge-warning" >
									<?php echo (int) $item->rating; ?>
									</span>
								</td>
							<?php endif; ?>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="featured" value="1" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
