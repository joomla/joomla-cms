<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
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

JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));

$function     = $app->input->get('function', 'jSelectMenuItem', 'cmd');
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn     = $this->escape($this->state->get('list.direction'));

$app->getDocument()->addScriptDeclaration("
jQuery(document).ready(function($) {
	$('body').on('click', '.select-link', function() {
		// Run function on parent window.
		if(self != top)
		{
			window.parent." . $function . "(this.getAttribute('data-id'), this.getAttribute('data-title'), null, null, this.getAttribute('data-uri'), this.getAttribute('data-language'), null);
		}
	});
});");
?>
<div class="container-popup">
	<form action="<?php echo JRoute::_('index.php?option=com_menus&view=items&layout=modal&tmpl=component&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-warning alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-sm">
				<thead>
					<tr>
						<th width="1%" class="nowrap text-center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_MENUS_HEADING_MENU', 'menutype_title', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="text-center nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_MENUS_HEADING_HOME', 'a.home', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php if ($item->type != 'separator' && $item->type != 'alias' && $item->type != 'heading' && $item->type != 'url') : ?>
						<?php if ($item->language && JLanguageMultilang::isEnabled())
						{
							if ($item->language !== '*')
							{
								$language = $item->language;
							}
							else
							{
								$language = '';
							}
						}
						elseif (!JLanguageMultilang::isEnabled())
						{
							$language = '';
						}
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo JHtml::_('MenusHtml.Menus.state', $item->published, $i, 0); ?>
							</td>
							<td>
								<?php $prefix = JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
								<?php echo $prefix; ?>
								<a class="select-link" href="javascript:void(0)"
									data-function="<?php echo $this->escape($function); ?>"
									data-id="<?php echo $item->id; ?>"
									data-title="<?php echo $this->escape($item->title); ?>"
									data-uri="<?php echo 'index.php?Itemid=' . $item->id; ?>"
									data-language="<?php echo $this->escape($language); ?>">
									<?php echo $this->escape($item->title); ?></a>
								<span class="small">
									<?php if (empty($item->note)) : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									<?php else : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
									<?php endif; ?>
								</span>
								<div title="<?php echo $this->escape($item->path); ?>">
									<?php echo $prefix; ?>
									<span class="small" title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
										<?php echo $this->escape($item->item_type); ?></span>
								</div>
							</td>
							<td class="small hidden-sm-down">
								<?php echo $this->escape($item->menutype_title); ?>
							</td>
							<td class="text-center hidden-sm-down">
								<?php if ($item->type == 'component') : ?>
									<?php if ($item->language == '*' || $item->home == '0') : ?>
										<?php echo JHtml::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && 0); ?>
									<?php else : ?>
										<?php if ($item->language_image) : ?>
											<?php echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
										<?php else : ?>
											<span class="label" title="<?php echo $item->language_title; ?>"><?php echo $item->language_sef; ?></span>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							</td>
							<td class="small hidden-sm-down">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="small hidden-sm-down">
								<?php if ($item->language == '') : ?>
									<?php echo JText::_('JDEFAULT'); ?>
								<?php elseif ($item->language == '*') : ?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else : ?>
									<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
								<?php endif; ?>
							</td>
							<td class="hidden-sm-down">
								<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
									<?php echo (int) $item->id; ?>
								</span>
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="function" value="<?php echo $function; ?>" />
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'cmd'); ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
