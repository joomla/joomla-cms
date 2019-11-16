<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\AssociationHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;

// Create some shortcuts.
$n          = count($this->items);
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$langFilter = false;

// Tags filtering based on language filter
if (($this->params->get('filter_field') === 'tag') && (Multilanguage::isEnabled()))
{
	$tagfilter = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter');

	switch ($tagfilter)
	{
		case 'current_language' :
			$langFilter = Factory::getApplication()->getLanguage()->getTag();
			break;

		case 'all' :
			$langFilter = false;
			break;

		default :
			$langFilter = $tagfilter;
	}
}

// Check for at least one editable article
$isEditable = false;

if (!empty($this->items))
{
	foreach ($this->items as $article)
	{
		if ($article->params->get('access-edit'))
		{
			$isEditable = true;
			break;
		}
	}
}
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="com-content-category__articles form-inline">

<?php if ($this->params->get('filter_field') !== 'hide' || $this->params->get('show_pagination_limit')) : ?>
	<fieldset class="com-content-category__filters filters btn-toolbar clearfix">
		<legend class="hidden-xs-up"><?php echo Text::_('COM_CONTENT_FORM_FILTER_LEGEND'); ?></legend>
		<?php if ($this->params->get('filter_field') !== 'hide') : ?>
			<div class="btn-group">
				<?php if ($this->params->get('filter_field') === 'tag') : ?>
					<select name="filter_tag" id="filter_tag" onchange="document.adminForm.submit();" >
						<option value=""><?php echo Text::_('JOPTION_SELECT_TAG'); ?></option>
						<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.options', array('filter.published' => array(1), 'filter.language' => $langFilter), true), 'value', 'text', $this->state->get('filter.tag')); ?>
					</select>
				<?php elseif ($this->params->get('filter_field') === 'month') : ?>
					<select name="filter-search" id="filter-search" onchange="document.adminForm.submit();">
						<option value=""><?php echo Text::_('JOPTION_SELECT_MONTH'); ?></option>
						<?php echo HtmlHelper::_('select.options', HtmlHelper::_('content.months', $this->state), 'value', 'text', $this->state->get('list.filter')); ?>
					</select>
				<?php else : ?>
					<label class="filter-search-lbl sr-only" for="filter-search">
						<?php echo Text::_('COM_CONTENT_' . $this->params->get('filter_field') . '_FILTER_LABEL') . '&#160;'; ?>
					</label>
					<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo Text::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo Text::_('COM_CONTENT_' . $this->params->get('filter_field') . '_FILTER_LABEL'); ?>">
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="com-content-category__pagination btn-group float-right">
				<label for="limit" class="sr-only">
					<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>

		<input type="hidden" name="filter_order" value="">
		<input type="hidden" name="filter_order_Dir" value="">
		<input type="hidden" name="limitstart" value="">
		<input type="hidden" name="task" value="">
	</fieldset>

	<div class="com-content-category__filter-submit control-group hidden-xs-up float-right">
		<div class="controls">
			<button type="submit" name="filter_submit" class="btn btn-primary"><?php echo Text::_('COM_CONTENT_FORM_FILTER_SUBMIT'); ?></button>
		</div>
	</div>
<?php endif; ?>

<?php if (empty($this->items)) : ?>
	<?php if ($this->params->get('show_no_articles', 1)) : ?>
		<p class="com-content-category__no-articles"><?php echo Text::_('COM_CONTENT_NO_ARTICLES'); ?></p>
	<?php endif; ?>
<?php else : ?>

	<table class="com-content-category__table category table table-striped table-bordered table-hover">
		<?php
		$headerTitle    = '';
		$headerDate     = '';
		$headerAuthor   = '';
		$headerHits     = '';
		$headerVotes    = '';
		$headerRatings  = '';
		$headerEdit     = '';
		?>
		<?php if ($this->params->get('show_headings')) : ?>
			<?php
			$headerTitle    = 'headers="categorylist_header_title"';
			$headerDate     = 'headers="categorylist_header_date"';
			$headerAuthor   = 'headers="categorylist_header_author"';
			$headerHits     = 'headers="categorylist_header_hits"';
			$headerVotes    = 'headers="categorylist_header_votes"';
			$headerRatings  = 'headers="categorylist_header_ratings"';
			$headerEdit     = 'headers="categorylist_header_edit"';
			?>
			<thead>
			<tr>
				<th scope="col" id="categorylist_header_title">
					<?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
				</th>
				<?php if ($date = $this->params->get('list_show_date')) : ?>
					<th scope="col" id="categorylist_header_date">
						<?php if ($date === 'created') : ?>
							<?php echo HTMLHelper::_('grid.sort', 'COM_CONTENT_' . $date . '_DATE', 'a.created', $listDirn, $listOrder); ?>
						<?php elseif ($date === 'modified') : ?>
							<?php echo HTMLHelper::_('grid.sort', 'COM_CONTENT_' . $date . '_DATE', 'a.modified', $listDirn, $listOrder); ?>
						<?php elseif ($date === 'published') : ?>
							<?php echo HTMLHelper::_('grid.sort', 'COM_CONTENT_' . $date . '_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
						<?php endif; ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_author')) : ?>
					<th scope="col" id="categorylist_header_author">
						<?php echo HTMLHelper::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_hits')) : ?>
					<th scope="col" id="categorylist_header_hits">
						<?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_votes', 0) && $this->vote) : ?>
					<th scope="col" id="categorylist_header_votes">
						<?php echo HTMLHelper::_('grid.sort', 'COM_CONTENT_VOTES', 'rating_count', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_ratings', 0) && $this->vote) : ?>
					<th scope="col" id="categorylist_header_ratings">
						<?php echo HTMLHelper::_('grid.sort', 'COM_CONTENT_RATINGS', 'rating', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($isEditable) : ?>
					<th scope="col" id="categorylist_header_edit"><?php echo Text::_('COM_CONTENT_EDIT_ITEM'); ?></th>
				<?php endif; ?>
			</tr>
			</thead>
		<?php endif; ?>
		<tbody>
		<?php foreach ($this->items as $i => $article) : ?>
			<?php if ($this->items[$i]->state == ContentComponent::CONDITION_UNPUBLISHED) : ?>
				<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
			<?php else : ?>
				<tr class="cat-list-row<?php echo $i % 2; ?>" >
			<?php endif; ?>
			<td headers="categorylist_header_title" class="list-title">
				<?php if (in_array($article->access, $this->user->getAuthorisedViewLevels())) : ?>
					<a href="<?php echo Route::_(RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language)); ?>">
						<?php echo $this->escape($article->title); ?>
					</a>
					<?php if (Associations::isEnabled() && $this->params->get('show_associations')) : ?>
						<?php $associations = AssociationHelper::displayAssociations($article->id); ?>
						<?php foreach ($associations as $association) : ?>
							<?php if ($this->params->get('flags', 1) && $association['language']->image) : ?>
								<?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
								&nbsp;<a href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>&nbsp;
							<?php else : ?>
								<?php $class = 'badge badge-secondary badge-' . $association['language']->sef; ?>
								&nbsp;<a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo strtoupper($association['language']->sef); ?></a>&nbsp;
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php else : ?>
					<?php
					echo $this->escape($article->title) . ' : ';
					$itemId = Factory::getApplication()->getMenu()->getActive()->id;
					$link   = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
					$link->setVar('return', base64_encode(RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language)));
					?>
					<a href="<?php echo $link; ?>" class="register">
						<?php echo Text::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?>
					</a>
					<?php if (Associations::isEnabled() && $this->params->get('show_associations')) : ?>
						<?php $associations = AssociationHelper::displayAssociations($article->id); ?>
						<?php foreach ($associations as $association) : ?>
							<?php if ($this->params->get('flags', 1)) : ?>
								<?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
								&nbsp;<a href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>&nbsp;
							<?php else : ?>
								<?php $class = 'badge badge-secondary badge-' . $association['language']->sef; ?>
								&nbsp;<a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo strtoupper($association['language']->sef); ?></a>&nbsp;
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($article->state == ContentComponent::CONDITION_UNPUBLISHED) : ?>
					<span class="list-published badge badge-warning">
						<?php echo Text::_('JUNPUBLISHED'); ?>
					</span>
				<?php endif; ?>
				<?php if (strtotime($article->publish_up) > strtotime(Factory::getDate())) : ?>
					<span class="list-published badge badge-warning">
						<?php echo Text::_('JNOTPUBLISHEDYET'); ?>
					</span>
				<?php endif; ?>
				<?php if (!is_null($article->publish_down) && strtotime($article->publish_down) < strtotime(Factory::getDate())) : ?>
					<span class="list-published badge badge-warning">
						<?php echo Text::_('JEXPIRED'); ?>
					</span>
				<?php endif; ?>
			</td>
			<?php if ($this->params->get('list_show_date')) : ?>
				<td headers="categorylist_header_date" class="list-date small">
					<?php
					echo HTMLHelper::_(
						'date', $article->displayDate,
						$this->escape($this->params->get('date_format', Text::_('DATE_FORMAT_LC3')))
					); ?>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_author', 1)) : ?>
				<td headers="categorylist_header_author" class="list-author">
					<?php if (!empty($article->author) || !empty($article->created_by_alias)) : ?>
						<?php $author = $article->author ?>
						<?php $author = $article->created_by_alias ?: $author; ?>
						<?php if (!empty($article->contact_link) && $this->params->get('link_author') == true) : ?>
							<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', HTMLHelper::_('link', $article->contact_link, $author)); ?>
						<?php else : ?>
							<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_hits', 1)) : ?>
				<td headers="categorylist_header_hits" class="list-hits">
					<span class="badge badge-info">
						<?php echo Text::sprintf('JGLOBAL_HITS_COUNT', $article->hits); ?>
					</span>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_votes', 0) && $this->vote) : ?>
				<td headers="categorylist_header_votes" class="list-votes">
					<span class="badge badge-success">
						<?php echo Text::sprintf('COM_CONTENT_VOTES_COUNT', $article->rating_count); ?>
					</span>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_ratings', 0) && $this->vote) : ?>
				<td headers="categorylist_header_ratings" class="list-ratings">
					<span class="badge badge-warning">
						<?php echo Text::sprintf('COM_CONTENT_RATINGS_COUNT', $article->rating); ?>
					</span>
				</td>
			<?php endif; ?>
			<?php if ($isEditable) : ?>
				<td headers="categorylist_header_edit" class="list-edit">
					<?php if ($article->params->get('access-edit')) : ?>
						<?php echo HTMLHelper::_('contenticon.edit', $article, $article->params); ?>
					<?php endif; ?>
				</td>
			<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php // Code to add a link to submit an article. ?>
<?php if ($this->category->getParams()->get('access-create')) : ?>
	<?php echo HTMLHelper::_('contenticon.create', $this->category, $this->category->params); ?>
<?php endif; ?>

<?php // Add pagination links ?>
<?php if (!empty($this->items)) : ?>
	<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
		<div class="com-content-category__navigation w-100">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
				<p class="com-content-category__counter counter float-right pt-3 pr-2">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</p>
			<?php endif; ?>
			<div class="com-content-category__pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
</form>
