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
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = JFactory::getApplication()->input->get('editor', '', 'cmd');

JFactory::getDocument()->addScriptDeclaration('
moduleIns = function(type, name) {
	var extraVal ,fieldExtra = jQuery("#extra_class");
	extraVal = (fieldExtra.length && fieldExtra.val().length) ? "," + fieldExtra.val() : "";
	window.parent.jInsertEditorText("{loadmodule " + type + "," + name + extraVal + "}", "' . $editor . '");
	window.parent.jModalClose();
};
modulePosIns = function(position) {
	var extraVal ,fieldExtra = jQuery("#extra_class");
	extraVal = (fieldExtra.length && fieldExtra.val().length) ? "," + fieldExtra.val() : "";
	window.parent.jInsertEditorText("{loadposition " + position +  extraVal  + "}", "' . $editor . '");
	window.parent.jModalClose();
};');
?>
<form action="<?php echo JRoute::_('index.php?option=com_modules&view=modules&layout=modal&tmpl=component&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="container-popup">

		<div class="well">
			<div class="control-group">
				<div class="control-label">
					<label for="extra_class" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_MODULES_EXTRA_STYLE_DESC'); ?>" aria-invalid="false">
						<?php echo JText::_('COM_MODULES_EXTRA_STYLE_TITLE'); ?>
					</label>
				</div>
				<div class="controls">
					<input type="text" id="extra_class" value="" class="span12" size="45" maxlength="255" aria-invalid="false" />
				</div>
			</div>
		</div>

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if ($this->total > 0) : ?>
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

	</div>
</form>
