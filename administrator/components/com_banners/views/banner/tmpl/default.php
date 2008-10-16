<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
	JHTML::_('behavior.tooltip');
?>

<script language="javascript" type="text/javascript">
<!--
function changeDisplayImage() {
	if (document.adminForm.imageurl.value !='') {
		document.adminForm.imagelib.src='../images/banners/' + document.adminForm.imageurl.value;
	} else {
		document.adminForm.imagelib.src='images/blank.png';
	}
}
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
	// do field validation
	if (form.name.value == "") {
		alert( "<?php echo JText::_( 'You must provide a banner name.', true ); ?>" );
	} else if (getSelectedValue('adminForm','cid') < 1) {
		alert( "<?php echo JText::_( 'Please select a client.', true ); ?>" );
	/*} else if (!getSelectedValue('adminForm','imageurl')) {
		alert( "<?php echo JText::_( 'Please select an image.', true ); ?>" );*/
	/*} else if (form.clickurl.value == "") {
		alert( "<?php echo JText::_( 'Please fill in the URL for the banner.', true ); ?>" );*/
	} else if ( getSelectedValue('adminForm','catid') == 0 ) {
		alert( "<?php echo JText::_( 'Please select a category.', true ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
//-->
</script>
<form action="<?php echo JRoute::_("index.php"); ?>" method="post" name="adminForm">

<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tbody>
			<tr>
				<td width="20%" class="key">
					<label for="name">
						<?php echo JText::_( 'Name' ); ?>:
					</label>
				</td>
				<td width="80%">
					<input class="inputbox" type="text" name="name" id="name" size="50" value="<?php echo $this->row->name;?>" />
				</td>
			</tr>
			<tr>
				<td width="20%" class="key">
					<label for="alias">
						<?php echo JText::_( 'Alias' ); ?>:
					</label>
				</td>
				<td width="80%">
					<input class="inputbox" type="text" name="alias" id="alias" size="50" value="<?php echo $this->row->alias;?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_( 'Show Banner' ); ?>:
				</td>
				<td>
					<?php echo $this->lists['showBanner']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_( 'Sticky' ); ?>:
				</td>
				<td>
					<?php echo $this->lists['sticky']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="ordering">
						<?php echo JText::_( 'Ordering' ); ?>:
					</label>
				</td>
				<td>
					<?php echo JHTML::_('list.specificordering',  $this->row, $this->row->bid, $this->order_query, 1 ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top" align="right" class="key">
					<label for="catid">
						<?php echo JText::_( 'Category' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $this->lists['catid']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="cid">
						<?php echo JText::_( 'Client Name' ); ?>:
					</label>
				</td>
				<td >
					<?php echo $this->lists['cid']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="imptotal">
						<?php echo JText::_( 'Impressions Purchased' ); ?>:
					</label>
				</td>
				<?php
				$unlimited = '';
				if ($this->row->imptotal == 0) {
					$unlimited = 'checked="checked"';
					$this->row->imptotal = '';
				}
				?>
				<td>
					<input class="inputbox" type="text" name="imptotal" id="imptotal" size="12" maxlength="11" value="<?php echo $this->row->imptotal;?>" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label for="unlimited">
						<?php echo JText::_( 'Unlimited' ); ?>
					</label>
					<input type="checkbox" name="unlimited" id="unlimited" <?php echo $unlimited;?> />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="clickurl">
						<?php echo JText::_( 'Click URL' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="clickurl" id="clickurl" size="100" maxlength="200" value="<?php echo $this->row->clickurl;?>" />
				</td>
			</tr>
			<tr >
				<td valign="top" align="right" class="key">
					<?php echo JText::_( 'Clicks' ); ?>:
				</td>
				<td colspan="2">
					<?php echo $this->row->clicks;?>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<input name="reset_hits" type="button" class="button" value="<?php echo JText::_( 'Reset Clicks' ); ?>" onclick="submitbutton('resethits');" />
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="custombannercode">
						<?php echo JText::_( 'Custom banner code' ); ?>:
					</label>
				</td>
				<td>
					<textarea class="inputbox" cols="70" rows="8" name="custombannercode" id="custombannercode"><?php echo $this->row->custombannercode;?></textarea>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="description">
						<?php echo JText::_( 'Description/Notes' ); ?>:
					</label>
				</td>
				<td>
					<textarea class="inputbox" cols="70" rows="3" name="description" id="description"><?php echo $this->row->description;?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="3">
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="imageurl">
						<?php echo JText::_( 'Banner Image Selector' ); ?>:
					</label>
				</td>
				<td >
					<?php echo $this->lists['imageurl']; ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_( 'Banner Image' ); ?>:
				</td>
				<td valign="top">
					<?php
					if (eregi("swf", $this->row->imageurl)) {
						?>
						<img src="images/blank.png" name="imagelib">
						<?php
					} elseif (eregi("gif|jpg|png", $this->row->imageurl)) {
						?>
						<img src="../images/banners/<?php echo $this->row->imageurl; ?>" name="imagelib" />
						<?php
					} else {
						?>
						<img src="images/blank.png" name="imagelib" />
						<?php
					}
					?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="tags">
						<?php echo JText::_( 'Tags' ); ?>:
					</label>
				</td>
				<td>
					<textarea class="inputbox" cols="70" rows="3" name="tags" id="tags"><?php echo $this->row->tags;?></textarea>
				</td>
			</tr>
		</tbody>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="c" value="banner" />
<input type="hidden" name="option" value="com_banners" />
<input type="hidden" name="bid" value="<?php echo $this->row->bid; ?>" />
<input type="hidden" name="clicks" value="<?php echo $this->row->clicks; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="impmade" value="<?php echo $this->row->impmade; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
