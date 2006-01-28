<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Categories
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
* @subpackage Categories
*/
class categories_html {

	/**
	* Writes a list of the categories for a section
	* @param array An array of category objects
	* @param string The name of the category section
	*/
	function show( &$rows, $section, $section_name, &$pageNav, &$lists, $type ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_categories&amp;section=<?php echo $section; ?>" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<td>
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
			<td align="right">
				<?php
				if ( $section == 'content') {
					echo $lists['sectionid'];
				}
				?>
				<?php echo $lists['state']; ?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="10" align="left">
            	<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
			</th>
			<th class="title">
				<?php mosCommonHTML :: tableOrdering( 'Category Name', 'c.name', $lists ); ?>
			</th>
			<th width="10%">
				<?php mosCommonHTML :: tableOrdering( 'Published', 'c.published', $lists ); ?>
			</th>
			<?php
			if ( $section <> 'content') {
				?>
				<th colspan="2" width="5%">
					<?php echo JText::_( 'Reorder' ); ?>
				</th>
				<?php
			}
			?>
			<th width="2%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'Order', 'c.ordering', $lists ); ?>
			</th>
			<th width="1%">
				<a href="javascript: saveorder( <?php echo count( $rows )-1; ?> )">
					<img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo JText::_( 'Save Order' ); ?>" /></a>
			</th>
			<th width="7%">
				<?php mosCommonHTML :: tableOrdering( 'Access', 'groupname', $lists ); ?>
			</th>
			<th width="2%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'ID', 'c.id', $lists ); ?>
			</th>
			<?php
			if ( $section == 'content') {
				?>
				<th width="12%"  class="title">
					<?php mosCommonHTML :: tableOrdering( 'Section', 'section_name', $lists ); ?>
				</th>
				<?php
			}
			?>
			<?php
			if ( $type == 'content') {
				?>
				<th width="5%">
					<?php echo JText::_( 'Num Active' ); ?>
				</th>
				<th width="5%">
					<?php echo JText::_( 'Num Trash' ); ?>
				</th>
				<?php
			}
			?>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row 	= &$rows[$i];

			$row->sect_link = ampReplace( 'index2.php?option=com_sections&task=editA&hidemainmenu=1&id='. $row->section );

			$link = 'index2.php?option=com_categories&section='. $section .'&task=editA&hidemainmenu=1&id='. $row->id;

			$access 	= mosCommonHTML::AccessProcessing( $row, $i );
			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php
					if ( $row->checked_out_contact_category && ( $row->checked_out_contact_category != $my->id ) ) {
						echo $row->name .' ( '. $row->title .' )';
					} else {
						?>
						<a href="<?php echo ampReplace( $link ); ?>">
							<?php echo $row->name .' ( '. $row->title .' )'; ?></a>
						<?php
					}
					?>
				</td>
				<td align="center">
					<?php echo $published;?>
				</td>
				<?php
				if ( $section <> 'content' ) {
					?>
					<td>
						<?php echo $pageNav->orderUpIcon( $i ); ?>
					</td>
					<td>
						<?php echo $pageNav->orderDownIcon( $i, $n ); ?>
					</td>
					<?php
				}
				?>
				<td align="center" colspan="2">
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td align="center">
					<?php echo $access;?>
				</td>
				<td align="center">
					<?php echo $row->id; ?>
				</td>
				<?php
				if ( $section == 'content' ) {
					?>
					<td>
						<a href="<?php echo $row->sect_link; ?>" title="<?php echo JText::_( 'Edit Section' ); ?>">
							<?php echo $row->section_name; ?></a>
					</td>
					<?php
				}
				?>
				<?php
				if ( $type == 'content') {
					?>
					<td align="center">
						<?php echo $row->active; ?>
					</td>
					<td align="center">
						<?php echo $row->trash; ?>
					</td>
					<?php
				}
				$k = 1 - $k;
				?>
			</tr>
			<?php
		}
		?>
		</table>

		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="com_categories" />
		<input type="hidden" name="section" value="<?php echo $section;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="chosen" value="" />
		<input type="hidden" name="act" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing categories
	* @param JModelCategory The category object
	* @param string
	* @param array
	*/
	function edit( &$row, &$lists, $redirect, $menus ) {
		if ($row->image == '') {
			$row->image = 'blank.png';
		}

		if ( $redirect == 'content' ) {
			$component = 'Content';
		} else {
			$component = ucfirst( substr( $redirect, 4 ) );
			if ( $redirect == 'com_contact_details' ) {
				$component = 'Contact';
			}
		}
		mosMakeHtmlSafe( $row, ENT_QUOTES, 'description' );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton, section) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == "" ) {
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

			if ( form.name.value == "" ) {
				alert("<?php echo JText::_( 'Category must have a name', true ); ?>");
			} else {
				<?php 
				$editor =& JEditor::getInstance();
				echo $editor->getEditorContents( 'editor1', 'description' ) ; ?>
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr>
			<td valign="top" width="60%">
				<table class="adminform">
				<tr>
					<th colspan="3">
					<?php echo JText::_( 'Category Details' ); ?>
					</th>
				<tr>
				<tr>
					<td>
					<?php echo JText::_( 'Category Title' ); ?>:
					</td>
					<td colspan="2">
					<input class="text_area" type="text" name="title" value="<?php echo $row->title; ?>" size="50" maxlength="50" title="<?php echo JText::_( 'A short name to appear in menus' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Category Name' ); ?>:
					</td>
					<td colspan="2">
					<input class="text_area" type="text" name="name" value="<?php echo $row->name; ?>" size="50" maxlength="255" title="<?php echo JText::_( 'A long name to be displayed in headings' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Section' ); ?>:
					</td>
					<td colspan="2">
					<?php echo $lists['section']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Image' ); ?>:
					</td>
					<td>
					<?php echo $lists['image']; ?>
					</td>
					<td rowspan="4" width="50%">
					<script language="javascript" type="text/javascript">
					if (document.forms[0].image.options.value!=''){
					  jsimg='../images/stories/' + getSelectedValue( 'adminForm', 'image' );
					} else {
					  jsimg='../images/M_images/blank.png';
					}
					document.write('<img src=' + jsimg + ' name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />');
					</script>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Image Position' ); ?>:
					</td>
					<td>
					<?php echo $lists['image_position']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Ordering' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Access Level' ); ?>:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo JText::_( 'Description' ); ?>:
					</td>
					<td colspan="2">
					<?php
					// parameters : areaname, content, hidden field, width, height, rows, cols
					$editor =& JEditor::getInstance();
					echo $editor->getEditor( 'editor1',  $row->description , 'description', '100%;', '300', '60', '20' ) ; ?>
					</td>
				</tr>
				</table>
			</td>
			<td valign="top" width="40%">
			<?php
			if ( $row->id > 0 ) {
			?>
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Link to Menu' ); ?>
					</th>
				<tr>
				<tr>
					<td colspan="2">
					<?php echo JText::_( 'Will Create New Menu Item in Menu Selected' ); ?>
					<br /><br />
					</td>
				<tr>
				<tr>
					<td valign="top" width="100px">
					<?php echo JText::_( 'Select a Menu' ); ?>
					</td>
					<td>
					<?php echo $lists['menuselect']; ?>
					</td>
				<tr>
				<tr>
					<td valign="top" width="100px">
					<?php echo JText::_( 'Select Menu Type' ); ?>
					</td>
					<td>
					<?php echo $lists['link_type']; ?>
					</td>
				<tr>
				<tr>
					<td valign="top" width="100px">
					<?php echo JText::_( 'Menu Item Name' ); ?>
					</td>
					<td>
					<input type="text" name="link_name" class="inputbox" value="" size="25" />
					</td>
				<tr>
				<tr>
					<td>
					</td>
					<td>
					<input name="menu_link" type="button" class="button" value="<?php echo JText::_( 'Link to Menu' ); ?>" onclick="submitbutton('menulink');" />
					</td>
				<tr>
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Existing Menu Links' ); ?>
					</th>
				</tr>
				<?php
				if ( $menus == NULL ) {
					?>
					<tr>
						<td colspan="2">
						<?php echo JText::_( 'None' ); ?>
						</td>
					</tr>
					<?php
				} else {
					mosCommonHTML::menuLinksSecCat( $menus );
				}
				?>
				<tr>
					<td colspan="2">
					</td>
				</tr>
				</table>
			<?php
			} else {
			?>
			<table class="adminform" width="40%">
				<tr><th>&nbsp;</th></tr>
				<tr><td><?php echo JText::_( 'Menu links available when saved' ); ?></td></tr>
			</table>
			<?php
			}
			?>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_categories" />
		<input type="hidden" name="oldtitle" value="<?php echo $row->title ; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $row->section; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}


	/**
	* Form to select Section to move Category to
	*/
	function moveCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect ) {
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Move to Section' ); ?>:</strong>
			<br />
			<?php echo $SectionList ?>
			<br /><br />
			</td>
			<td  valign="top" width="20%">
			<strong><?php echo JText::_( 'Categories being moved' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->name ."</li>";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top" width="20%">
			<strong><?php echo JText::_( 'Content Items being moved' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $contents as $content ) {
				echo "<li>". $content->title ."</li>";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top">
			<?php echo JText::_( 'This will move the Categories listed' ); ?>
			<br />
			<?php echo JText::_( 'and all the items within the category (also listed)' ); ?>
			<br />
			<?php echo JText::_( 'to the selected Section' ); ?>.
			</td>.
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="section" value="<?php echo $sectionOld;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		<input type="hidden" name="task" value="" />
		<?php
		foreach ( $cid as $id ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}


	/**
	* Form to select Section to copy Category to
	*/
	function copyCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect ) {
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Copy to Section' ); ?>:</strong>
			<br />
			<?php echo $SectionList ?>
			<br /><br />
			</td>
			<td  valign="top" width="20%">
			<strong><?php echo JText::_( 'Categories being copied' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->name ."</li>";
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
				echo "\n <input type=\"hidden\" name=\"item[]\" value=\"$content->id\" />";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top">
			<?php echo JText::_( 'This will copy the Categories listed' ); ?>
			<br />
			<?php echo JText::_( 'and all the items within the category (also listed)' ); ?>
			<br />
			<?php echo JText::_( 'to the selected Section' ); ?>.
			</td>.
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="section" value="<?php echo $sectionOld;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		<input type="hidden" name="task" value="" />
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
