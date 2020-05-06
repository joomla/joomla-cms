<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

if (Factory::getApplication()->isClient('site'))
{
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

HTMLHelper::_('behavior.core');
HTMLHelper::_('script', 'com_fields/admin-fields-modal.js', array('version' => 'auto', 'relative' => true));

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = Factory::getApplication()->input->get('editor', '', 'cmd');
?>
<div class="container-popup">

	<form action="<?php echo Route::_('index.php?option=com_fields&view=fields&layout=modal&tmpl=component&editor=' . $editor . '&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table" id="moduleList">
				<caption id="captionTable" class="sr-only">
					<?php echo Text::_('COM_FIELDS_FIELDS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
				</caption>
				<thead>
					<tr>
						<th scope="col" style="width:1%" class="center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:15%" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_FIELDS_FIELD_GROUP_LABEL', 'g.title', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_FIELDS_FIELD_TYPE_LABEL', 'a.type', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:1%" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
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
						<td class="text-center tbody-icon">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
						</td>
						<th scope="row" class="has-context">
							<a class="btn btn-sm btn-block btn-success" href="#" onclick="Joomla.fieldIns('<?php echo $this->escape($item->id); ?>', '<?php echo $this->escape($editor); ?>');"><?php echo $this->escape($item->title); ?></a>
						</th>
						<td class="small d-none d-md-table-cell">
							<a class="btn btn-sm btn-block btn-warning" href="#" onclick="Joomla.fieldgroupIns('<?php echo $this->escape($item->group_id); ?>', '<?php echo $this->escape($editor); ?>');"><?php echo $item->group_id ? $this->escape($item->group_title) : Text::_('JNONE'); ?></a>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo $item->type; ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php // load the pagination. ?>
			<?php echo $this->pagination->getListFooter(); ?>

		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
