<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Content
*/
class HTML_content {

	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showContent( &$rows, $section, &$lists, $search, $pageNav, $all=NULL, $redirect ) {
		global $my, $acl, $database;
		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<th class="edit" rowspan="2" nowrap>
			<?php
            echo $_LANG->_( 'Content Items Manager' );
			if ( $all ) {
				?>
				 <small><small>[ <?php echo $_LANG->_( 'Section: All' ); ?> ]</small></small>
				<?php
			} else {
				?>
				 <small><small>[ <?php echo $_LANG->_( 'Section' ); ?>: <?php echo $section->title; ?> ]</small></small>
				<?php
			}
			?>
			</th>
			<?php
			if ( $all ) {
				?>
				<td width="right" rowspan="2" valign="top">
				<?php echo $lists['sectionid'];?>
				</td>
				<?php
			}
			?>
			<td width="right" valign="top">
			<?php echo $lists['catid'];?>
			</td>
			<td width="right" valign="top">
			<?php echo $lists['authorid'];?>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo $_LANG->_( 'Filter' ); ?>:
			</td>
			<td>
			<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="5">
			<?php echo $_LANG->_( 'Num' ); ?>
			</th>
			<th width="5">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th class="title">
			<?php echo $_LANG->_( 'Title' ); ?>
			</th>
			<th width="5%">
			<?php echo $_LANG->_( 'Published' ); ?>
			</th>
			<th nowrap="nowrap" width="5%">
			<?php echo $_LANG->_( 'Front Page' ); ?>
			</th>
			<th colspan="2" align="center" width="5%">
			<?php echo $_LANG->_( 'Reorder' ); ?>
			</th>
			<th width="2%">
			<?php echo $_LANG->_( 'Order' ); ?>
			</th>
			<th width="1%">
			<a href="javascript: saveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo $_LANG->_( 'Save Order' ); ?>" /></a>
			</th>
			<th >
			<?php echo $_LANG->_( 'Access' ); ?>
			</th>
			<th width="2%">
			<?php echo $_LANG->_( 'ID' ); ?>
			</th>
			<?php
			if ( $all ) {
				?>
				<th align="left">
				<?php echo $_LANG->_( 'Section' ); ?>
				</th>
				<?php
			}
			?>
			<th align="left">
			<?php echo $_LANG->_( 'Category' ); ?>
			</th>
			<th align="left">
			<?php echo $_LANG->_( 'Author' ); ?>
			</th>
			<th align="center" width="10">
			<?php echo $_LANG->_( 'Date' ); ?>
			</th>
		  </tr>
		<?php
		$k = 0;
		$nullDate = $database->getNullDate();
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$link 	= 'index2.php?option=com_content&sectionid='. $redirect .'&task=edit&hidemainmenu=1&id='. $row->id;

			$row->sect_link = 'index2.php?option=com_sections&task=editA&hidemainmenu=1&id='. $row->sectionid;
			$row->cat_link 	= 'index2.php?option=com_categories&task=editA&hidemainmenu=1&id='. $row->catid;

			$now = date( 'Y-m-d H:i:s' );
			if ( $now <= $row->publish_up && $row->state == "1" ) {
				$img = 'publish_y.png';
				$alt = $_LANG->_( 'Published' );
			} else if ( ( $now <= $row->publish_down || $row->publish_down == $nullDate ) && $row->state == "1" ) {
				$img = 'publish_g.png';
				$alt = $_LANG->_( 'Published' );
			} else if ( $now > $row->publish_down && $row->state == "1" ) {
				$img = 'publish_r.png';
				$alt = $_LANG->_( 'Expired' );
			} elseif ( $row->state == "0" ) {
				$img = "publish_x.png";
				$alt = $_LANG->_( 'Unpublished' );
			}
			$times = '';
			if (isset($row->publish_up)) {
				if ($row->publish_up == $nullDate) {
					$times .= "<tr><td>". $_LANG->_( 'Start: Always' ) ."</td></tr>";
				} else {
					$times .= "<tr><td>". $_LANG->_( 'Start' ) .": ". $row->publish_up ."</td></tr>";
				}
			}
			if (isset($row->publish_down)) {
				if ($row->publish_down == $nullDate) {
					$times .= "<tr><td>". $_LANG->_( 'Finish: No Expiry' ) ."</td></tr>";
				} else {
					$times .= "<tr><td>". $_LANG->_( 'Finish' ) .": ". $row->publish_down ."</td></tr>";
				}
			}

			if ( $acl->acl_check( 'administration', 'manage', 'users', $my->usertype, 'components', 'com_users' ) ) {
				if ( $row->created_by_alias ) {
					$author = $row->created_by_alias;
				} else {
					$linkA 	= 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->created_by;
					$author = '<a href="'. $linkA .'" title="'. $_LANG->_( 'Edit User' ) .'">'. $row->author .'</a>';
				}
			} else {
				if ( $row->created_by_alias ) {
					$author = $row->created_by_alias;
				} else {
					$author = $row->author;
				}
			}

			$date = mosFormatDate( $row->created, '%x' );

			$access 	= mosCommonHTML::AccessProcessing( $row, $i );
			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
				<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td align="center">
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->title;
				}
                else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Content' ); ?>">
					<?php echo htmlspecialchars($row->title, ENT_QUOTES); ?>
					</a>
					<?php
				}
				?>
				</td>
				<?php
				if ( $times ) {
					?>
					<td align="center">
					<a href="javascript: void(0);" onMouseOver="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Publish Information' ); ?>', BELOW, RIGHT);" onMouseOut="return nd();" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? $_LANG->_( 'unpublish' ) : $_LANG->_( 'publish' );?>')">
					<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
					</a>
					</td>
					<?php
				}
				?>
				<td align="center">
				<a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','toggle_frontpage')">
				<img src="images/<?php echo ( $row->frontpage ) ? 'tick.png' : 'publish_x.png';?>" width="12" height="12" border="0" alt="<?php echo ( $row->frontpage ) ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' );?>" />
				</a>
				</td>
				<td align="right">
				<?php echo $pageNav->orderUpIcon( $i, ($row->catid == @$rows[$i-1]->catid) ); ?>
				</td>
				<td align="left">
				<?php echo $pageNav->orderDownIcon( $i, $n, ($row->catid == @$rows[$i+1]->catid) ); ?>
				</td>
				<td align="center" colspan="2">
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td align="center">
				<?php echo $access;?>
				</td>
				<td align="left">
				<?php echo $row->id; ?>
				</td>
				<?php
				if ( $all ) {
					?>
					<td align="left">
					<a href="<?php echo $row->sect_link; ?>" title="<?php echo $_LANG->_( 'Edit Section' ); ?>">
					<?php echo $row->section_name; ?>
					</a>
					</td>
					<?php
				}
				?>
				<td align="left">
				<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>">
				<?php echo $row->name; ?>
				</a>
				</td>
				<td align="left">
				<?php echo $author; ?>
				</td>
				<td align="left">
				<?php echo $date; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>

		<?php echo $pageNav->getListFooter(); ?>
		<?php mosCommonHTML::ContentLegend(); ?>

		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="sectionid" value="<?php echo $section->id;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		</form>
		<?php
	}


	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showArchive( &$rows, $section, &$lists, $search, $pageNav, $option, $all=NULL, $redirect ) {
		global $my, $acl;
		global $_LANG;

		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'remove') {
				if (document.adminForm.boxchecked.value == 0) {
					alert("<?php echo $_LANG->_( 'VALIDSELECTIONLISTSENDTRASH' ); ?>");
				} else if ( confirm("<?php echo $_LANG->_( 'VALIDTRASHSELECTEDITEMS' ); ?>")) {
					submitform('remove');
				}
			} else {
				submitform(pressbutton);
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<th class="edit" rowspan="2">
            <?php
            echo $_LANG->_( 'Archive Manager' );
			if ( $all ) {
				?>
				 <small><small>[ <?php echo $_LANG->_( 'Section: All' ); ?> ]</small></small>
				<?php
			} else {
				?>
				 <small><small>[ <?php echo $_LANG->_( 'Section' ); ?>: <?php echo $section->title; ?> ]</small></small>
				<?php
			}
			?>
			</th>
			<?php
			if ( $all ) {
				?>
				<td width="right" rowspan="2" valign="top">
				<?php echo $lists['sectionid'];?>
				</td>
				<?php
			}
			?>
			<td width="right">
			<?php echo $lists['catid'];?>
			</td>
			<td width="right">
			<?php echo $lists['authorid'];?>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo $_LANG->_( 'Filter' ); ?>:
			</td>
			<td>
			<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="5">
			<?php echo $_LANG->_( 'Num' ); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th class="title">
			<?php echo $_LANG->_( 'Title' ); ?>
			</th>
			<th width="2%">
			<?php echo $_LANG->_( 'Order' ); ?>
			</th>
			<th width="1%">
			<a href="javascript: saveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo $_LANG->_( 'Save Order' ); ?>" /></a>
			</th>
			<th width="15%" align="left">
			<?php echo $_LANG->_( 'Category' ); ?>
			</th>
			<th width="15%" align="left">
			<?php echo $_LANG->_( 'Author' ); ?>
			</th>
			<th align="center" width="10">
			<?php echo $_LANG->_( 'Date' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$row->cat_link 	= 'index2.php?option=com_categories&task=editA&hidemainmenu=1&id='. $row->catid;

			if ( $acl->acl_check( 'administration', 'manage', 'users', $my->usertype, 'components', 'com_users' ) ) {
				if ( $row->created_by_alias ) {
					$author = $row->created_by_alias;
				} else {
					$linkA 	= 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->created_by;
					$author = '<a href="'. $linkA .'" title="'. $_LANG->_( 'Edit User' ) .'">'. $row->author .'</a>';
				}
			} else {
				if ( $row->created_by_alias ) {
					$author = $row->created_by_alias;
				} else {
					$author = $row->author;
				}
			}

			$date = mosFormatDate( $row->created, '%x' );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
				<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td width="20">
				<?php echo mosHTML::idBox( $i, $row->id ); ?>
				</td>
				<td>
				<?php echo $row->title; ?>
				</td>
				<td align="center" colspan="2">
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td>
				<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>">
				<?php echo $row->name; ?>
				</a>
				</td>
				<td>
				<?php echo $author; ?>
				</td>
				<td>
				<?php echo $date; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>

		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $section->id;?>" />
		<input type="hidden" name="task" value="showarchive" />
		<input type="hidden" name="returntask" value="showarchive" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		</form>
		<?php
	}


	/**
	* Writes the edit form for new and existing content item
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosContent The category object
	* @param string The html for the groups select list
	*/
	function editContent( &$row, $section, &$lists, &$sectioncategories, &$images, &$params, $option, $redirect, &$menus ) {
		global $mosConfig_live_site;
		global $_LANG;

		mosMakeHtmlSafe( $row );

		$create_date = null;
		if (intval( $row->created ) <> 0) {
			$create_date 	= mosFormatDate( $row->created, '%A, %d %B %Y %H:%M', '0' );
		}
		$mod_date = null;
		if (intval( $row->modified ) <> 0) {
			$mod_date 		= mosFormatDate( $row->modified, '%A, %d %B %Y %H:%M', '0' );
		}

		$tabs = new mosTabs(1);


		// used to hide "Reset Hits" when hits = 0
		if ( !$row->hits ) {
			$visibility = "style='display: none; visbility: hidden;'";
		} else {
			$visibility = "";
		}

		mosCommonHTML::loadOverlib();
		mosCommonHTML::loadCalendar();
		?>
		<script language="javascript" type="text/javascript">
		<!--
		var sectioncategories = new Array;
		<?php
		$i = 0;
		foreach ($sectioncategories as $k=>$items) {
			foreach ($items as $v) {
				echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->name )."' );\n\t\t";
			}
		}
		?>

		var folderimages = new Array;
		<?php
		$i = 0;
		foreach ($images as $k=>$items) {
			foreach ($items as $v) {
				echo "folderimages[".$i++."] = new Array( '$k','".addslashes( $v->value )."','".addslashes( $v->text )."' );\n\t\t";
			}
		}
		?>

		function submitbutton(pressbutton) {
			var form = document.adminForm;

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please select a Menu' ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please enter a Name for this menu item' ); ?>" );
					return;
				}
			}

			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// assemble the images back into one field
			var temp = new Array;
			for (var i=0, n=form.imagelist.options.length; i < n; i++) {
				temp[i] = form.imagelist.options[i].value;
			}
			form.images.value = temp.join( '\n' );

			// do field validation
			if (form.title.value == ""){
				alert( "<?php echo $_LANG->_( 'Content item must have a title' ); ?>" );
			} else if (form.sectionid.value == "-1"){
				alert( "<?php echo $_LANG->_( 'You must select a Section.' ); ?>" );
			} else if (form.catid.value == "-1"){
				alert( "<?php echo $_LANG->_( 'You must select a Category.' ); ?>" );
 			} else if (form.catid.value == ""){
 				alert( "<?php echo $_LANG->_( 'You must select a Category.' ); ?>" );
			} else {
				<?php getEditorContents( 'editor1', 'introtext' ) ; ?>
				<?php getEditorContents( 'editor2', 'fulltext' ) ; ?>
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th class="edit">
			<?php echo $_LANG->_( 'Content Item' ); ?>:
			<small>
			<?php echo $row->id ? $_LANG->_( 'Edit' ) : $_LANG->_( 'New' );?>
			</small>
			<?php
			if ( $row->id ) {
				?>
				<small><small>
				[ <?php echo $_LANG->_( 'Section' ); ?>: <?php echo $section?> ]
				</small></small>
				<?php
			}
			?>
			</th>
		</tr>
		</table>

		<table cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td width="60%" valign="top">
				<table width="100%" class="adminform">
				<tr>
					<td width="100%">
						<table cellspacing="0" cellpadding="0" border="0" width="100%">
						<tr>
							<th colspan="4">
							<?php echo $_LANG->_( 'Item Details' ); ?>
							</th>
						<tr>
						<tr>
							<td>
							<?php echo $_LANG->_( 'Title' ); ?>:
							</td>
							<td>
							<input class="text_area" type="text" name="title" size="30" maxlength="100" value="<?php echo $row->title; ?>" />
							</td>
							<td>
							<?php echo $_LANG->_( 'Section' ); ?>:
							</td>
							<td>
							<?php echo $lists['sectionid']; ?>
							</td>
						</tr>
						<tr>
							<td>
							<?php echo $_LANG->_( 'Title Alias' ); ?>:
							</td>
							<td>
							<input name="title_alias" type="text" class="text_area" id="title_alias" value="<?php echo $row->title_alias; ?>" size="30" maxlength="100" />
							</td>
							<td>
							<?php echo $_LANG->_( 'Category' ); ?>:
							</td>
							<td>
							<?php echo $lists['catid']; ?>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="100%">
					<?php echo $_LANG->_( 'Intro Text: (required)' ); ?>
					<br /><?php
					// parameters : areaname, content, hidden field, width, height, rows, cols
					editorArea( 'editor1',  $row->introtext , 'introtext', '100%;', '350', '75', '20' ) ; ?>
					</td>
				</tr>
				<tr>
					<td width="100%">
					<?php echo $_LANG->_( 'Main Text: (optional)' ); ?>
					<br /><?php
					// parameters : areaname, content, hidden field, width, height, rows, cols
					editorArea( 'editor2',  $row->fulltext , 'fulltext', '100%;', '400', '75', '30' ) ; ?>
					</td>
				</tr>
				</table>
			</td>
			<td valign="top" width="40%">
				<table>
				<tr>
					<td>
					<?php
					$title = $_LANG->_( 'Publishing' );
					$tabs->startPane("content-pane");
					$tabs->startTab( $title, "publish-page" );
					?>
					<table class="adminform">
					<tr>
						<th colspan="2">
						<?php echo $_LANG->_( 'Publishing Info' ); ?>
						</th>
					<tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Show on Frontpage' ); ?>:
						</td>
						<td>
						<input type="checkbox" name="frontpage" value="1" <?php echo $row->frontpage ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Published' ); ?>:
						</td>
						<td>
						<input type="checkbox" name="published" value="1" <?php echo $row->state ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Access Level' ); ?>:
						</td>
						<td>
						<?php echo $lists['access']; ?> </td>
						</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Author Alias' ); ?>:
						</td>
						<td>
						<input type="text" name="created_by_alias" size="30" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="text_area" />
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Change Creator' ); ?>:
						</td>
						<td>
						<?php echo $lists['created_by']; ?> </td>
					</tr>
					<tr>
						<td valign="top" align="right"><?php echo $_LANG->_( 'Ordering' ); ?>:</td>
						<td>
						<?php echo $lists['ordering']; ?> </td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Override Created Date' ); ?>
						</td>
						<td>
						<input class="text_area" type="text" name="created" id="created" size="25" maxlength="19" value="<?php echo $row->created; ?>" />
						<input name="reset" type="reset" class="button" onClick="return showCalendar('created', 'y-mm-dd');" value="...">
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Start Publishing' ); ?>:
						</td>
						<td>
						<input class="text_area" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
						<input type="reset" class="button" value="..." onClick="return showCalendar('publish_up', 'y-mm-dd');">
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<?php echo $_LANG->_( 'Finish Publishing' ); ?>:
						</td>
						<td>
						<input class="text_area" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
						<input type="reset" class="button" value="..." onClick="return showCalendar('publish_down', 'y-mm-dd');">
						</td>
					</tr>
					</table>
					<br />
					<table class="adminform">
					<?php
					if ( $row->id ) {
						?>
						<tr>
							<td>
							<strong><?php echo $_LANG->_( 'Content ID' ); ?>:</strong>
							</td>
							<td>
							<?php echo $row->id; ?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td width="90px" valign="top" align="right">
						<strong><?php echo $_LANG->_( 'State' ); ?>:</strong>
						</td>
						<td>
						<?php echo $row->state > 0 ? $_LANG->_( 'Published' ) : ($row->state < 0 ? $_LANG->_( 'Archived' ) : $_LANG->_( 'Draft Unpublished' ) );?>
						</td>
					</tr>
					<tr >
						<td valign="top" align="right">
						<strong>
						<?php echo $_LANG->_( 'Hits' ); ?>
						</strong>:
						</td>
						<td>
						<?php echo $row->hits;?>
						<div <?php echo $visibility; ?>>
						<input name="reset_hits" type="button" class="button" value="<?php echo $_LANG->_( 'Reset Hit Count' ); ?>" onClick="submitbutton('resethits');">
						</div>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<strong>
						<?php echo $_LANG->_( 'Revised' ); ?>
						</strong>:
						</td>
						<td>
						<?php echo $row->version;?> <?php echo $_LANG->_( 'times' ); ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<strong>
						<?php echo $_LANG->_( 'Created' ); ?>
						</strong>
						</td>
						<td>
						<?php echo $row->created ? "$create_date</td></tr><tr><td valign='top' align='right'><strong>". $_LANG->_( 'By' ) ."</strong></td><td>". $row->creator : $_LANG->_( 'New document' ); ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
						<strong>
						<?php echo $_LANG->_( 'Last Modified' ); ?>
						</strong>
						</td>
						<td>
						<?php echo $row->modified ? "$mod_date</td></tr><tr><td valign='top' align='right'><strong>". $_LANG->_( 'By' ) ."</strong></td><td>". $row->modifier : $_LANG->_( 'Not modified' );?>
						</td>
					</tr>
					</table>
					<?php
					$title = $_LANG->_( 'Images' );
					$tabs->endTab();
					$tabs->startTab( $title, "images-page" );
					?>
					<table class="adminform" width="100%">
					<tr>
						<th colspan="2">
							<?php echo $_LANG->_( 'MOSImage Control' ); ?>
						</th>
					</tr>
					<tr>
						<td colspan="2">
							<table width="100%">
							<tr>
								<td width="48%">
									<div align="center">
										<?php echo $_LANG->_( 'Gallery Images' ); ?>:
										<br />
										<?php echo $lists['imagefiles'];?>
										<br />
										<?php echo $_LANG->_( 'Sub-folder' ); ?>: <?php echo $lists['folders'];?>
									</div>
								</td>
								<td width="2%">
									<input class="button" type="button" value=">>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" title="<?php echo $_LANG->_( 'Add' ); ?>"/>
									<br />
									<input class="button" type="button" value="<<" onclick="delSelectedFromList('adminForm','imagelist')" title="<?php echo $_LANG->_( 'Remove' ); ?>"/>
								</td>
								<td width="48%">
									<div align="center">
										<?php echo $_LANG->_( 'Content Images' ); ?>:
										<br />
										<?php echo $lists['imagelist'];?>
										<br />
										<input class="button" type="button" value="<?php echo $_LANG->_( 'Up' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,-1)" />
										<input class="button" type="button" value="<?php echo $_LANG->_( 'Down' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,+1)" />
									</div>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<div align="center">
								<?php echo $_LANG->_( 'Sample Image' ); ?>:<br />
								<img name="view_imagefiles" src="../images/M_images/blank.png" width="100" />
							</div>
						</td>
						<td valign="top">
							<div align="center">
								<?php echo $_LANG->_( 'Active Image' ); ?>:<br />
								<img name="view_imagelist" src="../images/M_images/blank.png" width="100" />
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php echo $_LANG->_( 'Edit the image selected' ); ?>:
							<table>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Source' ); ?>:
								</td>
								<td>
								<input class="text_area" type="text" name= "_source" value="" />
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Image Align' ); ?>:
								</td>
								<td>
								<?php echo $lists['_align']; ?>
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Alt Text' ); ?>:
								</td>
								<td>
								<input class="text_area" type="text" name="_alt" value="" />
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Border' ); ?>:
								</td>
								<td>
								<input class="text_area" type="text" name="_border" value="" size="3" maxlength="1" />
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Caption' ); ?>:
								</td>
								<td>
								<input class="text_area" type="text" name="_caption" value="" size="30" />
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Caption Position' ); ?>:
								</td>
								<td>
								<?php echo $lists['_caption_position']; ?>
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Caption Align' ); ?>:
								</td>
								<td>
								<?php echo $lists['_caption_align']; ?>
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo $_LANG->_( 'Caption Width' ); ?>:
								</td>
								<td>
								<input class="text_area" type="text" name="_width" value="" size="5" maxlength="5" />
								</td>
							</tr>
							<tr>
								<td colspan="2">
								<input class="button" type="button" value="<?php echo $_LANG->_( 'Apply' ); ?>" onClick="applyImageProps()" />
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</table>
					<?php
					$title = $_LANG->_( 'Parameters' );
					$tabs->endTab();
					$tabs->startTab( $title, "params-page" );
					?>
					<table class="adminform">
					<tr>
						<th colspan="2">
						<?php echo $_LANG->_( 'Parameter Control' ); ?>
						</th>
					<tr>
					<tr>
						<td>
						<?php echo $_LANG->_( 'DESCPARAMCONTROLWHATSEE' ); ?>
						<br /><br />
						</td>
					</tr>
					<tr>
						<td>
						<?php echo $params->render();?>
						</td>
					</tr>
					</table>
					<?php
					$title = $_LANG->_( 'Meta Info' );
					$tabs->endTab();
					$tabs->startTab( $title, "metadata-page" );
					?>
					<table class="adminform">
					<tr>
						<th colspan="2">
						<?php echo $_LANG->_( 'Meta Data' ); ?>
						</th>
					<tr>
					<tr>
						<td>
						<?php echo $_LANG->_( 'Description' ); ?>:
						<br />
						<textarea class="text_area" cols="30" rows="3" style="width:300px; height:50px" name="metadesc" width="500"><?php echo str_replace('&','&amp;',$row->metadesc); ?></textarea>
						</td>
					</tr>
						<tr>
						<td>
						<?php echo $_LANG->_( 'Keywords' ); ?>:
						<br />
						<textarea class="text_area" cols="30" rows="3" style="width:300px; height:50px" name="metakey" width="500"><?php echo str_replace('&','&amp;',$row->metakey); ?></textarea>
						</td>
					</tr>
					<tr>
						<td>
						<input type="button" class="button" value="<?php echo $_LANG->_( 'Add Sect/Cat/Title' ); ?>" onClick="f=document.adminForm;f.metakey.value=document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].text+', '+getSelectedText('adminForm','catid')+', '+f.title.value+f.metakey.value;" />
						</td>
					</tr>
					</table>
					<?php
					$title = $_LANG->_( 'Link to Menu' );
					$tabs->endTab();
					$tabs->startTab( $title, "link-page" );
					?>
					<table class="adminform">
					<tr>
						<th colspan="2">
						<?php echo $_LANG->_( 'Link to Menu' ); ?>
						</th>
					<tr>
					<tr>
						<td colspan="2">
						<?php echo $_LANG->_( 'DESCWILLCREATELINKINMENU' ); ?>
						<br /><br />
						</td>
					<tr>
					<tr>
						<td valign="top" width="90px">
						<?php echo $_LANG->_( 'Select a Menu' ); ?>
						</td>
						<td>
						<?php echo $lists['menuselect']; ?>
						</td>
					<tr>
					<tr>
						<td valign="top" width="90px">
						<?php echo $_LANG->_( 'Menu Item Name' ); ?>
						</td>
						<td>
						<input type="text" name="link_name" class="inputbox" value="" size="30" />
						</td>
					<tr>
					<tr>
						<td>
						</td>
						<td>
						<input name="menu_link" type="button" class="button" value="<?php echo $_LANG->_( 'Link to Menu' ); ?>" onClick="submitbutton('menulink');" />
						</td>
					<tr>
					<tr>
						<th colspan="2">
						<?php echo $_LANG->_( 'Existing Menu Links' ); ?>
						</th>
					</tr>
					<?php
					if ( $menus == NULL ) {
						?>
						<tr>
							<td colspan="2">
							<?php echo $_LANG->_( 'None' ); ?>
							</td>
						</tr>
						<?php
					} else {
						mosCommonHTML::menuLinksContent( $menus );
					}
					?>
					<tr>
						<td colspan="2">
						</td>
					</tr>
					</table>
					<?php
					$tabs->endTab();
					$tabs->endPane();
					?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $row->version; ?>" />
		<input type="hidden" name="mask" value="0" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="images" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php

	}


	/**
	* Form to select Section/Category to move item(s) to
	* @param array An array of selected objects
	* @param int The current section we are looking at
	* @param array The list of sections and categories to move to
	*/
	function moveSection( $cid, $sectCatList, $option, $sectionid, $items ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (!getSelectedValue( 'adminForm', 'sectcat' )) {
				alert( "<?php echo $_LANG->_( 'Please select something' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">
		<br />
		<table class="adminheading">
		<tr>
			<th class="edit">
			<?php echo $_LANG->_( 'Move Items' ); ?>
			</th>
		</tr>
		</table>

		<br />
		<table class="adminform">
		<tr>
			<td align="left" valign="top" width="40%">
			<strong><?php echo $_LANG->_( 'Move to Section/Category' ); ?>:</strong>
			<br />
			<?php echo $sectCatList; ?>
			<br /><br />
			</td>
			<td align="left" valign="top">
			<strong><?php echo $_LANG->_( 'Items being Moved' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->title ."</li>";
			}
			echo "</ol>";
			?>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sectionid; ?>" />
		<input type="hidden" name="task" value="" />
		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}



	/**
	* Form to select Section/Category to copys item(s) to
	*/
	function copySection( $option, $cid, $sectCatList, $sectionid, $items  ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (!getSelectedValue( 'adminForm', 'sectcat' )) {
				alert( "<?php echo $_LANG->_( 'VALIDSELECTSECTCATCOPYITEMS' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<br />
		<table class="adminheading">
		<tr>
			<th class="edit">
			<?php echo $_LANG->_( 'Copy Content Items' ); ?>
			</th>
		</tr>
		</table>

		<br />
		<table class="adminform">
		<tr>
			<td align="left" valign="top" width="40%">
			<strong><?php echo $_LANG->_( 'Copy to Section/Category' ); ?>:</strong>
			<br />
			<?php echo $sectCatList; ?>
			<br /><br />
			</td>
			<td align="left" valign="top">
			<strong><?php echo $_LANG->_( 'Items being copied' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $items as $item ) {
				echo "<li>". $item->title ."</li>";
			}
			echo "</ol>";
			?>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sectionid; ?>" />
		<input type="hidden" name="task" value="" />
		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}
}
?>