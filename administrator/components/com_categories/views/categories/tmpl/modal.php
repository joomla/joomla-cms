<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));

$extension = $this->escape($this->state->get('filter.extension'));
$function  = $app->input->getCmd('function', 'jSelectCategory');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div class="container-popup">

	<form action="<?php echo JRoute::_('index.php?option=com_categories&view=categories&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<div class="clearfix"></div>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="categoryList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="5">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$iconStates = array(
						-2 => 'icon-trash',
						0 => 'icon-unpublish',
						1 => 'icon-publish',
						2 => 'icon-archive',
					);
					?>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php if ($item->language && JLanguageMultilang::isEnabled())
						{
							$tag = strlen($item->language);
							if ($tag == 5)
							{
								$lang = substr($item->language, 0, 2);
							}
							elseif ($tag == 6)
							{
								$lang = substr($item->language, 0, 3);
							}
							else
							{
								$lang = '';
							}
						}
						elseif (!JLanguageMultilang::isEnabled())
						{
							$lang = '';
						}
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<span class="<?php echo $iconStates[$this->escape($item->published)]; ?>"></span>
							</td>
							<td>
								<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
								<a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', null, '<?php echo $this->escape(ContentHelperRoute::getCategoryRoute($item->id, $item->language)); ?>', '<?php echo $this->escape($lang); ?>', null);">
									<?php echo $this->escape($item->title); ?>
								</a>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="small hidden-phone">
								<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
							</td>
							<td class="hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="extension" value="<?php echo $extension; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
