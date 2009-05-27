<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 */
class HTML_newsfeeds
{
	function showNewsFeeds(&$rows, &$lists, &$pageNav, $option)
	{
		global $mainframe;

		$user = &JFactory::getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'a.ordering');

		JHtml::_('behavior.tooltip');
		?>
		<form action="index.php?option=com_newsfeeds" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_('Filter'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['catid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

			<table class="adminlist">
			<thead>
				<tr>
					<th width="10">
						<?php echo JText::_('NUM'); ?>
					</th>
					<th width="10">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort',   'News Feed', 'a.name', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort',   'Published', 'a.published', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="8%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'Order', 'a.ordering', @$lists['order_Dir'], @$lists['order']); ?>
						<?php if ($ordering) echo JHtml::_('grid.order',  $rows); ?>
					</th>
					<th class="title" width="10%">
						<?php echo JHtml::_('grid.sort',   'Category', 'catname', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'Num Articles', 'a.numarticles', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort',   'Cache time', 'a.cache_time', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="1%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'ID', 'a.id', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $pageNav->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count($rows); $i < $n; $i++) {
				$row = &$rows[$i];

				$link 		= JRoute::_('index.php?option=com_newsfeeds&task=edit&cid[]='. $row->id);

				$checked 	= JHtml::_('grid.checkedout',   $row, $i);
				$published 	= JHtml::_('grid.published', $row, $i);

				$row->cat_link 	= JRoute::_('index.php?option=com_categories&section=com_newsfeeds&task=edit&cid[]='. $row->catid);
				?>
				<tr class="<?php echo 'row'. $k; ?>">
					<td align="center">
						<?php echo $pageNav->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<?php
						if (JTable::isCheckedOut($user->get ('id'), $row->checked_out)) {
							echo $row->name;
						} else {
							?>
								<span class="editlinktip hasTip" title="<?php echo JText::_('Edit Newsfeed');?>::<?php echo $row->name; ?>">
							<a href="<?php echo $link; ?>">
								<?php echo $row->name; ?></a></span>
							<?php
						}
						?>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td class="order">
						<span><?php echo $pageNav->orderUpIcon($i, ($row->catid == @$rows[$i-1]->catid), 'orderup', 'Move Up', $ordering); ?></span>
						<span><?php echo $pageNav->orderDownIcon($i, $n, ($row->catid == @$rows[$i+1]->catid), 'orderdown', 'Move Down', $ordering); ?></span>
						<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_('Edit Category'); ?>">
							<?php echo $row->catname;?></a>
					</td>
					<td align="center">
						<?php echo $row->numarticles;?>
					</td>
					<td align="center">
						<?php echo $row->cache_time;?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>

			<table class="adminform">
			<tr>
				<td>
					<table align="center">
					<?php
					$visible = 0;
					// check to hide certain paths if not super admin
					if ($user->authorise('core.cache.manage')) {
						$visible = 1;
					}
					HTML_newsfeeds::writableCell(JPATH_SITE.DS.'cache', 0, '<strong>'. JText::_('Cache Directory') .'</strong> ', $visible);
					?>
					</table>
				</td>
			</tr>
			</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}

	function editNewsFeed(&$row, &$lists, $option)
	{
		JRequest::setVar('hidemainmenu', 1);

		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES);
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform(pressbutton);
				return;
			}

			// do field validation
			if (form.name.value == '') {
				alert("<?php echo JText::_('Please fill in the newsfeed name.', true); ?>");
			} else if (form.catid.value == 0) {
				alert("<?php echo JText::_('Please select a Category.', true); ?>");
			} else if (form.link.value == '') {
				alert("<?php echo JText::_('Please fill in the newsfeed link.', true); ?>");
			} else if (getSelectedValue('adminForm','catid') < 0) {
				alert("<?php echo JText::_('Please select a category.', true); ?>");
			} else if (form.numarticles.value == "" || form.numarticles.value == 0) {
				alert("<?php echo JText::_('VALIDARTICLESDISPLAY', true); ?>");
			} else if (form.cache_time.value == "" || form.cache_time.value == 0) {
				alert("<?php echo JText::_('Please fill in the cache refresh time.', true); ?>");
			} else {
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index.php" method="post" name="adminForm">

		<div class="col100">
			<fieldset class="adminform">
				<legend><?php echo JText::_('Details'); ?></legend>

				<table class="admintable">
				<tr>
					<td width="170" class="key">
						<label for="name">
							<?php echo JText::_('Name'); ?>
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" size="40" name="name" id="name" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td width="170" class="key">
						<label for="name">
							<?php echo JText::_('Alias'); ?>
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" size="40" name="alias" id="alias" value="<?php echo $row->alias; ?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="key">
						<?php echo JText::_('Published'); ?>:
					</td>
					<td>
						<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="catid">
							<?php echo JText::_('Category'); ?>
						</label>
					</td>
					<td>
						<?php echo $lists['category']; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="link">
							<?php echo JText::_('Link'); ?>
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" size="60" name="link" id="link" value="<?php echo $row->link; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="numarticles">
							<?php echo JText::_('Number of Articles'); ?>
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" size="2" name="numarticles" id="numarticles" value="<?php echo $row->numarticles; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="editlinktip hasTip" title="<?php echo JText::_('TIPCACHETIME'); ?>">
					<?php echo JText::_('Cache time'); ?>
				</span>
					</td>
					<td>
						<input class="inputbox" type="text" size="4" name="cache_time" id="cache_time" value="<?php echo $row->cache_time; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="ordering">
							<?php echo JText::_('Ordering'); ?>
						</label>
					</td>
					<td>
						<?php echo $lists['ordering']; ?>
					</td>
				</tr>

				<tr>
					<td class="key">
						<label for="rtl">
							<?php echo JText::_('RTL feed'); ?>
						</label>
					</td>
					<td>
						<?php echo $lists['rtl']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
					</td>
				</tr>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php
	}

	function writableCell($folder, $relative=1, $text='', $visible=1)
	{
		$writeable 		= '<b><font color="green">'. JText::_('Writable') .'</font></b>';
		$unwriteable 	= '<b><font color="red">'. JText::_('Unwritable') .'</font></b>';

		echo '<tr>';
		echo '<td class="item">';
		echo $text;
		if ($visible) {

			echo $folder . '/';
		}
		echo '</td>';
		echo '<td >';
		if ($relative) {
			echo is_writable("../$folder") 	? $writeable : $unwriteable;
		} else {
			echo is_writable("$folder") 		? $writeable : $unwriteable;
		}
		echo '</td>';
		echo '</tr>';
	}
}