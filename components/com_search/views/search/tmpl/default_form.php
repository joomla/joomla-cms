<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post">
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td nowrap="nowrap">
		<label for="search_searchword">
			<?php echo JText::_( 'Search Keyword' ); ?>:
		</label>
	</td>
	<td nowrap="nowrap">
		<input type="text" name="searchword" id="search_searchword" size="30" maxlength="20" value="<?php echo $this->searchword; ?>" class="inputbox" />
	</td>
	<td width="100%" nowrap="nowrap">
		<input type="submit" name="submit" value="<?php echo JText::_( 'Search' );?>" class="button" />
	</td>
</tr>
<tr>
	<td colspan="3">
		<?php echo $this->lists['searchphrase']; ?>
	</td>
</tr>
<tr>
	<td colspan="3">
		<label for="ordering">
			<?php echo JText::_( 'Ordering' );?>:
		</label>
		<?php echo $this->lists['ordering'];?>
	</td>
</tr>
</table>
<?php if ($this->params->get( 'search_areas', 1 )) : ?>
	<?php echo JText::_( 'Search Only' );?>:
	<?php foreach ($this->searchareas['search'] as $val => $txt) :
		$checked = is_array( $this->searchareas['active'] ) && in_array( $val, $this->searchareas['active'] ) ? 'checked="true"' : '';
	?>
	<input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area_<?php echo $val;?>" <?php echo $checked;?> />
		<label for="area_<?php echo $val;?>">
			<?php echo JText::_($txt); ?>
		</label>
	<?php endforeach; ?>
<?php endif; ?>
<input type="hidden" name="task"   value="search" />
<input type="hidden" name="option" value="com_search" />
</form>