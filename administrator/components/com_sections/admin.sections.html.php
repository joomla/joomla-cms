<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Sections
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

/**
* @package Joomla
* @subpackage Sections
*/
class sections_html 
{
	/**
	* Writes a list of the categories for a section
	* @param array An array of category objects
	* @param string The name of the category section
	*/
	function show( &$rows, $scope, $myid, &$page, $option, &$lists ) 
	{
		global $mainframe;

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
		$user =& $mainframe->getUser();
		
		//Ordering allowed ?
		$ordering = ($lists['order'] == 's.ordering');

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_sections&amp;scope=<?php echo $scope; ?>" method="post" name="adminForm">

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
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<thead>
			<tr>
				<th width="10">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="10">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML::tableOrdering( 'Section Name', 's.name', $lists ); ?>
				</th>
				<th width="10%">
					<?php mosCommonHTML::tableOrdering( 'Published', 's.published', $lists ); ?>
				</th>
				<th width="80" nowrap="nowrap">
					<a href="javascript:tableOrdering('s.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
						<?php echo JText::_( 'Order' ); ?>
					</a>	
				</th>
				<th width="1%">
					<?php mosCommonHTML::saveorderButton( $rows ); ?>
				</th>
				<th width="8%">
					<?php mosCommonHTML::tableOrdering( 'Access', 'groupname', $lists ); ?>
				</th>
				<th width="2%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'ID', 's.id', $lists ); ?>
				</th>
				<th width="9%" nowrap="nowrap">
					<?php echo JText::_( 'Num Categories' ); ?>
				</th>
				<th width="9%" nowrap="nowrap">
					<?php echo JText::_( 'Num Active' ); ?>
				</th>
				<th width="9%" nowrap="nowrap">
					<?php echo JText::_( 'Num Trash' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<td colspan="13">
				<?php echo $page->getListFooter(); ?>
			</td>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
			$row = &$rows[$i];

			$link 		= 'index2.php?option=com_sections&scope=content&task=editA&hidemainmenu=1&id='. $row->id;

			$access 	= mosCommonHTML::AccessProcessing( $row, $i );
			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center">
					<?php echo $page->rowNumber( $i ); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td onmouseover="return overlib('<?php echo $row->title; ?>', CAPTION, '<?php echo JText::_( 'Title' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
					<?php
					if ( $row->checked_out && ( $row->checked_out != $user->get('id') ) ) {
						echo $row->name;
					} else {
						?>
						<a href="<?php echo ampReplace( $link ); ?>">
							<?php echo $row->name; ?></a>
						<?php
					}
					?>
				</td>
				<td align="center">
					<?php echo $published;?>
				</td>
				<td class="order" colspan="2">
					<span><?php echo $page->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering ); ?></span>
					<span><?php echo $page->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
					<?php $disabled = $ordering ?  '' : '"disabled=disabled"'; ?>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
				</td>
				<td align="center">
					<?php echo $access;?>
				</td>
				<td align="center">
					<?php echo $row->id; ?>
				</td>
				<td align="center">
					<?php echo $row->categories; ?>
				</td>
				<td align="center">
					<?php echo $row->active; ?>
				</td>
				<td align="center">
					<?php echo $row->trash; ?>
				</td>
				<?php
				$k = 1 - $k;
				?>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>
	
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="scope" value="<?php echo $scope;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="chosen" value="" />
		<input type="hidden" name="act" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing categories
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.  Note that the <var>section</var> property <b>must</b> be defined
	* even for a new record.
	* @param JTableCategory The category object
	* @param string The html for the image list select list
	* @param string The html for the image position select list
	* @param string The html for the ordering list
	* @param string The html for the groups select list
	*/
	function edit( &$row, $option, &$lists, &$menus )
	{
		global $mainframe;

		jimport( 'joomla.presentation.editor' );
		$editor =& JEditor::getInstance();

		if ( $row->name != '' ) {
			$name = $row->name;
		} else {
			$name = JText::_( 'New Section' );
		}
		if ($row->image == '') {
			$row->image = 'blank.png';
		}

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'description' );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == '' ) {
					alert( "<?php echo JText::_( 'Please select a Menu', true ); ?>" );
					return;
				} else if ( form.link_type.value == "" ) {
					alert( "<?php echo JText::_( 'Please select a menu type', true ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo JText::_( 'Please enter a Name for this menu item', true ); ?>" );
					return;
				}
			}

			if ( form.name.value == '' ){
				alert("<?php echo JText::_( 'Section must have a name', true ); ?>");
			} else if ( form.title.value == '' ){
				alert("<?php echo JText::_( 'Section must have a title', true  ); ?>");
			} else {
				<?php
				echo $editor->save( 'description' ) ; ?>
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<div id="editcell">
			<table width="100%">
			<tr>
				<td valign="top" width="60%">
					<table class="adminform" border="1">
					<tr>
						<th colspan="3">
							<?php echo JText::_( 'Section Details' ); ?>
						</th>
					</tr>
					<tr>
						<td width="100">
							<?php echo JText::_( 'Scope' ); ?>:
						</td>
						<td colspan="2">
							<strong>
							<?php echo $row->scope; ?>
							</strong>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'Title' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<input class="text_area" type="text" name="title" id="title" value="<?php echo $row->title; ?>" size="50" maxlength="50" title="<?php echo JText::_( 'TIPTITLEFIELD' ); ?>" />
						</td>
					</tr>
					<tr>
						<td nowrap="nowrap">
							<label for="name">
								<?php echo JText::_( 'Section Name' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<input class="text_area" type="text" name="name" id="name" value="<?php echo $row->name; ?>" size="50" maxlength="255" title="<?php echo JText::_( 'TIPNAMEFIELD' ); ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Published' ); ?>:
						</td>
						<td colspan="2">
							<?php echo $lists['published']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="ordering">
								<?php echo JText::_( 'Ordering' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<?php echo $lists['ordering']; ?>
						</td>
					</tr>
					<tr>
						<td nowrap="nowrap" valign="top">
							<label for="access">
								<?php echo JText::_( 'Access Level' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['access']; ?>
						</td>
						<td rowspan="4" width="50%">
							<?php
								$path = $mainframe->getSiteURL() . "/images/";
								if ($row->image != "blank.png") {
									$path.= "stories/";
								}
							?>
							<img src="<?php echo $path;?><?php echo $row->image;?>" name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="image">
								<?php echo JText::_( 'Image' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['image']; ?>
						</td>
					</tr>
					<tr>
						<td nowrap="nowrap">
							<label for="image_position">
								<?php echo JText::_( 'Image Position' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['image_position']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" colspan="2">
							<label for="description">
								<?php echo JText::_( 'Description' ); ?>:
							</label>
						</td>
					</tr>
					<tr>
						<td valign="top" colspan="3">
							<?php
							// parameters : areaname, content, hidden field, width, height, rows, cols
							echo $editor->display( 'description',  $row->description, '100%;', '300', '60', '20' ) ;
							?>
						</td>
					</tr>
					</table>
				</td>
				<td valign="top">
					<?php
					if ( $row->id > 0 ) {
						?>
						<table class="adminform">
						<tr>
							<th colspan="2">
								<?php echo JText::_( 'Link to Menu' ); ?>
							</th>
						</tr>
						<tr>
							<td colspan="2">
								<?php echo JText::_( 'DESCNEWMENUITEM' ); ?>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td valign="top" width="100">
								<label for="menuselect">
									<?php echo JText::_( 'Select a Menu' ); ?>
								</label>
							</td>
							<td>
								<?php echo $lists['menuselect']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="100">
								<label for="link_type">
									<?php echo JText::_( 'Select Menu Type' ); ?>
								</label>
							</td>
							<td>
								<?php echo $lists['link_type']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="100">
								<label for="link_name">
									<?php echo JText::_( 'Menu Item Name' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="link_name" id="link_name" class="inputbox" value="" size="25" />
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<input name="menu_link" type="button" class="button" value="<?php echo JText::_( 'Link to Menu' ); ?>" onclick="submitbutton('menulink');" />
							</td>
						</tr>
						</table>

						<br />

						<?php
						if ( $menus != NULL ) {
							?>
							<table class="adminform">
							<?php mosCommonHTML::menuLinksSecCat( $menus ); ?>
							</table>
							<?php
						}
						?>
						<?php
					} else {
						?>
						<table class="adminform" width="40%">
						<tr>
							<td>
								<?php echo JText::_( 'Menu links available when saved' ); ?>
							</td>
						</tr>
						</table>
						<?php
					}
					?>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="scope" value="<?php echo $row->scope; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="oldtitle" value="<?php echo $row->title ; ?>" />
		</form>
		<?php
	}


	/**
	* Form to select Section to copy Category to
	*/
	function copySectionSelect( $option, $cid, $categories, $contents, $section ) {
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Copy to Section' ); ?>:</strong>
			<br />
			<input class="text_area" type="text" name="title" value="" size="35" maxlength="50" title="<?php echo JText::_( 'The new Section name' ); ?>" />
			<br /><br />
			</td>
			<td  valign="top" width="20%">
			<strong><?php echo JText::_( 'Categories being copied' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $categories as $category ) {
				echo "<li>". $category->name ."</li>";
				echo "\n <input type=\"hidden\" name=\"category[]\" value=\"$category->id\" />";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top" width="20%">
			<strong><?php echo JText::_( 'Content Items being copied' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $contents as $content ) {
				echo "<li>". $content->title ."</li>";
				echo "\n <input type=\"hidden\" name=\"content[]\" value=\"$content->id\" />";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top">
			<?php echo JText::_( 'This will copy the Categories listed' ); ?>
			<br />
			<?php echo JText::_( 'DESCALLITEMSWITHINCAT' ); ?>
			<br />
			<?php echo JText::_( 'to the new Section created.' ); ?>
			</td>.
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="section" value="<?php echo $section;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="scope" value="content" />
		<?php
		foreach ( $cid as $id ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}

}
?>
