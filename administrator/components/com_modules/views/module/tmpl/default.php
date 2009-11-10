<?php
/**
 * @version	
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
?>
<?php defined('_JEXEC') or die; ?>

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
<div class="width-50 fltlft">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Details'); ?></legend>

			<label id="jform_title-lbl" class="hasTip" for="jform_title"><?php echo JText::_('Title'); ?>:</label>
			<input id="jform_title" class="inputbox required" type="text" size="35" name="jform[title]" value="<?php echo $this->row->title; ?>" />

			<label id="jform_type-lbl" class="hasTip" for="jform_type"><?php echo JText::_('Module Type'); ?>:</label>
			<input id="jform_type" class="readonly" type="text" readonly="readonly" size="16" value="<?php echo JText::_($this->row->module); ?>" name="jform[type]"/>
		
			<label id="jform_showtitle-lbl" class="hasTip" for="jform_showtitle"><?php echo JText::_('Show title'); ?>:</label>
			<fieldset id="jform_showtitle" class="radio">
				<div class="jform_mod_title"><?php echo $this->lists['showtitle']; ?></div>
			</fieldset>
		
			<label id="jform_published-lbl" class="hasTip" for="jform_published"><?php echo JText::_('Published'); ?>:</label>
			<fieldset id="jform_published" class="radio">
					<?php echo $this->lists['published']; ?>
			</fieldset>
			<label id="jform_position-lbl" class="hasTip" for="jform_position" title="<?php echo JText::_('MODULE_POSITION_TIP_TITLE', true); ?>::<?php echo JText::_('MODULE_POSITION_TIP_TEXT', true); ?>">
						<?php echo JText::_('Position'); ?>:
					</label>
			
			<select id="jform_position" class="inputbox" size="1" name="jform[position]">
			<?php
					foreach ($this->positions as $position) {
						echo '<option value="'.$position.'"'.($this->row->position == $position ? ' selected="selected"' : '').'>'.$position.'</option>';
					}
					?>
					</select>

			<label id="jform_ordering-lbl" class="hasTip" for="jform_ordering"><?php echo JText::_('Order'); ?>:</label>
					<script language="javascript" type="text/javascript">
					<!--
					writeDynaList('class="inputbox" name="jform[ordering]" id="ordering" size="1"', orders, originalPos, originalPos, originalOrder);
					//-->
					</script>

			<label id="jform_access-lbl" class="hasTip" for="jform_access"><?php echo JText::_('Access Level'); ?>:</label>
					<?php
					if ($this->row->client_id == 0) :
						echo JHtml::_('access.assetgrouplist', 'access', $this->row->access);
					else :
						echo "<div class='jform_na'>";
						echo JText::_('N/A');
						echo "</div>";
					endif;
					?>

			<label id="jform_id-lbl" class="hasTip" for="jform_id"><?php echo JText::_('ID'); ?>:</label>
			<input id="jform_id" class="readonly" type="text" readonly="readonly" size="4" value="<?php echo $this->row->id; ?>" name="jform[id]"/>

			<label id="jform_description-lbl" class="hasTip" for="jform_description"><?php echo JText::_('Description'); ?>:</label>
			<p class="jform_desc"><?php echo JText::_($this->row->description); ?></p>
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
					e.options[i].selected = true;				
				}
			}
		</script>
	<!-- TO DO: Need to rework UI for this section -->
			<label id="jform_menus-lbl" class="hasTip" for="jform_menus"><?php echo JText::_('Menus'); ?>:</label>
				<?php if ($this->row->client_id != 1) : ?>
				
			<fieldset id="jform_menus" class="radio">
				<label id="jform_menus-all-lbl" for="menus-all"><?php echo JText::_('All'); ?></label>
				<input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" <?php
						echo ($this->row->pages == 'all') ? 'checked="checked"' : ''; ?> />
			
				<label id="jform_menus-none-lbl" for="menus-none"><?php echo JText::_('None'); ?></label>	
				<input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" <?php
						echo ($this->row->pages == 'none') ? 'checked="checked"' : ''; ?> />
			
				<label id="jform_menus-select-lbl" for="menus-select"><?php echo JText::_('Select From List'); ?></label>	
				<input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" <?php
						echo ($this->row->pages == 'select') ? 'checked="checked"' : ''; ?> />
						
				<label id="jform_menus-deselect-lbl" for="menus-deselect"><?php echo JText::_('Deselect From List'); ?></label>
				<input id="menus-deselect" type="radio" name="menus" value="deselect" onclick="enableselections();" <?php
						echo ($this->row->pages == 'deselect') ? 'checked="checked"' : ''; ?> />
			</fieldset>	
				<?php endif; ?>
				
			<label id="jform_menuselect-lbl" class="hasTip" for="jform_menuselect"><?php echo JText::_('Menu Selection'); ?>:</label>
					<?php echo $this->lists['selections']; ?>
			
		<?php if ($this->row->client_id != 1) : ?>
			<?php if ($this->row->pages == 'all') : ?>
			<script type="text/javascript">allselections();</script>
			<?php elseif ($this->row->pages == 'none') : ?>
			<script type="text/javascript">disableselections();</script>
			<?php endif; ?>
		<?php endif; ?>
	</fieldset>
</div>

<div class="width-50 fltrt">
		<?php
			echo $pane->startPane("menu-pane");
			echo $pane->startPanel(JText :: _('Module Parameters'), "param-page");
			$p = $this->params;
			if ($this->params = $p->render('jform[params]')) :
				echo "<fieldset class=\"panelform-legacy\">".$this->params."</fieldset>";
			else :
				echo "<div class=\"noparams-notice\">".JText::_('There are no parameters for this item')."</div>";
			endif;
			echo $pane->endPanel();

			if ($p->getNumParams('advanced')) {
				echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
				if ($this->params = $p->render('jform[params]', 'advanced')) :
					echo "<fieldset class=\"panelform-legacy\">".$this->params."</fieldset>";
				else :
					echo "<div class=\"noparams-notice\">".JText::_('There are no advanced parameters for this item')."</div>";
				endif;
				echo $pane->endPanel();
			}

			if ($p->getNumParams('legacy')) {
				echo $pane->startPanel(JText :: _('Legacy Parameters'), "legacy-page");
				if ($this->params = $p->render('jform[params]', 'legacy')) :
					echo "<fieldset class=\"panelform-legacy\">".$this->params."</fieldset>";
				else :
					echo "<div class=\"noparams-notice\">".JText::_('There are no legacy parameters for this item')."</div>";
				endif;
				echo $pane->endPanel();
			}


		if ($p->getNumParams('other')) {
			echo $pane->startPanel(JText :: _('Other Parameters'), "other-page");
			if ($this->params = $p->render('jform[params]', 'other')) :
				echo "<fieldset class=\"panelform-legacy\">".$this->params."</fieldset>";
				else :
				echo "<div class=\"noparams-notice\">".JText::_('There are no other parameters for this item')."</div>";
				endif;
				echo $pane->endPanel();
			}
			echo $pane->endPane();
		?>
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
<input type="hidden" name="jform[id]" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="original" value="<?php echo $this->row->ordering; ?>" />
<input type="hidden" name="jform[module]" value="<?php echo $this->row->module; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="jform[client_id]" value="<?php echo $this->client->id ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
