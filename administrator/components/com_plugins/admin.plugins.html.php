<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Plugins
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Plugins
*/
class HTML_modules {

	/**
	* Writes a list of the defined modules
	* @param array An array of category objects
	*/
	function showPlugins( &$rows, $client, &$page, $option, &$lists )
	{
		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$user =& JFactory::getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'p.folder');

		JHTML::_('behavior.tooltip');
		?>
		<form action="index.php?option=com_plugins" method="post" name="adminForm">
			<table>
			<tr>
				<td align="left" width="100%">
					<?php echo JText::_( 'Filter' ); ?>:
					<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
					<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
				</td>
				<td nowrap="nowrap">
					<?php
					echo $lists['type'];
					echo $lists['state'];
					?>
				</td>
			</tr>
			</table>

			<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort',   'Plugin Name', 'p.name', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th nowrap="nowrap" width="5%">
						<?php echo JHTML::_('grid.sort',   'Published', 'p.published', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="80" nowrap="nowrap">
						<?php echo JHTML::_('grid.sort',   'Order', 'p.folder', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="1%">
						<?php echo JHTML::_('grid.order',  $rows ); ?>
					</th>
					<th nowrap="nowrap" width="7%">
						<?php echo JHTML::_('grid.sort',   'Access', 'groupname', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th nowrap="nowrap"  width="3%" class="title">
						<?php echo JHTML::_('grid.sort',   'ID', 'p.id', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th nowrap="nowrap"  width="13%" class="title">
						<?php echo JHTML::_('grid.sort',   'Type', 'p.folder', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th nowrap="nowrap"  width="13%" class="title">
						<?php echo JHTML::_('grid.sort',   'File', 'p.element', @$lists['order_Dir'], @$lists['order'] ); ?>
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
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row 	= &$rows[$i];

				$link = JRoute::_( 'index.php?option=com_plugins&client='. $client .'&task=edit&cid[]='. $row->id );

				$access 	= JHTML::_('grid.access',   $row, $i );
				$checked 	= JHTML::_('grid.checkedout',   $row, $i );
				$published 	= JHTML::_('grid.published', $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="right">
						<?php echo $page->getRowOffset( $i ); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
					<?php
					if (  JTable::isCheckedOut($user->get ('id'), $row->checked_out ) ) {
						echo $row->name;
					} else {
						?>
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?></a>
						<?php
					}
					?>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td class="order" colspan="2">
						<span><?php echo $page->orderUpIcon( $i, ($row->folder == @$rows[$i-1]->folder && $row->ordering > -10000 && $row->ordering < 10000), 'orderup', 'Move Up', $ordering ); ?></span>
						<span><?php echo $page->orderDownIcon( $i, $n, ($row->folder == @$rows[$i+1]->folder && $row->ordering > -10000 && $row->ordering < 10000), 'orderdown', 'Move Down', $ordering ); ?></span>
						<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"  <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td nowrap="nowrap">
						<?php echo $row->id;?>
					</td>
					<td nowrap="nowrap">
						<?php echo $row->folder;?>
					</td>
					<td nowrap="nowrap">
						<?php echo $row->element;?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_client" value="<?php echo $client;?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param JCategoryModel The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function editPlugin( &$row, &$lists, &$params, $option )
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		JHTML::_('behavior.tooltip');

		$row->nameA = '';
		if ( $row->id ) {
			$row->nameA = '<small><small>[ '. $row->name .' ]</small></small>';
		}
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == "cancel") {
				submitform(pressbutton);
				return;
			}
			// validation
			var form = document.adminForm;
			if (form.name.value == "") {
				alert( "<?php echo JText::_( 'Plugin must have a name', true ); ?>" );
			} else if (form.element.value == "") {
				alert( "<?php echo JText::_( 'Plugin must have a filename', true ); ?>" );
			} else {
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index.php" method="post" name="adminForm">

		<div class="col60">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
				<tr>
					<td width="100" class="key">
						<label for="name">
							<?php echo JText::_( 'Name' ); ?>:
						</label>
					</td>
					<td>
						<input class="text_area" type="text" name="name" id="name" size="35" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Published' ); ?>:
					</td>
					<td>
						<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="folder">
							<?php echo JText::_( 'Type' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $row->folder; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="element">
							<?php echo JText::_( 'Plugin file' ); ?>:
						</label>
					</td>
					<td>
						<input class="text_area" type="text" name="element" id="element" size="35" value="<?php echo $row->element; ?>" />.php
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="access">
							<?php echo JText::_( 'Access Level' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Order' ); ?>:
					</td>
					<td>
						<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Description' ); ?>:
					</td>
					<td>
						<?php echo JText::_( $row->description ); ?>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>

		<div class="col40">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Parameters' ); ?></legend>

				<?php
					jimport('joomla.html.pane');
					$pane =& JPane::getInstance('sliders');
					echo $pane->startPane("plugin-pane");
					echo $pane->startPanel(JText :: _('Plugin Parameters'), "param-page");
					if($output = $params->render('params')) :
						echo $output;
					else :
						echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
					endif;
					echo $pane->endPanel();

					if ($params->getNumParams('advanced')) {
						echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
						if($output = $params->render('params', 'advanced')) :
							echo $output;
						else :
							echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no advanced parameters for this item')."</div>";
						endif;
						echo $pane->endPanel();
					}

					if ($params->getNumParams('legacy')) {
						echo $pane->startPanel(JText :: _('Legacy Parameters'), "legacy-page");
						if($output = $params->render('params', 'legacy')) :
							echo $output;
						else :
							echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no legacy parameters for this item')."</div>";
						endif;
						echo $pane->endPanel();
					}
					echo $pane->endPane();
				?>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="client" value="<?php echo $row->client_id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
