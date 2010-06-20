<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

$actions	= $this->data['actions'];
$extensions	= $this->data['extensions'];
?>

	<table class="adminlist">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('COM_USERS_REPORT_EXTENSION'); ?>
				</th>
				<?php foreach ($actions as $name) : ?>
				<th>
					<?php echo $name; ?>
				</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					&#160;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($extensions as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="right">
					<?php echo $item->name; ?>
				</td>
				<?php foreach ($actions as $action => $name) :
					$allowed = $item->actions[$action];
				?>
				<td class="center">
					<?php
					if ($allowed === null) :
						echo '<span class="disable">'.JText::_('JDENY').'</span>';
					elseif ($allowed) :
						echo '<span class="allow">'.JText::_('JALLOW').'</span>';
					else :
						echo '<span class="deny">'.JText::_('JDENY').'</span>';
					endif;
					?>
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>