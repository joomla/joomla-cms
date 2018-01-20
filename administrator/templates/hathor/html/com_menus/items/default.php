<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.multiselect');

$user      = JFactory::getUser();
$app       = JFactory::getApplication();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.lft');
$canOrder  = $user->authorise('core.edit.state', 'com_menus');
$saveOrder = ($listOrder == 'a.lft' && $listDirn == 'asc');
$menutypeid	= (int) $this->state->get('menutypeid');
$assoc     = JLanguageAssociations::isEnabled() && $this->state->get('filter.client_id') == 0;;
?>

<?php // Set up the filter bar. ?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=items');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MENUS_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select">
			<label class="selectlabel" for="menutype">
				<?php echo JText::_('TPL_HATHOR_COM_MENUS_MENU'); ?>
			</label>
			<select name="menutype" id="menutype">
				<?php echo JHtml::_('select.options', JHtml::_('menu.menus'), 'value', 'text', $this->state->get('filter.menutype'));?>
			</select>

			<label class="selectlabel" for="filter_level">
				<?php echo JText::_('COM_MENUS_OPTION_SELECT_LEVEL'); ?>
			</label>
			<select name="filter_level" id="filter_level">
				<option value=""><?php echo JText::_('COM_MENUS_OPTION_SELECT_LEVEL');?></option>
				<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'));?>
			</select>

            		<label class="selectlabel" for="filter_published">
				<?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?>
			</label>
			<select name="filter[published]" id="filter_published">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => false)), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

            		<label class="selectlabel" for="filter_access">
				<?php echo JText::_('JOPTION_SELECT_ACCESS'); ?>
			</label>
			<select name="filter[access]" id="filter_access">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<label class="selectlabel" for="filter_language">
				<?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?>
			</label>
			<select name="filter[language]" id="filter_language">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>
<?php //Set up the grid heading. ?>
	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap state-col">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap ordering-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder); ?>
					<?php if ($canOrder && $saveOrder) : ?>
						<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'items.saveorder'); ?>
					<?php endif; ?>
				</th>
				<th class="title access-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('JGRID_HEADING_MENU_ITEM_TYPE'); ?>
				</th>
				<?php if ($this->state->get('filter.client_id') == 0): ?>
				<th class="home-col">
					<?php echo JHtml::_('grid.sort', 'COM_MENUS_HEADING_HOME', 'a.home', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<?php if ($assoc) : ?>
				<th class="width-5">
					<?php echo JHtml::_('grid.sort', 'COM_MENUS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
				</th>
				<?php endif;?>
				<th class="language-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap id-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php
		foreach ($this->items as $i => $item) :
			$orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
			$canCreate  = $user->authorise('core.create',     'com_menus.menu.' . $menutypeid);
			$canEdit    = $user->authorise('core.edit',       'com_menus.menu.' . $menutypeid);
			$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
			$canChange  = $user->authorise('core.edit.state', 'com_menus.menu.' . $menutypeid) && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit && !$item->protected) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_menus&task=item.edit&id='.(int) $item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<p class="smallsub" title="<?php echo $this->escape($item->path);?>">
						<?php echo str_repeat('<span class="gtr">|&mdash;</span>', $item->level - 1) ?>
						<?php if ($item->type != 'url') : ?>
							<?php if (empty($item->note)) : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
							<?php else : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note));?>
							<?php endif; ?>
						<?php elseif ($item->type == 'url' && $item->note) : ?>
							<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note));?>
						<?php endif; ?></p>
				</td>
				<td class="center">
					<?php echo JHtml::_('MenusHtml.Menus.state', $item->published, $i, $canChange, 'cb'); ?>
				</td>
				<td class="order">
					<?php if ($canChange) : ?>
						<?php if ($saveOrder) : ?>
							<span><?php echo $this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderkey - 1]), 'items.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
							<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, isset($this->ordering[$item->parent_id][$orderkey + 1]), 'items.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
						<?php endif; ?>
						<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" value="<?php echo $orderkey + 1;?>" <?php echo $disabled ?> class="text-area-order" title="<?php echo $item->title; ?> order" />
					<?php else : ?>
						<?php echo $orderkey + 1;?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="nowrap">
					<span title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
						<?php echo $this->escape($item->item_type); ?></span>
				</td>
				<?php if ($this->state->get('filter.client_id') == 0): ?>
				<td class="center">
					<?php if ($item->type == 'component') : ?>
						<?php if ($item->language == '*' || $item->home == '0'):?>
							<?php echo JHtml::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && $canChange  && !$item->protected);?>
						<?php elseif ($canChange):?>
							<a href="<?php echo JRoute::_('index.php?option=com_menus&task=items.unsetDefault&cid[]='.$item->id.'&'.JSession::getFormToken().'=1'); ?>">
								<?php if ($item->language_image) : ?>
									<?php echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => JText::sprintf('COM_MENUS_GRID_UNSET_LANGUAGE', $item->language_title)), true); ?>
								<?php else : ?>
									<span class="label" title="<?php echo JText::sprintf('COM_MENUS_GRID_UNSET_LANGUAGE', $item->language_title); ?>"><?php echo $item->language_sef; ?></span>
								<?php endif; ?>
							</a>
						<?php else:?>
							<?php if ($item->language_image) : ?>
								<?php echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
							<?php else : ?>
								<span class="label" title="<?php echo $item->language_title; ?>"><?php echo $item->language_sef; ?></span>
							<?php endif; ?>
						<?php endif;?>
					<?php endif; ?>
				</td>
				<?php endif; ?>
				<?php
				if ($assoc):
				?>
				<td class="center">
					<?php if ($item->association):?>
						<?php echo JHtml::_('MenusHtml.Menus.association', $item->id);?>
					<?php endif;?>
				</td>
				<?php endif;?>
				<td class="center">
					<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
				</td>
				<td class="center">
					<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
						<?php echo (int) $item->id; ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>
	<div class="clr"> </div>

	<?php //Load the batch processing form.is user is allowed ?>
	<?php if ($user->authorise('core.create', 'com_menus') || $user->authorise('core.edit', 'com_menus')) : ?>
		<?php echo JHtml::_(
			'bootstrap.renderModal',
			'collapseModal',
			array(
				'title'  => JText::_('COM_MENUS_BATCH_OPTIONS'),
				'footer' => $this->loadTemplate('batch_footer'),
			),
			$this->loadTemplate('batch_body')
		); ?>
	<?php endif;?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
