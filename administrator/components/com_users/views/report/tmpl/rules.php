<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
					<?php echo JText::_('Users_Report_Extension'); ?>
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
					&nbsp;
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
						echo '<span style="color:#aaa">Deny</span>';
					elseif ($allowed) :
						echo '<span style="color:green">'.JText::_('JAllow').'</span>';
					else :
						echo '<span style="color:red">'.JText::_('JDeny').'</span>';
					endif;
					?>
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
