<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('behavior.multiselect');
JHtml::_('jquery.framework');

$input = JFactory::getApplication()->input;
$field = $input->getCmd('field');
$function = 'jSelectContenthistory_' . $field;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$message = JText::_('COM_CONTENTHISTORY_BUTTON_SELECT_ONE', true);
$compareMessage = JText::_('COM_CONTENTHISTORY_BUTTON_SELECT_TWO', true);
JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
$deleteMessage = "alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));";
$aliasArray = explode('.', $this->state->type_alias);
$option = (end($aliasArray) == 'category') ? 'com_categories&amp;extension=' . implode('.', array_slice($aliasArray, 0, count($aliasArray) - 1)) : $aliasArray[0];
$filter = JFilterInput::getInstance();
$task = $filter->clean(end($aliasArray)) . '.loadhistory';
$loadUrl = JRoute::_('index.php?option=' . $filter->clean($option) . '&amp;task=' . $task);
$deleteUrl = JRoute::_('index.php?option=com_contenthistory&task=history.delete');
$hash = $this->state->get('sha1_hash');
$formUrl = 'index.php?option=com_contenthistory&view=history&layout=modal&tmpl=component&item_id=' . $this->state->get('item_id') . '&type_id='
	. $this->state->get('type_id') . '&type_alias=' . $this->state->get('type_alias') . '&' . JSession::getFormToken() . '=1';

JFactory::getDocument()->addScriptDeclaration("
	(function ($){
		$(document).ready(function (){
			$('#toolbar-load').click(function() {
				var ids = $('input[id*=\'cb\']:checked');
				if (ids.length == 1) {
					// Add version item id to URL
					var url = $('#toolbar-load').attr('data-url') + '&version_id=' + ids[0].value;
					$('#content-url').attr('data-url', url);
					if (window.parent) {
						window.parent.location = url;
					}
				} else {
					alert('" . $message . "');
				}
			});

		$('#toolbar-preview').click(function() {
				var windowSizeArray = ['width=800, height=600, resizable=yes, scrollbars=yes'];
				var ids = $('input[id*=\'cb\']:checked');
				if (ids.length == 1) {
					// Add version item id to URL
					var url = $('#toolbar-preview').attr('data-url') + '&version_id=' + ids[0].value;
					$('#content-url').attr('data-url', url);
					if (window.parent) {
						window.open(url, '', windowSizeArray);
						return false;
					}
				} else {
					alert('" . $message . "');
				}
			});

			$('#toolbar-compare').click(function() {
				var windowSizeArray = ['width=1000, height=600, resizable=yes, scrollbars=yes'];
				var ids = $('input[id*=\'cb\']:checked');
				if (ids.length == 2) {
					// Add version item ids to URL
					var url = $('#toolbar-compare').attr('data-url') + '&id1=' + ids[0].value + '&id2=' + ids[1].value;
					$('#content-url').attr('data-url', url);
					if (window.parent) {
						window.open(url, '', windowSizeArray);
						return false;
					}
				} else {
					alert('" . $compareMessage . "');
				}
			});
		});
	})(jQuery);
	"
);

?>
<div class="container-popup">

	<div class="btn-group pull-right">
		<button id="toolbar-load" type="submit" class="btn hasTooltip" aria-label="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_LOAD_DESC'); ?>" title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_LOAD_DESC'); ?>" data-url="<?php echo JRoute::_($loadUrl); ?>">
			<span class="icon-upload" aria-hidden="true"></span><span class="hidden-phone"><?php echo JText::_('COM_CONTENTHISTORY_BUTTON_LOAD'); ?></span></button>
		<button id="toolbar-preview" type="button" class="btn hasTooltip" aria-label="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_PREVIEW_DESC'); ?>" title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_PREVIEW_DESC'); ?>" data-url="<?php echo JRoute::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . JSession::getFormToken() . '=1'); ?>">
			<span class="icon-search" aria-hidden="true"></span><span class="hidden-phone"><?php echo JText::_('COM_CONTENTHISTORY_BUTTON_PREVIEW'); ?></span></button>
		<button id="toolbar-compare" type="button" class="btn hasTooltip" aria-label="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_COMPARE_DESC'); ?>" title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_COMPARE_DESC'); ?>" data-url="<?php echo JRoute::_('index.php?option=com_contenthistory&view=compare&layout=compare&tmpl=component&' . JSession::getFormToken() . '=1'); ?>">
			<span class="icon-zoom-in" aria-hidden="true"></span><span class="hidden-phone"><?php echo JText::_('COM_CONTENTHISTORY_BUTTON_COMPARE'); ?></span></button>
		<button onclick="if (document.adminForm.boxchecked.value==0){<?php echo $deleteMessage; ?>}else{ Joomla.submitbutton('history.keep')}" class="btn pointer hasTooltip" aria-label="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_KEEP_DESC'); ?>" title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_KEEP_DESC'); ?>">
			<span class="icon-lock" aria-hidden="true"></span><span class="hidden-phone"><?php echo JText::_('COM_CONTENTHISTORY_BUTTON_KEEP'); ?></span></button>
		<button onclick="if (document.adminForm.boxchecked.value==0){<?php echo $deleteMessage; ?>}else{ Joomla.submitbutton('history.delete')}" class="btn pointer hasTooltip" aria-label="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_DELETE_DESC'); ?>" title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_DELETE_DESC'); ?>">
			<span class="icon-delete" aria-hidden="true"></span><span class="hidden-phone"><?php echo JText::_('COM_CONTENTHISTORY_BUTTON_DELETE'); ?></span></button>
	</div>

	<div class="clearfix"></div>
	<hr class="hr-condensed" />

	<form action="<?php echo JRoute::_($formUrl); ?>" method="post" name="adminForm" id="adminForm">
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th width="1%" class="center">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="15%">
						<?php echo JText::_('JDATE'); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JText::_('COM_CONTENTHISTORY_VERSION_NOTE'); ?>
					</th>
					<th width="10%" class="nowrap">
						<?php echo JText::_('COM_CONTENTHISTORY_KEEP_VERSION'); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JText::_('JAUTHOR'); ?>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo JText::_('COM_CONTENTHISTORY_CHARACTER_COUNT'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="15">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php $i = 0; ?>
			<?php foreach ($this->items as $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->version_id); ?>
					</td>
					<td>
						<a class="save-date" onclick="window.open(this.href,'win2','width=800,height=600,resizable=yes,scrollbars=yes'); return false;"
							href="<?php echo JRoute::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . JSession::getFormToken() . '=1&version_id=' . $item->version_id); ?>">
							<?php echo JHtml::_('date', $item->save_date, JText::_('DATE_FORMAT_LC6')); ?>
						</a>
						<?php if ($item->sha1_hash == $hash) : ?>
							<span class="icon-featured" aria-hidden="true"><span class="element-invisible"><?php echo JText::_('JFEATURED'); ?></span></span>&nbsp;
						<?php endif; ?>
					</td>
					<td class="hidden-phone">
						<?php echo htmlspecialchars($item->version_note); ?>
					</td>
					<td>
						<?php if ($item->keep_forever) : ?>
							<a class="btn btn-mini active" rel="tooltip" href="javascript:void(0);"
								onclick="return listItemTask('cb<?php echo $i; ?>','history.keep')"
								data-original-title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_KEEP_TOGGLE_OFF'); ?>">
								<?php echo JText::_('JYES'); ?>&nbsp;<span class="icon-lock" aria-hidden="true"></span>
							</a>
						<?php else : ?>
							<a class="btn btn-mini active" rel="tooltip" href="javascript:void(0);"
								onclick="return listItemTask('cb<?php echo $i; ?>','history.keep')"
								data-original-title="<?php echo JText::_('COM_CONTENTHISTORY_BUTTON_KEEP_TOGGLE_ON'); ?>">
								<?php echo JText::_('JNO'); ?>
							</a>
						<?php endif; ?>
					</td>
					<td class="hidden-phone">
						<?php echo htmlspecialchars($item->editor); ?>
					</td>
					<td class="center">
						<?php echo number_format((int) $item->character_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')); ?>
					</td>
				</tr>
				<?php $i++; ?>
			<?php endforeach; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
