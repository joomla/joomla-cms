<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	JHtml::_('behavior.combobox');

	jimport('joomla.html.pane');
	$pane = &JPane::getInstance('sliders');
	$editor = &JFactory::getEditor();

	JHtml::_('behavior.tooltip');
?>

<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	if ((pressbutton == 'save' || pressbutton == 'apply') && (document.adminForm.title.value == "")) {
		alert("<?php echo JText::_('Module must have a title', true); ?>");
	} else {
		<?php
		if ($this->row->module == '' || $this->row->module == 'mod_custom') {
			echo $editor->save('content');
		}
		?>
		submitform(pressbutton);
	}
}
<!--
var originalOrder 	= '<?php echo $this->row->ordering;?>';
var originalPos 	= '<?php echo $this->row->position;?>';
var orders 			= new Array();	// array in the format [key,value,text]
<?php	$i = 0;
foreach ($this->orders2 as $k=>$items) {
	foreach ($items as $v) {
		echo "\n	orders[".$i++."] = new Array(\"$k\",\"$v->value\",\"$v->text\");";
	}
}
?>
//-->
</script>
<form action="<?php echo JRoute::_('index.php');?>" method="post" name="adminForm">
<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Details'); ?></legend>

		<table class="admintable" cellspacing="1">
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('Module Type'); ?>:
				</td>
				<td>
					<strong>
						<?php echo JText::_($this->row->module); ?>
					</strong>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="title">
						<?php echo JText::_('Title'); ?>:
					</label>
				</td>
				<td>
					<input class="text_area" type="text" name="title" id="title" size="35" value="<?php echo $this->row->title; ?>" />
				</td>
			</tr>
			<tr>
				<td width="100" class="key">
					<?php echo JText::_('Show title'); ?>:
				</td>
				<td>
					<?php echo $this->lists['showtitle']; ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('Published'); ?>:
				</td>
				<td>
					<?php echo $this->lists['published']; ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="position" class="hasTip" title="<?php echo JText::_('MODULE_POSITION_TIP_TITLE', true); ?>::<?php echo JText::_('MODULE_POSITION_TIP_TEXT', true); ?>">
						<?php echo JText::_('Position'); ?>:
					</label>
				</td>
				<td>
					<input type="text" id="position" class="combobox" name="position" value="<?php echo $this->row->position; ?>" />
					<ul id="combobox-position" style="display:none;"><?php
					for ($i=0,$n=count($this->positions);$i<$n;$i++) {
						echo '<li>',$this->positions[$i],'</li>';
					}
					?></ul>
				</td>
			</tr>
			<tr>
				<td valign="top"  class="key">
					<label for="ordering">
						<?php echo JText::_('Order'); ?>:
					</label>
				</td>
				<td>
					<script language="javascript" type="text/javascript">
					<!--
					writeDynaList('class="inputbox" name="ordering" id="ordering" size="1"', orders, originalPos, originalPos, originalOrder);
					//-->
					</script>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="access">
						<?php echo JText::_('Access Level'); ?>:
					</label>
				</td>
				<td>
					<?php
					if ($this->row->client_id == 0) :
						echo JHtml::_('acl.assetgroups', $this->row->access);
					else :
						echo JText::_('N/A');
					endif;
					?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('ID'); ?>:
				</td>
				<td>
					<?php echo $this->row->id; ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('Description'); ?>:
				</td>
				<td>
					<?php echo JText::_($this->row->description); ?>
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
				<?php if ($this->row->client_id != 1) : ?>
					<input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" <?php
						echo ($this->row->pages == 'all') ? 'checked="checked"' : ''; ?> />
					<label for="menus-all"><?php echo JText::_('All'); ?></label>

					<input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" <?php
						echo ($this->row->pages == 'none') ? 'checked="checked"' : ''; ?> />
					<label for="menus-none"><?php echo JText::_('None'); ?></label>
					<br />
					<input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" <?php
						echo ($this->row->pages == 'select') ? 'checked="checked"' : ''; ?> />
					<label for="menus-select"><?php echo JText::_('Select From List'); ?></label>
					<br />
					<input id="menus-deselect" type="radio" name="menus" value="deselect" onclick="enableselections();" <?php
						echo ($this->row->pages == 'deselect') ? 'checked="checked"' : ''; ?> />
					<label for="menus-deselect"><?php echo JText::_('Deselect From List'); ?></label>
				<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<?php echo JText::_('Menu Selection'); ?>:
				</td>
				<td>
					<?php echo $this->lists['selections']; ?>
				</td>
			</tr>
		</table>
		<?php if ($this->row->client_id != 1) : ?>
			<?php if ($this->row->pages == 'all') : ?>
			<script type="text/javascript">allselections();</script>
			<?php elseif ($this->row->pages == 'none') : ?>
			<script type="text/javascript">disableselections();</script>
			<?php endif; ?>
		<?php endif; ?>
	</fieldset>
</div>

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Parameters'); ?></legend>

		<?php
			echo $pane->startPane("menu-pane");
			echo $pane->startPanel(JText :: _('Module Parameters'), "param-page");
			$p = $this->params;
			if ($this->params = $p->render('params')) :
				echo $this->params;
			else :
				echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
			endif;
			echo $pane->endPanel();

			if ($p->getNumParams('advanced')) {
				echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
				if ($this->params = $p->render('params', 'advanced')) :
					echo $this->params;
				else :
					echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no advanced parameters for this item')."</div>";
				endif;
				echo $pane->endPanel();
			}

			if ($p->getNumParams('legacy')) {
				echo $pane->startPanel(JText :: _('Legacy Parameters'), "legacy-page");
				if ($this->params = $p->render('params', 'legacy')) :
					echo $this->params;
				else :
					echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no legacy parameters for this item')."</div>";
				endif;
				echo $pane->endPanel();
			}


		if ($p->getNumParams('other')) {
			echo $pane->startPanel(JText :: _('Other Parameters'), "other-page");
			if ($this->params = $p->render('params', 'other')) :
				echo $this->params;
				else :
				echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no other parameters for this item')."</div>";
				endif;
				echo $pane->endPanel();
			}
			echo $pane->endPane();
		?>
	</fieldset>
</div>
<div class="clr"></div>

<?php
if (!$this->row->module || $this->row->module == 'custom' || $this->row->module == 'mod_custom') {
	?>
	<fieldset class="adminform">
		<legend><?php echo JText::_('Custom Output'); ?></legend>

		<?php
		// parameters : areaname, content, width, height, cols, rows
		echo $editor->display('content', $this->row->content, '100%', '400', '60', '20', array('pagebreak', 'readmore')) ;
		?>

	</fieldset>
	<?php
}
?>

<input type="hidden" name="option" value="com_modules" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="original" value="<?php echo $this->row->ordering; ?>" />
<input type="hidden" name="module" value="<?php echo $this->row->module; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
