<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');

foreach ($this->levels as $key => $value) {
	$allLevels[$value->id] = $value->title;
}

JFactory::getDocument()->addScriptDeclaration('
	var viewLevels = ' . json_encode($allLevels) . ',
		menuId = parseInt(' . (int) $this->item->id . ');

	jQuery(function($) {
		var baseLink = "index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;tmpl=component&amp;view=module&amp;layout=modal&amp;id=",
			iFrameAttr = "class=\"iframe jviewport-height70\"";

		$(document)
			.on("click", "input:radio[id^=\'jform_toggle_modules_assigned1\']", function (event) {
				$(".table tr.no").hide();
			})
			.on("click", "input:radio[id^=\'jform_toggle_modules_assigned0\']", function (event) {
				$(".table tr.no").show();
			})
			.on("click", "input:radio[id^=\'jform_toggle_modules_published1\']", function (event) {
				$(".table tr.unpublished").hide();
			})
			.on("click", "input:radio[id^=\'jform_toggle_modules_published0\']", function (event) {
				$(".table tr.unpublished").show();
			})
			.on("click", ".module-edit-link", function () {
				var link = baseLink + $(this).data("moduleId"),
					iFrame = $("<iframe src=\"" + link + "\" " + iFrameAttr + "></iframe>");

				$("#moduleEditModal").modal()
					.find(".modal-body").empty().prepend(iFrame);
			})
			.on("click", "#moduleEditModal .modal-footer .btn", function () {
				var target = $(this).data("target");

				if (target) {
					$("#moduleEditModal iframe").contents().find(target).click();
				}
			});
	});
');

JFactory::getDocument()->addStyleDeclaration('
ul.horizontal-buttons li {
  display: inline-block;
  padding-right: 10%;
}
');

// Set up the bootstrap modal that will be used for all module editors
echo JHtml::_(
	'bootstrap.renderModal',
	'moduleEditModal',
	array(
		'title'       => JText::_('COM_MENUS_EDIT_MODULE_SETTINGS'),
		'backdrop'    => 'static',
		'keyboard'    => false,
		'closeButton' => false,
		'bodyHeight'  => '70',
		'modalWidth'  => '80',
		'footer'      => '<button type="button" class="btn" data-dismiss="modal" data-target="#closeBtn">'
				. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
				. '<button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#saveBtn">'
				. JText::_('JSAVE') . '</button>'
				. '<button type="button" class="btn btn-success" data-target="#applyBtn">'
				. JText::_('JAPPLY') . '</button>',
	)
);

?>
<?php
// Set main fields.
$this->fields = array('toggle_modules_assigned','toggle_modules_published');

echo JLayoutHelper::render('joomla.menu.edit_modules', $this); ?>

	<table class="table table-striped">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('COM_MENUS_HEADING_ASSIGN_MODULE'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_LEVELS'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_POSITION'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_DISPLAY'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?>
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
			<tr class="<?php echo $no; ?><?php echo $status; ?>row<?php echo $i % 2; ?>" id="tr-<?php echo $module->id; ?>" style="display:table-row">
				<td id="<?php echo $module->id; ?>">
					<button type="button"
						data-target="#moduleEditModal"
						class="btn btn-link module-edit-link"
						title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS'); ?>"
						id="title-<?php echo $module->id; ?>"
						data-module-id="<?php echo $module->id; ?>">
						<?php echo $this->escape($module->title); ?></button>
				</td>
				<td id="access-<?php echo $module->id; ?>">
					<?php echo $this->escape($module->access_title); ?>
				</td>
				<td id="position-<?php echo $module->id; ?>">
					<?php echo $this->escape($module->position); ?>
				</td>
				<td id="menus-<?php echo $module->id; ?>">
					<?php if (is_null($module->menuid)) : ?>
						<?php if ($module->except) : ?>
							<span class="label label-success">
								<?php echo JText::_('JYES'); ?>
							</span>
						<?php else : ?>
							<span class="label label-important">
								<?php echo JText::_('JNO'); ?>
							</span>
						<?php endif; ?>
					<?php elseif ($module->menuid > 0) : ?>
						<span class="label label-success">
							<?php echo JText::_('JYES'); ?>
						</span>
					<?php elseif ($module->menuid < 0) : ?>
						<span class="label label-important">
							<?php echo JText::_('JNO'); ?>
						</span>
					<?php else : ?>
						<span class="label label-info">
							<?php echo JText::_('JALL'); ?>
						</span>
					<?php endif; ?>
				</td>
				<td id="status-<?php echo $module->id; ?>">
						<?php if ($module->published) : ?>
							<span class="label label-success">
								<?php echo JText::_('JYES'); ?>
							</span>
						<?php else : ?>
							<span class="label label-important">
								<?php echo JText::_('JNO'); ?>
							</span>
						<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
