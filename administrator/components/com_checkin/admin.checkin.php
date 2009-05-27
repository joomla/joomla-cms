<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Make sure the user is authorized to view this page

$user = & JFactory::getUser();
if (!$user->authorize('core.checkin.manage')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}
$db			= &JFactory::getDbo();
$nullDate	= $db->getNullDate();
?>
<div id="tablecell">
	<table class="adminform">
	<tr>
		<th class="title">
			<?php echo JText::_('Database Table'); ?>
		</th>
		<th class="title">
			<?php echo JText::_('Num of Items'); ?>
		</th>
		<th class="title">
			<?php echo JText::_('Checked-In'); ?>
		</th>
		<th class="title">
		</th>
	</tr>
	<?php
	$tables = $db->getTableList();
	$k = 0;
	foreach ($tables as $tn) {
		// make sure we get the right tables based on prefix
		if (!preg_match("/^".$mainframe->getCfg('dbprefix')."/i", $tn)) {
			continue;
		}
		$fields = $db->getTableFields(array($tn));

		$foundCO = false;
		$foundCOT = false;
		$foundE = false;

		$foundCO	= isset($fields[$tn]['checked_out']);
		$foundCOT	= isset($fields[$tn]['checked_out_time']);
		$foundE		= isset($fields[$tn]['editor']);

		if ($foundCO && $foundCOT) {
			if ($foundE) {
				$query = 'SELECT checked_out, editor FROM '.$tn.' WHERE checked_out > 0';
			} else {
				$query = 'SELECT checked_out FROM '.$tn.' WHERE checked_out > 0';
			}
			$db->setQuery($query);
			$res = $db->query();
			$num = $db->getNumRows($res);

			if ($foundE) {
				$query = 'UPDATE '.$tn.' SET checked_out = 0, checked_out_time = '.$db->Quote($nullDate).', editor = NULL WHERE checked_out > 0';
			} else {
				$query = 'UPDATE '.$tn.' SET checked_out = 0, checked_out_time = '.$db->Quote($nullDate).' WHERE checked_out > 0';
			}
			$db->setQuery($query);
			$res = $db->query();

			if ($res == 1) {
				if ($num > 0) {
					echo "<tr class=\"row$k\">";
					echo "\n	<td width=\"350\">". JText::_('Checking table') ." - ". $tn ."</td>";
					echo "\n	<td width=\"150\">". JText::_('Checked-In') ." <b>". $num ."</b> ". JText::_('items') ."</td>";
					echo "\n	<td width=\"100\" align=\"center\"><img src=\"images/tick.png\" border=\"0\" alt=\"". JText::_('tick') ."\" /></td>";
					echo "\n	<td>&nbsp;</td>";
					echo "\n</tr>";
				} else {
					echo "<tr class=\"row$k\">";
					echo "\n	<td width=\"350\">". JText::_('Checking table') ." - ". $tn ."</td>";
					echo "\n	<td width=\"150\">". JText::_('Checked-In') ." <b>". $num ."</b> ". JText::_('items') ."</td>";
					echo "\n	<td width=\"100\">&nbsp;</td>";
					echo "\n	<td>&nbsp;</td>";
					echo "\n</tr>";
				}
				$k = 1 - $k;
			}
		}
	}
	?>
	<tr>
		<td colspan="4">
			<strong>
			<?php echo JText::_('Checked out items have now been all checked in'); ?>
			</strong>
		</td>
	</tr>
	</table>
</div>