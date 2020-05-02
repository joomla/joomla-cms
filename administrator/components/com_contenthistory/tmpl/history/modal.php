<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

HTMLHelper::_('behavior.multiselect');

$input          = Factory::getApplication()->input;
$field          = $input->getCmd('field');
$function       = 'jSelectContenthistory_' . $field;
$listOrder      = $this->escape($this->state->get('list.ordering'));
$listDirn       = $this->escape($this->state->get('list.direction'));
$deleteMessage  = "alert(Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));";
$aliasArray     = explode('.', $this->state->type_alias);
$option         = (end($aliasArray) == 'category') ? 'com_categories&amp;extension=' . implode('.', array_slice($aliasArray, 0, count($aliasArray) - 1)) : $aliasArray[0];
$filter         = JFilterInput::getInstance();
$task           = $filter->clean(end($aliasArray)) . '.loadhistory';
$loadUrl        = Route::_('index.php?option=' . $filter->clean($option) . '&amp;task=' . $task);
$deleteUrl      = Route::_('index.php?option=com_contenthistory&task=history.delete');
$hash           = $this->state->get('sha1_hash');
$formUrl        = 'index.php?option=com_contenthistory&view=history&layout=modal&tmpl=component&item_id=' . $this->state->get('item_id') . '&type_id='
					. $this->state->get('type_id') . '&type_alias=' . $this->state->get('type_alias') . '&' . Session::getFormToken() . '=1';

Text::script('COM_CONTENTHISTORY_BUTTON_SELECT_ONE', true);
Text::script('COM_CONTENTHISTORY_BUTTON_SELECT_TWO', true);
Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

HTMLHelper::_('script', 'com_contenthistory/admin-history-modal.min.js', array('version' => 'auto', 'relative' => true));
?>
<div class="container-popup">
	<nav aria-label="toolbar">
		<div class="float-right mb-3">
			<button id="toolbar-load" type="submit" class="btn btn-secondary" aria-label="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_LOAD_DESC'); ?>" title="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_LOAD_DESC'); ?>" data-url="<?php echo Route::_($loadUrl); ?>">
				<span class="fas fa-upload" aria-hidden="true"></span>
				<span class="d-none d-md-inline"><?php echo Text::_('COM_CONTENTHISTORY_BUTTON_LOAD'); ?></span>
			</button>
			<button id="toolbar-preview" type="button" class="btn btn-secondary" aria-label="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_PREVIEW_DESC'); ?>" title="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_PREVIEW_DESC'); ?>" data-url="<?php echo Route::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . Session::getFormToken() . '=1'); ?>">
				<span class="fas fa-search" aria-hidden="true"></span>
				<span class="d-none d-md-inline"><?php echo Text::_('COM_CONTENTHISTORY_BUTTON_PREVIEW'); ?></span>
			</button>
			<button id="toolbar-compare" type="button" class="btn btn-secondary" aria-label="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_COMPARE_DESC'); ?>" title="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_COMPARE_DESC'); ?>" data-url="<?php echo Route::_('index.php?option=com_contenthistory&view=compare&layout=compare&tmpl=component&' . Session::getFormToken() . '=1'); ?>">
				<span class="fas fa-search-plus" aria-hidden="true"></span>
				<span class="d-none d-md-inline"><?php echo Text::_('COM_CONTENTHISTORY_BUTTON_COMPARE'); ?></span>
			</button>
			<button onclick="if (document.adminForm.boxchecked.value==0){<?php echo $deleteMessage; ?>}else{ Joomla.submitbutton('history.keep')}" class="btn btn-secondary pointer" aria-label="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_KEEP_DESC'); ?>" title="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_KEEP_DESC'); ?>">
				<span class="fas fa-lock" aria-hidden="true"></span>
				<span class="d-none d-md-inline"><?php echo Text::_('COM_CONTENTHISTORY_BUTTON_KEEP'); ?></span>
			</button>
			<button onclick="if (document.adminForm.boxchecked.value==0){<?php echo $deleteMessage; ?>}else{ Joomla.submitbutton('history.delete')}" class="btn btn-secondary pointer" aria-label="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_DELETE_DESC'); ?>" title="<?php echo Text::_('COM_CONTENTHISTORY_BUTTON_DELETE_DESC'); ?>">
				<span class="fas fa-times" aria-hidden="true"></span>
				<span class="d-none d-md-inline"><?php echo Text::_('COM_CONTENTHISTORY_BUTTON_DELETE'); ?></span>
			</button>
		</div>
	</nav>

	<form action="<?php echo Route::_($formUrl); ?>" method="post" name="adminForm" id="adminForm">
		<table class="table table-sm">
			<caption id="captionTable" class="sr-only">
				<?php echo Text::_('COM_CONTENTHISTORY_VERSION_CAPTION'); ?>
			</caption>
			<thead>
				<tr>
					<td style="width:1%" class="text-center">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)">
					</td>
					<th scope="col" style="width:15%">
						<?php echo Text::_('JDATE'); ?>
					</th>
					<th scope="col" style="width:15%" class="d-none d-md-table-cell">
						<?php echo Text::_('COM_CONTENTHISTORY_VERSION_NOTE'); ?>
					</th>
					<th scope="col" class="w-10">
						<?php echo Text::_('COM_CONTENTHISTORY_KEEP_VERSION'); ?>
					</th>
					<th scope="col" style="width:15%" class="d-none d-md-table-cell">
						<?php echo Text::_('JAUTHOR'); ?>
					</th>
					<th scope="col" class="w-10 text-right">
						<?php echo Text::_('COM_CONTENTHISTORY_CHARACTER_COUNT'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php $i = 0; ?>
			<?php foreach ($this->items as $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="text-center">
						<?php echo HTMLHelper::_('grid.id', $i, $item->version_id); ?>
					</td>
					<th scope="row">
						<a class="save-date" onclick="window.open(this.href,'win2','width=800,height=600,resizable=yes,scrollbars=yes'); return false;"
							href="<?php echo Route::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . Session::getFormToken() . '=1&version_id=' . $item->version_id); ?>">
							<?php echo HTMLHelper::_('date', $item->save_date, Text::_('DATE_FORMAT_LC6')); ?>
						</a>
						<?php if ($item->sha1_hash == $hash) : ?>
							<span class="fas fa-star" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('JCURRENT'); ?></span>
						<?php endif; ?>
					</th>
					<td class="d-none d-md-table-cell">
						<?php echo htmlspecialchars($item->version_note); ?>
					</td>
					<td>
						<?php if ($item->keep_forever) : ?>
							<a class="btn btn-secondary btn-sm active" rel="tooltip" href="javascript:void(0);"
								onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','history.keep')">
								<?php echo Text::_('JYES'); ?>&nbsp;<span class="fas fa-lock" aria-hidden="true"></span>
							</a>
						<?php else : ?>
							<a class="btn btn-secondary btn-sm active" rel="tooltip" href="javascript:void(0);"
								onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','history.keep')">
								<?php echo Text::_('JNO'); ?>
							</a>
						<?php endif; ?>
					</td>
					<td class="d-none d-md-table-cell">
						<?php echo htmlspecialchars($item->editor); ?>
					</td>
					<td class="text-right">
						<?php echo number_format((int) $item->character_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')); ?>
					</td>
				</tr>
				<?php $i++; ?>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php // load the pagination. ?>
		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
