<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
<table class="adminlist" cellspacing="1">
	<thead>
	<tr>
		<th class="title" width="10">
			<?php echo JText::_( 'Num' ); ?>
		</th>
		<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->data );?>);" />
		</th>
		<th class="title" nowrap="nowrap">
			<?php echo JText::_( 'Cache Group' ); ?>
		</th>
		<th width="5%" align="center" nowrap="nowrap">
			<?php echo JText::_( 'Number of Files' ); ?>
		</th>
		<th width="10%" align="center">
			<?php echo JText::_( 'Size' ); ?>
		</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="6">
		<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php
	$rc = 0;
	for ($i = 0, $n = count($this->data); $i < $n; $i ++) {
		$row = & $this->data[$i];
		?>

		<tr class="<?php echo "row$rc"; ?>" >
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->group; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td>
				<span class="bold">
					<?php echo $row->group; ?>
				</span>
			</td>
			<td align="center">
				<?php echo $row->count; ?>
			</td>
			<td align="center">
				<?php echo $row->size ?>
			</td>
		</tr>
		<?php
		$rc = 1 - $rc;
	}
	?>
	</tbody>
</table>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_cache" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_( 'form.token' ); ?>
</form>
