<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include jQuery
JHtml::_('behavior.core');
JHtml::_('bootstrap.modal');

$script = array();
$script[] = '	function jSelectPosition_' . $this->id . '(name) {';
$script[] = '		document.getElementById("' . $this->id . '").value = name;';
$script[] = '		jQuery("#moduleModal").modal("hide");';
$script[] = '	}';

$script[] = "	jQuery(document).ready(function() {";
$script[] = "		jQuery('#showmods').click(function() {";
$script[] = "			jQuery('#showmods').css('display', 'block');";
$script[] = "		    jQuery('.table tr.no').toggle();";
$script[] = "		});";
$script[] = "	});";

// Add normalized style.
$style = '@media only screen and (min-width : 768px) {
			#moduleModal {
			width: 80% !important;
			margin-left:-40% !important;
			height:auto;
			}
			#moduleModal #moduleModal-container .modal-body iframe {
			margin:0;
			padding:0;
			display:block;
			width:100%;
			height:600px !important;
			border:none;
			}
		}';

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
JFactory::getDocument()->addStyleDeclaration($style);

?>

<div class="control-group">
	<div class="control-label">
		<label for="showmods"><?php echo JText::_('COM_MENUS_ITEM_FIELD_HIDE_UNASSIGNED');?></label>
	</div>
	<div class="controls">
		<input type="checkbox" id="showmods" />
	</div>
</div>

	<table class="table table-striped">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('COM_MENUS_HEADING_ASSIGN_MODULE');?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_DISPLAY');?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
 			<?php if (is_null($module->menuid)) : ?>
				<?php if (!$module->except || $module->menuid < 0) : ?>
					<tr class="no row<?php echo $i % 2;?>">
				<?php else : ?>
			<tr class="row<?php echo $i % 2;?>">
				<?php endif; ?>
			<?php endif; ?>
				<td>
					<?php $link = 'index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;id=' . $module->id . '&amp;tmpl=component&amp;view=module&amp;layout=modal'; ?>
					<a href="#moduleModal" role="button" class="btn btn-link" data-toggle="modal" title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS');?>">
						<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
				</td>
				<td class="center">
					<?php if (is_null($module->menuid)) : ?>
						<?php if ($module->except):?>
							<span class="label label-success">
								<?php echo JText::_('JYES'); ?>
							</span>
						<?php else : ?>
							<span class="label label-important">
								<?php echo JText::_('JNO'); ?>
							</span>
						<?php endif;?>
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
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php echo JHtmlBootstrap::renderModal('moduleModal', array( 'url' => $link, 'title' => JText::_('COM_MENUS_EDIT_MODULE_SETTINGS'),'height' => '800px', 'width' => '800px'), ''); ?>
