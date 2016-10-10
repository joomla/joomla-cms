<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (JFactory::getApplication()->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = JFactory::getApplication()->input->get('editor', '', 'cmd');

JFactory::getDocument()->addScriptDeclaration('
moduleIns = function(type, name) {
	window.parent.jInsertEditorText("{loadmodule " + type + "," + name + "}", "' . $editor . '");
	window.parent.jModalClose();
};
modulePosIns = function(position) {
	window.parent.jInsertEditorText("{loadposition " + position + "}", "' . $editor . '");
	window.parent.jModalClose();
};');
?>
<div class="container-popup">

	<form action="<?php echo JRoute::_('index.php?option=com_modules&view=modules&layout=modal&tmpl=component&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<div class="clearfix"></div>

		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_MODULES_MSG_MANAGE_NO_MODULES'); ?>
		</div>
		<?php else : ?>
		<table class="table table-striped" id="moduleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'a.position', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone hidden-tablet">
						<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_PAGES', 'pages', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'ag.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'l.title', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="8">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$iconStates = array(
					-2 => 'icon-trash',
					0  => 'icon-unpublish',
					1  => 'icon-publish',
					2  => 'icon-archive',
				);
				foreach ($this->items as $i => $item) :
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<span class="<?php echo $iconStates[$this->escape($item->published)]; ?>"></span>
					</td>
					<td class="has-context">
						<a class="btn btn-small btn-block btn-success" href="#" onclick="moduleIns('<?php echo $this->escape($item->module); ?>', '<?php echo $this->escape($item->title); ?>');"><?php echo $this->escape($item->title); ?></a>
					</td>
					<td class="small hidden-phone">
						<?php if ($item->position) : ?>
						<a class="btn btn-small btn-block btn-warning" href="#" onclick="modulePosIns('<?php echo $this->escape($item->position); ?>');"><?php echo $this->escape($item->position); ?></a>
						<?php else : ?>
						<span class="label"><?php echo JText::_('JNONE'); ?></span>
						<?php endif; ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $item->name; ?>
					</td>
					<td class="small hidden-phone hidden-tablet">
						<?php echo $item->pages; ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="small hidden-phone">
						<?php if ($item->language == '') : ?>
							<?php echo JText::_('JDEFAULT'); ?>
						<?php elseif ($item->language == '*') : ?>
							<?php echo JText::alt('JALL', 'language'); ?>
						<?php else : ?>
							<?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
						<?php endif;?>
					</td>
					<td class="hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif;?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
