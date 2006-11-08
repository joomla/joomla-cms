<fieldset class="adminform">
	<legend><?php echo JText::_( 'Locale Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip">
				<?php
					$tip = JText::_( 'Current date/time configured to display' ) .': '. JHTML::Date( 'now', DATE_FORMAT_LC2);
					echo mosToolTip( $tip, '', 280, 'tooltip.png', 'Time Offset', '', 0 );
				?>
				</span>
			</td>
			<td>
				<?php echo $lists['offset']; ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
