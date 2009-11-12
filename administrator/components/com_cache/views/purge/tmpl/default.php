<?php defined('_JEXEC') or die; ?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
<table class="adminlist">
	<thead>
		<tr>
			<th>
				<?php echo JText::_('Purge expired items'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
			<p class="mod-purge-instruct"><?php echo JText::_('Click on the Purge expired icon in the toolbar to delete all expired cache files. Note: Cache files that are still current will not be deleted.'); ?></p>
			<p class="warning"><?php echo JText::_('WARNING: This can be resource intensive on sites with large number of items!'); ?></p>
			</td>
		</tr>
	</tbody>
</table>

<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>