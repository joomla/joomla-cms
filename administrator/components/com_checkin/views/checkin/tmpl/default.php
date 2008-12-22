<?php defined('_JEXEC') or die('Restricted access'); ?>

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
	$k = 0;
	foreach ($this->rows as $row) {
		echo "<tr class=\"row$k\">";
		echo "\n	<td width=\"350\">". JText::_('Checking table') ." - ". $row['table'] ."</td>";
		echo "\n	<td width=\"150\">". JText::_('Checked-In') ." <b>". $row['checked_in'] ."</b> ". JText::_('items') ."</td>";
		if ($row['checked_in'] > 0) {
			echo "\n	<td width=\"100\" align=\"center\"><img src=\"images/tick.png\" border=\"0\" alt=\"". JText::_('tick') ."\" /></td>";
		} else {
			echo "\n	<td width=\"100\">&nbsp;</td>";
		}
		echo "\n	<td>&nbsp;</td>";
		echo "\n</tr>";
		$k = 1 - $k;
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
