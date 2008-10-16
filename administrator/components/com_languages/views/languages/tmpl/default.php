<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

	<?php if($this->ftp): ?>
	<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
		<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

		<?php echo JText::_('DESCFTP'); ?>

		<?php if(JError::isError($ftp)): ?>
			<p><?php echo JText::_($ftp->message); ?></p>
		<?php endif; ?>

		<table class="adminform nospace">
		<tbody>
		<tr>
			<td width="120">
				<label for="username"><?php echo JText::_('Username'); ?>:</label>
			</td>
			<td>
				<input type="text" id="username" name="username" class="input_box" size="70" value="" />
			</td>
		</tr>
		<tr>
			<td width="120">
				<label for="password"><?php echo JText::_('Password'); ?>:</label>
			</td>
			<td>
				<input type="password" id="password" name="password" class="input_box" size="70" value="" />
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
	<?php endif; ?>

	<table class="adminlist">
	<thead>
	<tr>
		<th width="20">
			<?php echo JText::_( 'Num' ); ?>
		</th>
		<th width="30">
			&nbsp;
		</th>
		<th width="25%" class="title">
			<?php echo JText::_( 'Language' ); ?>
		</th>
		<th width="5%">
			<?php echo JText::_( 'Default' ); ?>
		</th>
		<th width="10%">
			<?php echo JText::_( 'Version' ); ?>
		</th>
		<th width="10%">
			<?php echo JText::_( 'Date' ); ?>
		</th>
		<th width="20%">
			<?php echo JText::_( 'Author' ); ?>
		</th>
		<th width="25%">
			<?php echo JText::_( 'Author Email' ); ?>
		</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="8">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
		$row = &$this->rows[$i];
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="20">
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td width="20">
				<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->language; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td width="25%">
				<?php echo $row->name;?>
			</td>
			<td width="5%" align="center">
				<?php
				if ($row->published == 1) {	 ?>
					<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_( 'Default' ); ?>" />
					<?php
				} else {
					?>
					&nbsp;
				<?php
				}
			?>
			</td>
			<td align="center">
				<?php echo $row->version; ?>
			</td>
			<td align="center">
				<?php echo $row->creationdate; ?>
			</td>
			<td align="center">
				<?php echo $row->author; ?>
			</td>
			<td align="center">
				<?php echo $row->authorEmail; ?>
			</td>
		</tr>
	<?php
	}
	?>
	</tbody>
	</table>

<input type="hidden" name="option" value="<?php echo JRequest::getCMD('option', 'com_langauges');?>" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
