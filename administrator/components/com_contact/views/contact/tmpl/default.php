<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		// do field validation
		if (form.name.value == ""){
			alert( "<?php echo JText::_( 'Contact item must have a name', true ); ?>" );
		} else if (form.catid.value == "0"){
			alert( "<?php echo JText::_( 'You must select a category', true ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
</script>
<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td class="key">
				<label for="name">
					<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td >
				<input class="inputbox" type="text" name="name" id="name" size="60" maxlength="255" value="<?php echo $this->contact->name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="name">
					<?php echo JText::_( 'Alias' ); ?>:
				</label>
			</td>
			<td >
				<input class="inputbox" type="text" name="alias" id="alias" size="60" maxlength="255" value="<?php echo $this->contact->alias; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->contact->published ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="catid">
					<?php echo JText::_( 'Category' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('list.category',  'catid', 'com_contact_details', intval( $this->contact->catid ) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="user_id">
					<?php echo JText::_( 'Linked to User' ); ?>:
				</label>
			</td>
			<td >
				<?php echo JHTML::_('list.users',  'user_id', $this->contact->user_id, 1, NULL, 'name', 0 ); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="ordering">
					<?php echo JText::_( 'Ordering' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('list.specificordering',  $this->contact, $this->contact->id, $this->order_query, 1 ); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="access">
					<?php echo JText::_( 'Access' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('list.accesslevel',  $this->contact ); ?>
			</td>
		</tr>
		<?php
		if ($this->contact->id) {
			?>
			<tr>
				<td class="key">
					<label>
						<?php echo JText::_( 'ID' ); ?>:
					</label>
				</td>
				<td>
					<strong><?php echo $this->contact->id;?></strong>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Information' ); ?></legend>

		<table class="admintable">
		<tr>
			<td class="key">
			<label for="con_position">
				<?php echo JText::_( 'Contact\'s Position' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="con_position" id="con_position" size="60" maxlength="255" value="<?php echo $this->contact->con_position; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="email_to">
					<?php echo JText::_( 'E-mail' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="email_to" id="email_to" size="60" maxlength="255" value="<?php echo $this->contact->email_to; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<label for="address">
					<?php echo JText::_( 'Street Address' ); ?>:
					</label>
				</td>
				<td>
					<textarea name="address" id="address" rows="3" cols="45" class="inputbox"><?php echo $this->contact->address; ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="suburb">
					<?php echo JText::_( 'Town/Suburb' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="suburb" id="suburb" size="60" maxlength="100" value="<?php echo $this->contact->suburb;?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="state">
					<?php echo JText::_( 'State/County' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="state" id="state" size="60" maxlength="100" value="<?php echo $this->contact->state;?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="postcode">
					<?php echo JText::_( 'Postal Code/ZIP' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="postcode" id="postcode" size="60" maxlength="100" value="<?php echo $this->contact->postcode; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="country">
					<?php echo JText::_( 'Country' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="country" id="country" size="60" maxlength="100" value="<?php echo $this->contact->country;?>" />
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
			<label for="telephone">
			<?php echo JText::_( 'Telephone' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="telephone" id="telephone" size="60" maxlength="255" value="<?php echo $this->contact->telephone; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<label for="mobile">
					<?php echo JText::_( 'Mobile' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mobile" id="mobile" size="60" maxlength="255" value="<?php echo $this->contact->mobile; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<label for="fax">
					<?php echo JText::_( 'Fax' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="fax" id="fax" size="60" maxlength="255" value="<?php echo $this->contact->fax; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="webpage">
					<?php echo JText::_( 'Webpage' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="webpage" id="webpage" size="60" maxlength="255" value="<?php echo $this->contact->webpage; ?>" />
			</td>
		</tr>
		<tr>
			<td  class="key" valign="top">
				<label for="misc">
					<?php echo JText::_( 'Miscellaneous Info' ); ?>:
				</label>
			</td>
			<td>
				<textarea name="misc" id="misc" rows="5" cols="45" class="inputbox"><?php echo $this->contact->misc; ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="image">
					<?php echo JText::_( 'Image' ); ?>:
				</label>
			</td>
			<td >
				<?php echo JHTML::_('list.images',  'image', $this->contact->image ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php
					$path = JURI::root() . 'images/';
					if ($this->contact->image != 'blank.png') {
						$path.= 'stories/';
					}
				?>
				<img src="<?php echo $path;?><?php echo $this->contact->image;?>" name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />
			</td>
		</tr>
		</table>
	</fieldset>
</div>

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Parameters' ); ?></legend>

		<?php
			jimport('joomla.html.pane');
			$pane =& JPane::getInstance('sliders');
			echo $pane->startPane("menu-pane");
			echo $pane->startPanel(JText :: _('Contact Parameters'), "param-page");
			echo $this->params->render();
			echo $pane->endPanel();
			echo $pane->startPanel(JText :: _('Advanced Parameters'), "param-page");
			echo $this->params->render('params', 'advanced');
			echo $pane->endPanel();
			echo $pane->startPanel(JText :: _('E-mail Parameters'), "param-page");
			echo $this->params->render('params', 'email');
			echo $pane->endPanel();
			echo $pane->endPane();
		?></fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
<input type="hidden" name="option" value="com_contact" />
<input type="hidden" name="cid[]" value="<?php echo $this->contact->id; ?>" />
<input type="hidden" name="task" value="" />
</form>