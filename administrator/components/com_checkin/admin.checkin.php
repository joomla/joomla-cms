<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Checkin
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_checkin', 'manage' ))
{
	josRedirect( 'index2.php?', JText::_('ALERTNOTAUTH') );
}
$nullDate = $database->getNullDate();
?>
<div id="tablecell">				
	<table class="adminform">
	<tr>
		<th class="title">
			<?php echo JText::_( 'Database Table' ); ?>
		</th>
		<th class="title">
			<?php echo JText::_( 'Num of Items' ); ?>
		</th>
		<th class="title">
			<?php echo JText::_( 'Checked-In' ); ?>
		</th>
		<th class="title">
		</th>
	</tr>
	<?php
	$tables = $database->getTableList();
	$k = 0;
	foreach ($tables as $tn) {
		// make sure we get the right tables based on prefix
		if (!preg_match( "/^".$mainframe->getCfg('dbprefix')."/i", $tn )) {
			continue;
		}
		$fields = $database->getTableFields( array( $tn ) );
	
		$foundCO = false;
		$foundCOT = false;
		$foundE = false;
	
		$foundCO	= isset( $fields[$tn]['checked_out'] );
		$foundCOT	= isset( $fields[$tn]['checked_out_time'] );
		$foundE		= isset( $fields[$tn]['editor'] );
	
		if ($foundCO && $foundCOT) {
			if ($foundE) {
				$query = "SELECT checked_out, editor"
				. "\n FROM $tn"
				. "\n WHERE checked_out > 0"
				;
			} else {
				$query = "SELECT checked_out"
				. "\n FROM $tn"
				. "\n WHERE checked_out > 0"
				;
			}
			$database->setQuery( $query );
			$res = $database->query();
			$num = $database->getNumRows( $res );
	
			if ($foundE) {
				$query = "UPDATE $tn"
				. "\n SET checked_out = 0, checked_out_time = '$nullDate', editor = NULL"
				. "\n WHERE checked_out > 0"
				;
			} else {
				$query = "UPDATE $tn"
				. "\n SET checked_out = 0, checked_out_time = '$nullDate'"
				. "\n WHERE checked_out > 0"
				;
			}
			$database->setQuery( $query );
			$res = $database->query();
	
			if ($res == 1) {
				if ($num > 0) {
					echo "<tr class=\"row$k\">";
					echo "\n	<td width=\"350\">". JText::_( 'Checking table' ) ." - ". $tn ."</td>";
					echo "\n	<td width=\"150\">". JText::_( 'Checked-In' ) ." <b>". $num ."</b> ". JText::_( 'items' ) ."</td>";
					echo "\n	<td width=\"100\" align=\"center\"><img src=\"images/tick.png\" border=\"0\" alt=\"". JText::_( 'tick' ) ."\" /></td>";
					echo "\n	<td>&nbsp;</td>";
					echo "\n</tr>";
				} else {
					echo "<tr class=\"row$k\">";
					echo "\n	<td width=\"350\">". JText::_( 'Checking table' ) ." - ". $tn ."</td>";
					echo "\n	<td width=\"150\">". JText::_( 'Checked-In' ) ." <b>". $num ."</b> ". JText::_( 'items' ) ."</td>";
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
			<?php echo JText::_( 'Checked out items have now been all checked in' ); ?>
			</strong>
		</td>
	</tr>
	</table>
</div>