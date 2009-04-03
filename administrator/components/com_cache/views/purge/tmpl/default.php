<form action="index.php" method="post" name="adminForm">
<table class="adminlist" cellspacing="1">
<thead>
	<tr>
		<th align="left" style="text-align: left;">
			<?php echo JText::_( 'Purge expired items' ); ?>
		</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td align="left">
		<?php echo JText::_( 'Click on the Purge expired icon in the toolbar to delete all expired cache files. Note: Cache files that are still current will not be deleted.'); ?> <br />
		<span style="font-weight: bold"><?php echo JText::_( 'WARNING: This can be resource intensive on sites with large number of items!' ); ?></span>
		</td>
	</tr>
</tbody>
</table>
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_cache" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>