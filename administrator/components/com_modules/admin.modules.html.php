<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Modules
 */
class HTML_modules
{
	/**
	* Writes a list of the defined modules
	* @param array An array of category objects
	*/
	function view(&$rows, &$client, &$page, &$lists)
	{
		$user = &JFactory::getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'm.ordering' || $lists['order'] == 'm.position');

		JHtml::_('behavior.tooltip');
		?>
		<form action="index.php?option=com_modules" method="post" name="adminForm">

			<table>
			<tr>
				<td align="left" width="100%">
					<?php echo JText::_('Filter'); ?>:
					<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
					<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
				</td>
				<td nowrap="nowrap">
					<?php
					echo $lists['assigned'];
					echo $lists['position'];
					echo $lists['type'];
					echo $lists['state'];
					?>
				</td>
			</tr>
			</table>

			<table class="adminlist" cellspacing="1">
			<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('NUM'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows);?>);" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'Module Name', 'm.title', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th nowrap="nowrap" width="7%">
					<?php echo JHtml::_('grid.sort', 'Published', 'm.published', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th width="80" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort', 'Order', 'm.ordering', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th width="1%">
					<?php if ($ordering) echo JHtml::_('grid.order',  $rows); ?>
				</th>
				<th nowrap="nowrap" width="7%">
					<?php echo JHtml::_('grid.sort', 'Access', 'groupname', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th nowrap="nowrap" width="7%">
					<?php echo JHtml::_('grid.sort',   'Position', 'm.position', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort',   'Pages', 'pages', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th nowrap="nowrap" width="10%"  class="title">
					<?php echo JHtml::_('grid.sort',   'Type', 'm.module', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
				<th nowrap="nowrap" width="1%">
					<?php echo JHtml::_('grid.sort',   'ID', 'm.id', @$lists['order_Dir'], @$lists['order']); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			for ($i=0, $n=count($rows); $i < $n; $i++) {
				$row 	= &$rows[$i];
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td align="right">
						<?php echo $page->getRowOffset($i); ?>
					</td>
					<td width="20">
						<?php echo JHtml::_('grid.checkedout',   $row, $i); ?>
					</td>
					<td>
					<?php
					if (JTable::isCheckedOut($user->get ('id'), $row->checked_out)) {
						echo $row->title;
					} else {
						?>
						<span class="editlinktip hasTip" title="<?php echo JText::_('Edit Module');?>::<?php echo $row->title; ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_modules&client='. $client->id .'&task=edit&cid[]='. $row->id); ?>">
							<?php echo $row->title; ?></a>
						</span>
						<?php
					}
					?>
					</td>
					<td align="center">
						<?php echo JHtml::_('grid.published', $row, $i);?>
					</td>
					<td class="order" colspan="2">
						<span><?php echo $page->orderUpIcon($i, ($row->position == @$rows[$i-1]->position), 'orderup', 'Move Up', $ordering); ?></span>
						<span><?php echo $page->orderDownIcon($i, $n, ($row->position == @$rows[$i+1]->position),'orderdown', 'Move Down', $ordering); ?></span>
						<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $row->groupname; ?>
					</td>
					<td align="center">
						<?php echo $row->position; ?>
					</td>
					<td align="center">
						<?php
						if (is_null($row->pages)) {
							echo JText::_('None');
						} else if ($row->pages > 0) {
							echo JText::_('Varies');
						} else {
							echo JText::_('All');
						}
						?>
					</td>
					<td>
						<?php echo $row->module ? $row->module : JText::_('User');?>
					</td>
					<td>
						<?php echo $row->id;?>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="option" value="com_modules" />
		<input type="hidden" name="client" value="<?php echo $client->id;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param JTableCategory The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function edit(&$model, &$row, &$orders2, &$lists, &$params, $client)
	{
		JRequest::setVar('hidemainmenu', 1);

		// clean item data
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, 'content');

		// Check for component metadata.xml file
		//$path = JApplicationHelper::getPath('mod'.$client->id.'_xml', $row->module);
		//$params = new JParameter($row->params, $path);
		$document = &JFactory::getDocument();

		JHtml::_('behavior.combobox');

		jimport('joomla.html.pane');
        // TODO: allowAllClose should default true in J!1.6, so remove the array when it does.
		$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));
		$editor 	= &JFactory::getEditor();

		JHtml::_('behavior.tooltip');
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if ((pressbutton == 'save' || pressbutton == 'apply') && (document.adminForm.title.value == "")) {
				alert("<?php echo JText::_('Module must have a title', true); ?>");
			} else {
				<?php
				if ($row->module == '' || $row->module == 'mod_custom') {
					echo $editor->save('content');
				}
				?>
				submitform(pressbutton);
			}
		}
		<!--
		var originalOrder 	= '<?php echo $row->ordering;?>';
		var originalPos 	= '<?php echo $row->position;?>';
		var orders 			= new Array();	// array in the format [key,value,text]
		<?php	$i = 0;
		foreach ($orders2 as $k=>$items) {
			foreach ($items as $v) {
				echo "\n	orders[".$i++."] = new Array(\"$k\",\"$v->value\",\"$v->text\");";
			}
		}
		?>
		//-->
		</script>
		<form action="index.php" method="post" name="adminForm">
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
								<?php echo JText::_($row->module); ?>
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
							<input class="text_area" type="text" name="title" id="title" size="35" value="<?php echo $row->title; ?>" />
						</td>
					</tr>
					<tr>
						<td width="100" class="key">
							<?php echo JText::_('Show title'); ?>:
						</td>
						<td>
							<?php echo $lists['showtitle']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_('Published'); ?>:
						</td>
						<td>
							<?php echo $lists['published']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<label for="position" class="hasTip" title="<?php echo JText::_('MODULE_POSITION_TIP_TITLE', true); ?>::<?php echo JText::_('MODULE_POSITION_TIP_TEXT', true); ?>">
								<?php echo JText::_('Position'); ?>:
							</label>
						</td>
						<td>
							<input type="text" id="position" class="combobox" name="position" value="<?php echo $row->position; ?>" />
							<ul id="combobox-position" style="display:none;">
							<?php
								$positions = $model->getPositions();
								foreach ($positions as $position) {
									echo '<li>'.$position.'</li>';
								}
							?>
							</ul>
							<script language="javascript" type="text/javascript">
								window.addEvent('domready', function() {
									$('combobox-position').addEvent('change', function() {
										changeDynaList('ordering', orders, document.adminForm.position.value, 0, 0);
									});
									$('position').addEvent('change', function() {
										changeDynaList('ordering', orders, document.adminForm.position.value, 0, 0);
									});
								});
							</script>
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
							<?php echo $lists['access']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_('ID'); ?>:
						</td>
						<td>
							<?php echo $row->id; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_('Description'); ?>:
						</td>
						<td>
							<?php echo JText::_($row->description); ?>
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
						<?php if ($row->client_id != 1) : ?>
							<?php if ($row->pages == 'all') { ?>
							<label for="menus-all"><input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" checked="checked" /><?php echo JText::_('All'); ?></label>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" /><?php echo JText::_('None'); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_('Select From List'); ?></label>
							<?php } elseif ($row->pages == 'none') { ?>
							<label for="menus-all"><input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" /><?php echo JText::_('All'); ?></label>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" checked="checked" /><?php echo JText::_('None'); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_('Select From List'); ?></label>
							<?php } else { ?>
							<label for="menus-all"><input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" /><?php echo JText::_('All'); ?></label>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" /><?php echo JText::_('None'); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" checked="checked" /><?php echo JText::_('Select From List'); ?></label>
							<?php } ?>
						<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_('Menu Selection'); ?>:
						</td>
						<td>
							<?php echo $lists['selections']; ?>
						</td>
					</tr>
				</table>
				<?php if ($row->client_id != 1) : ?>
					<?php if ($row->pages == 'all') { ?>
					<script type="text/javascript">allselections();</script>
					<?php } elseif ($row->pages == 'none') { ?>
					<script type="text/javascript">disableselections();</script>
					<?php } else { ?>
					<?php } ?>
				<?php endif; ?>
			</fieldset>
		</div>

		<div class="col width-50">
			<fieldset class="adminform">
				<legend><?php echo JText::_('Parameters'); ?></legend>

				<?php
					echo $pane->startPane("menu-pane");
					echo $pane->startPanel(JText :: _('Module Parameters'), "param-page");
					$p = $params;
					if ($params = $p->render('params')) :
						echo $params;
					else :
						echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
					endif;
					echo $pane->endPanel();

					if ($p->getNumParams('advanced')) {
						echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
						if ($params = $p->render('params', 'advanced')) :
							echo $params;
						else :
							echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no advanced parameters for this item')."</div>";
						endif;
						echo $pane->endPanel();
					}

				if ($p->getNumParams('other')) {
					echo $pane->startPanel(JText :: _('Other Parameters'), "other-page");
					if ($params = $p->render('params', 'other')) :
						echo $params;
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
		if (!$row->module || $row->module == 'custom' || $row->module == 'mod_custom') {
			?>
			<fieldset class="adminform">
				<legend><?php echo JText::_('Custom Output'); ?></legend>

				<?php
				// parameters : areaname, content, width, height, cols, rows
				echo $editor->display('content', $row->content, '100%', '400', '60', '20', array('pagebreak', 'readmore')) ;
				?>

			</fieldset>
			<?php
		}
		?>

		<input type="hidden" name="option" value="com_modules" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="original" value="<?php echo $row->ordering; ?>" />
		<input type="hidden" name="module" value="<?php echo $row->module; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client->id ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php
	}

	function preview()
	{
		$editor = &JFactory::getEditor();

		?>
		<script>
		var form = window.top.document.adminForm
		var title = form.title.value;

		var alltext = window.top.<?php echo $editor->getContent('text') ?>;
		</script>

		<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
			<tr>
				<td class="contentheading" colspan="2"><script>document.write(title);</script></td>
			</tr>
		<tr>
			<script>document.write("<td valign=\"top\" height=\"90%\" colspan=\"2\">" + alltext + "</td>");</script>
		</tr>
		</table>
		<?php
	}

/**
	/**
	* Displays a selection list for module types
	*/
	function add(&$modules, $client)
	{
 		JHtml::_('behavior.tooltip');

		?>
		<form action="index.php" method="post" name="adminForm">

		<table class="adminlist" cellpadding="1" summary="Add Module">
		<thead>
		<tr>
			<th colspan="4">
			<?php echo JText::_('Modules'); ?>
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
		$altRow = 0;
		$count = count($modules);
		// Variable-column ready, just pass $cols in.
		$cols = 2;
		$pct = floor(100 / $cols);
		$rows = ceil($count / $cols);
		$posn = 0;
		do {
			?>
			<tr class="<?php echo 'row' . $altRow; ?>" valign="top">
			<?php
			$altRow = 1 - $altRow;
			for ($col = 0; $col < $cols; ++$col) :
				if (($mod = $posn + $col * $rows) >= $count) :
					?>
					<td width="<?php echo $pct; ?>%">&nbsp;</td>
					<?php
					continue;
				endif;
				$item = &$modules[$mod];
				$link = 'index.php?option=com_modules&amp;task=edit&amp;module='
					. $item->module . '&amp;created=1&amp;client=' . $client->id;
				?>
				<td width="<?php echo $pct; ?>%">
					<span class="editlinktip hasTip" title="<?php
						echo htmlspecialchars($item->name . ' :: '
							. JText::_(stripslashes($item->descrip)), ENT_QUOTES, 'UTF-8');
					?>" name="module" value="<?php
						echo $item->module;
					?>" onclick="isChecked(this.checked);">
					<input type="radio" name="module" value="<?php
						echo $item->module;
					?>" id="cb<?php echo $mod; ?>"/><a href="<?php
						echo $link;
					?>"><?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?></a></span>
				</td>
				<?php
			endfor;
			++$posn;
			?>
			</tr>
		<?php
		} while ($posn < $rows);
		?>
		</tbody>
		</table>

		<input type="hidden" name="option" value="com_modules" />
		<input type="hidden" name="client" value="<?php echo $client->id; ?>" />
		<input type="hidden" name="created" value="1" />
		<input type="hidden" name="task" value="edit" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}
}
