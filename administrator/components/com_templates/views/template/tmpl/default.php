<?php defined('_JEXEC') or die; ?>

<?php
	JHtml::_('behavior.tooltip');
	
	$page= JRoute::_('index.php');
?>
<form action="<?php echo $page; ?>"  method="post" name="styleForm">
<?php echo JText::_('Style'); ?>: 
	<select name="id" id="id" onChange="document.styleForm.submit();">
<?php for ($i = 0, $n = count($this->style); $i < $n; $i++) {
?>
<option <?php echo $this->style[$i]->id==$this->row->id ? 'selected="selected"' : ''; ?>value="<?php echo $this->style[$i]->id; ?>"><?php echo JText::_($this->row->template); ?> - <?php echo $this->style[$i]->description; ?> <?php echo $this->style[$i]->home ? '('.JText::_('DEFAULT').')' : ''; ?> <?php echo $this->style[$i]->assigned ? '('.JText::_('ASSIGNED').')' : ''; ?></option>
<?php
}
?>
	</select>
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="edit" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
</form>
<form action="<?php echo $page; ?>" method="post" name="adminForm">

<?php if ($this->ftp): ?>
<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>" class="adminform">
	<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

	<?php echo JText::_('DESCFTP'); ?>

	<?php if (JError::isError($this->ftp)): ?>
		<p><?php echo JText::_($this->ftp->message); ?></p>
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

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Details'); ?></legend>

		<table class="admintable">
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_('Name'); ?>:
			</td>
			<td>
				<strong>
					<?php echo JText::_($this->row->template); ?> - <input class="inputbox" type="text" name="description" id="description" size="40" maxlength="255" value="<?php echo $this->row->description; ?>" />
					
				</strong>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_('Description'); ?>:
			</td>
			<td>
				<?php echo JText::_($this->row->xmldata->description); ?>
			</td>
		</tr>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_('Menu Assignment'); ?></legend>
		<script type="text/javascript">
			function allselections() {
				var e = document.getElementById('selections');
					e.disabled = true;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = true;
					e.options[i].selected = true;
				}
			}
			function disableselections() {
				var e = document.getElementById('selections');
					e.disabled = true;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = true;
					e.options[i].selected = false;
				}
			}
			function enableselections() {
				var e = document.getElementById('selections');
					e.disabled = false;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = false;
				}
			}
		</script>
		<table class="admintable" cellspacing="1">
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('Menus'); ?>:
				</td>
				<td>
					<?php if ($this->client->id == 1) {
							echo JText::_('Cannot assign administrator template');
						  } elseif ($this->row->pages == 'all') { ?>
					<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();"  /><?php echo JText::_('None'); ?></label>
					<label for="menus-default"><input id="menus-default" type="radio" name="menus" value="default" onclick="disableselections();" checked="checked" /><?php echo JText::_('Default'); ?></label>
					<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_('Select From List'); ?></label>
					<?php } elseif ($this->row->pages == 'none') { ?>
					<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" checked="checked" /><?php echo JText::_('None'); ?></label>
					<label for="menus-default"><input id="menus-default" type="radio" name="menus" value="default" onclick="disableselections();" /><?php echo JText::_('Default'); ?></label>
					<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_('Select From List'); ?></label>
					<?php } else { ?>
					<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" /><?php echo JText::_('None'); ?></label>
					<label for="menus-default"><input id="menus-default" type="radio" name="menus" value="default" onclick="disableselections();" /><?php echo JText::_('Default'); ?></label>
					<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" checked="checked" /><?php echo JText::_('Select From List'); ?></label>
					
					<?php } ?>
				</td>
			</tr>
			<?php if ($this->client->id != 1) : ?>
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('Menu Selection'); ?>:
				</td>
				<td>
					<?php echo $this->lists['selections']; ?>
					<?php if ($this->row->pages == 'none' || $this->row->pages == 'all') { ?>
					<script type="text/javascript">disableselections();</script>
					<?php } ?>
				</td>
			</tr>
			<?php endif; ?>
		</table>
	</fieldset>
</div>

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Parameters'); ?></legend>
		<table class="admintable">
		<tr>
			<td>
			<?php
			if (!is_null($this->params)) {
				echo $this->params->render();
			} else {
				echo '<i>' . JText :: _('No Parameters') . '</i>';
			}
			?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="template" value="<?php echo $this->row->template; ?>" />

<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
