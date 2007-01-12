<fieldset class="adminform">
	<legend><?php echo JText::_( 'SEO Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Search Engine Friendly URLs' ); ?>::<?php echo JText::_('Search Engine Optimization Settings'); ?>">
					<?php echo JText::_( 'Search Engine Friendly URLs' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['sef']; ?>
				<span class="error hasTip" title="<?php echo JText::_( 'Warning' );?>::<?php echo JText::_( 'WARNAPACHEONLY' ); ?>">
					<?php echo ConfigApplicationView::WarningIcon(); ?>
				</span>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
