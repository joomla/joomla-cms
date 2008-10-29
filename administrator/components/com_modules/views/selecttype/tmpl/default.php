<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	JHtml::_('behavior.tooltip');
?>

<form action=<?php echo JRoute::_("index.php"); ?> method="post" name="adminForm">

<table class="adminlist" cellpadding="1">
<thead>
<tr>
	<th colspan="4">
	<?php echo JText::_( 'Modules' ); ?>
	</th>
</tr>
</thead>
<tfoot>
<tr>
	<th colspan="4">&nbsp;
	</th>
</tr>
</tfoot>
<tbody>
<?php
$k 		= 0;
$x 		= 0;
$count 	= count( $this->modules );
for ( $i=0; $i < $count; $i++ ) {
	$row = &$this->modules[$i];

	$link = 'index.php?option=com_modules&amp;task=edit&amp;module='. $row->module .'&amp;created=1&amp;client='. $this->client->id;
	if ( !$k ) {
		?>
		<tr class="<?php echo "row$x"; ?>" valign="top">
		<?php
		$x = 1 - $x;
	}
	?>
		<td width="50%">
			<span class="editlinktip hasTip" title="<?php echo JText::_(stripslashes( $row->name)).' :: '.JText::_(stripslashes( $row->descrip )); ?>" name="module" value="<?php echo $row->module; ?>" onclick="isChecked(this.checked);" /><input type="radio" name="module" value="<?php echo $row->module; ?>" id="cb<?php echo $i; ?>"><a href="<?php echo $link;?>"><?php echo JText::_($row->name); ?></a></span>
		</td>
	<?php
	if ( $k ) {
		?>
		</tr>
		<?php
	}
	?>
	<?php
	$k = 1 - $k;
}
?>
</tbody>
</table>

<input type="hidden" name="option" value="com_modules" />
<input type="hidden" name="client" value="<?php echo $this->client->id; ?>" />
<input type="hidden" name="created" value="1" />
<input type="hidden" name="task" value="edit" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::_( 'form.token' ); ?>
</form>
