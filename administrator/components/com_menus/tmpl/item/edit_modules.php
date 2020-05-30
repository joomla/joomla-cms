<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.core');

foreach ($this->levels as $key => $value)
{
	$allLevels[$value->id] = $value->title;
}

$this->document->addScriptOptions('menus-edit-modules', ['viewLevels' => $allLevels, 'itemId' => $this->item->id]);
HTMLHelper::_('stylesheet', 'com_menus/admin-item-edit_modules.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_menus/admin-item-edit_modules.min.js', ['version' => 'auto', 'relative' => true], ['defer' => 'defer']);

// Set up the bootstrap modal that will be used for all module editors
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'moduleEditModal',
	array(
		'title'       => Text::_('COM_MENUS_EDIT_MODULE_SETTINGS'),
		'backdrop'    => 'static',
		'keyboard'    => false,
		'closeButton' => false,
		'bodyHeight'  => '70',
		'modalWidth'  => '80',
		'footer'      => '<button type="button" class="btn" data-dismiss="modal" data-target="#closeBtn">'
				. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
				. '<button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#saveBtn">'
				. Text::_('JSAVE') . '</button>'
				. '<button type="button" class="btn btn-success" data-target="#applyBtn">'
				. Text::_('JAPPLY') . '</button>',
	)
);

?>
<?php
// Set main fields.
$this->fields = array('toggle_modules_assigned','toggle_modules_published');

echo LayoutHelper::render('joomla.menu.edit_modules', $this); ?>

<table class="table" id="modules_assigned">
	<caption id="captionTable" class="sr-only">
		<?php echo Text::_('COM_MENUS_MODULES_TABLE_CAPTION'); ?>
	</caption>
	<thead>
		<tr>
			<th scope="col" class="w-40">
				<?php echo Text::_('COM_MENUS_HEADING_ASSIGN_MODULE'); ?>
			</th>
			<th scope="col" class="w-15">
				<?php echo Text::_('COM_MENUS_HEADING_LEVELS'); ?>
			</th>
			<th scope="col" class="w-15">
				<?php echo Text::_('COM_MENUS_HEADING_POSITION'); ?>
			</th>
			<th scope="col">
				<?php echo Text::_('COM_MENUS_HEADING_DISPLAY'); ?>
			</th>
			<th scope="col">
				<?php echo Text::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($this->modules as $i => &$module) : ?>
		<?php if (is_null($module->menuid)) : ?>
			<?php if (!$module->except || $module->menuid < 0) : ?>
				<?php $no = 'no '; ?>
			<?php else : ?>
				<?php $no = ''; ?>
			<?php endif; ?>
		<?php else : ?>
			<?php $no = ''; ?>
		<?php endif; ?>
		<?php if ($module->published) : ?>
			<?php $status = ''; ?>
		<?php else : ?>
			<?php $status = 'unpublished '; ?>
		<?php endif; ?>
		<tr class="<?php echo $no; ?><?php echo $status; ?>row<?php echo $i % 2; ?>">
			<th scope="row">
				<button type="button"
					data-target="#moduleEditModal"
					class="btn btn-link module-edit-link"
					title="<?php echo Text::_('COM_MENUS_EDIT_MODULE_SETTINGS'); ?>"
					id="title-<?php echo $module->id; ?>"
					data-module-id="<?php echo $module->id; ?>">
					<?php echo $this->escape($module->title); ?></button>
			</th>
			<td>
				<?php echo $this->escape($module->access_title); ?>
			</td>
			<td>
				<?php echo $this->escape($module->position); ?>
			</td>
			<td>
				<?php if (is_null($module->menuid)) : ?>
					<?php if ($module->except) : ?>
						<span class="badge badge-success">
							<?php echo Text::_('JYES'); ?>
						</span>
					<?php else : ?>
						<span class="badge badge-danger">
							<?php echo Text::_('JNO'); ?>
						</span>
					<?php endif; ?>
				<?php elseif ($module->menuid > 0) : ?>
					<span class="badge badge-success">
						<?php echo Text::_('JYES'); ?>
					</span>
				<?php elseif ($module->menuid < 0) : ?>
					<span class="badge badge-danger">
						<?php echo Text::_('JNO'); ?>
					</span>
				<?php else : ?>
					<span class="badge badge-info">
						<?php echo Text::_('JALL'); ?>
					</span>
				<?php endif; ?>
			</td>
			<td>
				<?php if ($module->published) : ?>
					<span class="badge badge-success">
						<?php echo Text::_('JYES'); ?>
					</span>
				<?php else : ?>
					<span class="badge badge-danger">
						<?php echo Text::_('JNO'); ?>
					</span>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
