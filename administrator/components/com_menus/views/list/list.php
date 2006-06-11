<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.view');

/**
 * @package Joomla
 * @subpackage Menus
 * @since 1.5
 */
class JMenuViewList extends JView
{
	function display()
	{
		$document = &$this->getDocument();
		$document->setTitle('View Menu Items');

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');
		$lists		= &$this->_getViewLists();

		$app		= &$this->get('Application');
		$menutype 	= $app->getUserStateFromRequest( "com_menus.menutype", 'menutype', 'mainmenu' );
		$user		= &$app->getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'm.ordering');

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_menus&amp;menutype=<?php echo $menutype; ?>" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo JText::_( 'Max Levels' );
				echo $lists['levellist'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($items); ?>);" />
					</th>
					<th class="title" width="30%">
						<?php mosCommonHTML::tableOrdering( 'Menu Item', 'm.name', $lists ); ?>
					</th>
					<th width="5%">
						<?php echo JText::_( 'Default' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Published', 'm.published', $lists ); ?>
					</th>
					<th width="80" nowrap="nowrap">
						<a href="javascript:tableOrdering('m.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
							<?php echo JText::_( 'Order' ); ?>
						</a>
					</th>
					<th width="1%">
						<?php mosCommonHTML::saveorderButton( $items ); ?>
					</th>
					<th width="10%">
						<?php mosCommonHTML::tableOrdering( 'Access', 'groupname', $lists ); ?>
					</th>
					<th width="10%" class="title">
						<?php mosCommonHTML::tableOrdering( 'Type', 'm.type', $lists ); ?>
					</th>
					<th nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Itemid', 'm.id', $lists ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="12">
					<?php echo $pagination->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$i = 0;
			$n = count( $items );
			foreach ($items as $row) {
				$access 	= mosCommonHTML::AccessProcessing( $row, $i );
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $i + 1 + $pagination->limitstart;?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td nowrap="nowrap">
						<?php
						if ( $row->checked_out && ( $row->checked_out != $user->get('id') ) ) {
							echo $row->treename;
						} else {
							$link = 'index2.php?option=com_menus&menutype='.$row->menutype.'&task=edit&cid[]='.$row->id.'&hidemainmenu=1';
							?>
							<a href="<?php echo ampReplace( $link ); ?>">
								<?php echo $row->treename; ?></a>
							<?php
						}
						?>
					</td>
					<td align="center">
						<?php if ( $row->home == 1 ) { ?>
						<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_( 'Default' ); ?>" />
						<?php } else { ?>
						&nbsp;
						<?php } ?>
					</td>
					<td width="10%" align="center">
						<?php echo $published;?>
					</td>
					<td class="order" colspan="2" nowrap="nowrap">
						<span><?php echo $pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering); ?></span>
						<span><?php echo $pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
						<?php $disabled = $ordering ?  '' : '"disabled=disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td>
						<span class="editlinktip" style="text-transform:capitalize">
							<?php
							//echo mosToolTip( $row->descrip, '', 280, 'tooltip.png', $row->type, $row->edit, !empty($row->edit) );
							echo $row->type
							?>
						</span>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
	<?php
	}

	function copyForm()
	{
		$document = &$this->getDocument();
		$document->setTitle('Copy Menu Items');

		$app		= &$this->get('Application');
		$menutype 	= $app->getUserStateFromRequest( "com_menus.menutype", 'menutype', 'mainmenu' );

		// Build the menutypes select list
		$menuTypes 	= $this->get('MenuTypes');
		foreach ( $menuTypes as $menuType ) {
			$menu[] = mosHTML::makeOption( $menuType, $menuType );
		}
		$MenuList = mosHTML::selectList( $menu, 'menu', 'class="inputbox" size="10"', 'value', 'text', null );

		$items = &$this->get('ItemsFromRequest');
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong>
			<?php echo JText::_( 'Copy to Menu' ); ?>:
			</strong>
			<br />
			<?php echo $MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being copied' ); ?>:
			</strong>
			<br />
			<ol>
			<?php
			foreach ( $items as $item ) {
				?>
				<li>
				<?php echo $item->name; ?>
				</li>
				<?php
			}
			?>
			</ol>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<input type="hidden" name="task" value="" />
		<?php
		foreach ( $items as $item ) {
			echo "\n\t<input type=\"hidden\" name=\"cid[]\" value=\"$item->id\" />";
		}
		?>
		</form>
	<?php
	}

	function moveForm()
	{
		$document = &$this->getDocument();
		$document->setTitle('Copy Menu Items');

		$app		= &$this->get('Application');
		$menutype 	= $app->getUserStateFromRequest( "com_menus.menutype", 'menutype', 'mainmenu' );

		// Build the menutypes select list
		$menuTypes 	= $this->get('MenuTypes');
		foreach ( $menuTypes as $menuType ) {
			$menu[] = mosHTML::makeOption( $menuType, $menuType );
		}
		$MenuList = mosHTML::selectList( $menu, 'menu', 'class="inputbox" size="10"', 'value', 'text', null );

		$items = &$this->get('ItemsFromRequest');
	?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Move to Menu' ); ?>:</strong>
			<br />
			<?php echo $MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being moved' ); ?>:
			</strong>
			<br />
			<ol>
			<?php
			foreach ( $items as $item ) {
				?>
				<li>
				<?php echo $item->name; ?>
				</li>
				<?php
			}
			?>
			</ol>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="menutype" value="<?php echo $menutype; ?>" />
		<?php
		foreach ( $items as $item ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$item->id\" />";
		}
		?>
		</form>
	<?php
	}

	function &_getViewLists()
	{
		$app	=& $this->get('Application');
		$db		=& $this->get('DBO');

		$menutype 			= $app->getUserStateFromRequest( "com_menus.menutype",				 		'menutype', 		'mainmenu' );
		$filter_order		= $app->getUserStateFromRequest( "com_menus.$menutype.filter_order", 		'filter_order', 	'm.ordering' );
		$filter_order_Dir	= $app->getUserStateFromRequest( "com_menus.$menutype.filter_order_Dir",	'filter_order_Dir',	'ASC' );
		$filter_state		= $app->getUserStateFromRequest( "com_menus.$menutype.filter_state", 		'filter_state', 	'' );
		$levellimit 		= $app->getUserStateFromRequest( "com_menus.$menutype.levellimit", 			'levellimit', 		10 );
		$search 			= $app->getUserStateFromRequest( "com_menus.$menutype.search", 				'search', 			'' );
		$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

		// level limit filter
		$lists['levellist'] = mosHTML::integerSelectList( 1, 20, 1, 'levellimit', 'size="1" onchange="document.adminForm.submit();"', $levellimit );
	
		// state filter
		$lists['state']	= mosCommonHTML::selectState( $filter_state );
	
		// table ordering
		if ( $filter_order_Dir == 'DESC' ) {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
	
		// search filter
		$lists['search']= $search;

		return $lists;
	}
}
?>