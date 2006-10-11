<fieldset class="adminform">
	<legend><?php echo JText::_( 'SEO Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" class="key">
			<span class="editlinktip">
				<?php
					echo mosToolTip( 'Search Engine Optimization Settings', '', 280, 'tooltip.png', 'Search Engine Friendly URLs', '', 0 );
				?>
				</span>
			</td>
			<td>
				<?php echo $lists['sef']; ?>
				<span class="error">
				<?php
					$tip = JText::_( 'WARNAPACHEONLY', true );
					echo ConfigApplicationView::WarningIcon( $tip );
				?>
				</span>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
