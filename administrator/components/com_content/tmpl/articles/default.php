<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Button\ActionButton;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_CATEGORY')));
HTMLHelper::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_ACCESS')));
HTMLHelper::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_AUTHOR')));

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$columns   = 10;

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
elseif (strpos($listOrder, 'modified') !== false)
{
	$orderingColumn = 'modified';
}
else
{
	$orderingColumn = 'created';
}

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_content&task=articles.saveOrderAjax&tmpl=component' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$assoc = Associations::isEnabled();

// Configure content state button renderer.
$publishedButton = new PublishedButton(['task_prefix' => 'articles.', 'checkbox_name' => 'cb']);

// Configure featured button renderer.
$featuredButton = (new ActionButton(['tip_title' => 'JGLOBAL_TOGGLE_FEATURED']))
	->addState(0, 'articles.featured', 'unfeatured', 'COM_CONTENT_UNFEATURED')
	->addState(1, 'articles.unfeatured', 'featured', 'COM_CONTENT_FEATURED');
?>

<form action="<?php echo Route::_('index.php?option=com_content&view=articles'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) : ?>
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<?php endif; ?>
		<div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<?php
			// Search tools bar
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="articleList">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th style="min-width:100px" class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th style="width:15%" class="nowrap d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'JALIAS', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo JHtml::_('searchtools.sort', 'JCATEGORY', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<?php if ($assoc) : ?>
									<?php $columns++; ?>
									<th style="width:5%" class="nowrap d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_CONTENT_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort',  'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
								</th>
								<?php if (Multilanguage::isEnabled()) : ?>
									<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CONTENT_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
								</th>
								<th style="width:3%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
								</th>
								<?php if ($this->vote) : ?>
									<?php $columns++; ?>
									<th style="width:3%" class="nowrap d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_VOTES', 'rating_count', $listDirn, $listOrder); ?>
									</th>
									<?php $columns++; ?>
									<th style="width:3%" class="nowrap d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_RATINGS', 'rating', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th style="width:3%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="<?php echo $columns; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
							</tr>
						</tfoot>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$item->max_ordering = 0;
							$ordering   = ($listOrder == 'a.ordering');
							$canCreate  = $user->authorise('core.create',     'com_content.category.' . $item->catid);
							$canEdit    = $user->authorise('core.edit',       'com_content.article.' . $item->id);
							$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canEditOwn = $user->authorise('core.edit.own',   'com_content.article.' . $item->id) && $item->created_by == $userId;
							$canChange  = $user->authorise('core.edit.state', 'com_content.article.' . $item->id) && $canCheckin;
							?>
							<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->catid; ?>">
								<td class="order nowrap text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-menu" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center">
									<div class="btn-group">
										<?php echo $publishedButton->render($item->state, $i, ['disabled' => !$canChange], $item->publish_up, $item->publish_down); ?>
										<?php echo $featuredButton->render($item->featured, $i, ['disabled' => !$canChange]); ?>
									</div>
								</td>
								<td class="has-context">
									<div class="break-word">
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit || $canEditOwn) : ?>
											<?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pencil mr-2" aria-hidden="true"></span>'; ?>
											<a class="hasTooltip text-dark" href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
												<?php echo $editIcon; ?><?php echo $this->escape($item->title); ?></a>
										<?php else : ?>
											<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
										<?php endif; ?>
									</div>
								</td>
								<td class="text-secondary d-none d-md-table-cell">
									<?php echo $this->escape($item->alias); ?>
								</td>
								<td class="text-secondary d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->category_title); ?>
								</td>
								<td class="small d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<?php if ($assoc) : ?>
								<td class="d-none d-md-table-cell text-center">
									<?php if ($item->association) : ?>
										<?php echo HTMLHelper::_('contentadministrator.association', $item->id); ?>
									<?php endif; ?>
								</td>
								<?php endif; ?>
								<td class="small d-none d-md-table-cell text-center">
									<?php if ((int) $item->created_by != 0) : ?>
										<?php if ($item->created_by_alias) : ?>
                                            <a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo Text::_('JAUTHOR'); ?>">
												<?php echo $this->escape($item->author_name); ?></a>
                                            <div class="smallsub"><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
										<?php else : ?>
                                            <a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo Text::_('JAUTHOR'); ?>">
												<?php echo $this->escape($item->author_name); ?></a>
										<?php endif; ?>
									<?php else : ?>
										<?php if ($item->created_by_alias) : ?>
											<?php echo Text::_('JNONE'); ?>
                                            <div class="smallsub"><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
										<?php else : ?>
											<?php echo Text::_('JNONE'); ?>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<?php if (Multilanguage::isEnabled()) : ?>
									<td class="small d-none d-md-table-cell text-center">
										<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
									</td>
								<?php endif; ?>
								<td class="nowrap small d-none d-md-table-cell text-center">
									<?php
									$date = $item->{$orderingColumn};
									echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
									?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<span class="badge badge-info">
										<?php echo (int) $item->hits; ?>
									</span>
								</td>
								<?php if ($this->vote) : ?>
									<td class="d-none d-md-table-cell text-center">
										<span class="badge badge-success">
										<?php echo (int) $item->rating_count; ?>
										</span>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<span class="badge badge-warning">
										<?php echo (int) $item->rating; ?>
										</span>
									</td>
								<?php endif; ?>
								<td class="d-none d-md-table-cell text-center">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_content')
						&& $user->authorise('core.edit', 'com_content')
						&& $user->authorise('core.edit.state', 'com_content')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title'  => Text::_('COM_CONTENT_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer'),
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
