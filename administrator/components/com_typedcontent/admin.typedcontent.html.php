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
class HTML_typedcontent {

	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showContent( &$rows, &$pageNav, $option, $search, &$lists ) {
		global $my, $acl, $database;
		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<th class="edit">
			<?php echo $_LANG->_( 'Static Content Manager' ); ?>
			</th>
			<td>
			<?php echo $_LANG->_( 'Filter' ); ?>:&nbsp;
			</td>
			<td>
			<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
			</td>
			<td>
			&nbsp;&nbsp;&nbsp;<?php echo $_LANG->_( 'Order' ); ?>:&nbsp;
			</td>
			<td>
			<?php echo $lists['order']; ?>
			</td>
			<td width="right">
			<?php echo $lists['authorid'];?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="5">
			<?php echo $_LANG->_( 'NUM' ); ?>
			</th>
			<th width="5px">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th class="title">
			<?php echo $_LANG->_( 'Title' ); ?>
			</th>
			<th width="5%">
			<?php echo $_LANG->_( 'Published' ); ?>
			</th>
			<th width="2%">
			<?php echo $_LANG->_( 'Order' ); ?>
			</th>
			<th width="1%">
			<a href="javascript: saveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo $_LANG->_( 'Save Order' ); ?>" /></a>
			</th>
			<th width="10%">
			<?php echo $_LANG->_( 'Access' ); ?>
			</th>
			<th width="5%">
			<?php echo $_LANG->_( 'ID' ); ?>
			</th>
			<th width="1%" align="left">
			<?php echo $_LANG->_( 'Links' ); ?>
			</th>
			<th width="20%" align="left">
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

			if ( !$row->access ) {
				$color_access = 'style="color: green;"';
				$task_access = 'accessregistered';
			} else if ( $row->access == 1 ) {
				$color_access = 'style="color: red;"';
				$task_access = 'accessspecial';
			} else {
				$color_access = 'style="color: black;"';
				$task_access = 'accesspublic';
			}

			$link = 'index2.php?option=com_typedcontent&task=edit&hidemainmenu=1&id='. $row->id;

			if ( $row->checked_out ) {
				$checked	 		= mosCommonHTML::checkedOut( $row );
			} else {
				$checked	 		= mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
			}

			if ( $acl->acl_check( 'administration', 'manage', 'users', $my->usertype, 'components', 'com_users' ) ) {
				if ( $row->created_by_alias ) {
					$author = $row->created_by_alias;
				} else {
					$linkA 	= 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->created_by;
					$author = '<a href="'. $linkA .'" title="'. $_LANG->_( 'Edit User' ) .'">'. $row->creator .'</a>';
				}
			} else {
				if ( $row->created_by_alias ) {
					$author = $row->created_by_alias;
				} else {
					$author = $row->creator;
				}
			}

			$date = mosFormatDate( $row->created, '%x' );
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
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->title;
					if ( $row->title_alias ) {
						echo ' (<i>'. $row->title_alias .'</i>)';
					}
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Static Content' ); ?>">
					<?php
					echo $row->title;
					if ( $row->title_alias ) {
						echo ' (<i>'. $row->title_alias .'</i>)';
					}
					?>
					</a>
					<?php
				}
				?>
				</td>
				<?php
				if ( $times ) {
					?>
					<td align="center">
					<a href="javascript: void(0);" onMouseOver="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Publish Information' ); ?>', BELOW, RIGHT);" onMouseOut="return nd();" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? "unpublish" : "publish";?>')">
					<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
					</a>
					</td>
					<?php
				}
				?>
				<td align="center" colspan="2">
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td align="center">
				<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task_access;?>')" <?php echo $color_access; ?>>
				<?php echo $row->groupname;?>
				</a>
				</td>
				<td align="center">
				<?php echo $row->id;?>
				</td>
				<td align="center">
				<?php echo $row->links;?>
				</td>
				<td align="left">
				<?php echo $author;?>
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
		<?php mosCommonHTML::ContentLegend(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function edit( &$row, &$images, &$lists, &$params, $option, &$menus ) {
		global $_LANG;

		//mosMakeHtmlSafe( $row );
		$tabs = new mosTabs( 1 );
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
		var folderimages = new Array;
		<?php
		$i = 0;
		foreach ($images as $k=>$items) {
			foreach ($items as $v) {
				echo "\n	folderimages[".$i++."] = new Array( '$k','".addslashes( $v->value )."','".addslashes( $v->text )."' );";
			}
		}
		?>
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			if ( pressbutton ==' resethits' ) {
				if (confirm('<?php echo $_LANG->_( 'WARNWANTRESETHITSTOZERO' ); ?>')){
					submitform( pressbutton );
					return;
				} else {
					return;
				}
			}

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please select a Menu' ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please enter a Name for this menu item' ); ?>" );
					return;
				}
			}

			var temp = new Array;
			for (var i=0, n=form.imagelist.options.length; i < n; i++) {
				temp[i] = form.imagelist.options[i].value;
			}
			form.images.value = temp.join( '\n' );

			try {
				document.adminForm.onsubmit();
			}
			catch(e){}
			if (trim(form.title.value) == ""){
				alert( "<?php echo $_LANG->_( 'Content item must have a title' ); ?>" );
			} else if (trim(form.name.value) == ""){
				alert( "<?php echo $_LANG->_( 'Content item must have a name' ); ?>" );
			} else {
				if ( form.reset_hits.checked ) {
					form.hits.value = 0;
				} else {
				}
				<?php getEditorContents( 'editor1', 'introtext' ) ; ?>
				submitform( pressbutton );
			}
		}
		</script>

		<table class="adminheading">
		<tr>
			<th class="edit">
			<?php echo $_LANG->_( 'Static Content Item' ); ?>:
			<small>
			<?php echo $row->id ? $_LANG->_( 'Edit' ) : $_LANG->_( 'New' );?>
			</small>
			</th>
		</tr>
		</table>

		<form action="index2.php" method="post" name="adminForm">

		<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="3">
					<?php echo $_LANG->_( 'Item Details' ); ?>
					</th>
				<tr>
				<tr>
					<td align="left">
					<?php echo $_LANG->_( 'Title' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="title" size="30" maxlength="100" value="<?php echo $row->title; ?>" />
					</td>
				</tr>
				<tr>
					<td align="left">
					<?php echo $_LANG->_( 'Title Alias' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="title_alias" size="30" maxlength="100" value="<?php echo $row->title_alias; ?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="left" colspan="2">
					<?php echo $_LANG->_( 'Text: (required)' ); ?><br />
					<?php
					// parameters : areaname, content, hidden field, width, height, rows, cols
					editorArea( 'editor1',  $row->introtext, 'introtext', '100%;', '500', '75', '50' );
					?>
					</td>
				</tr>
				</table>
			</td>
			<td width="40%" valign="top">
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
					<?php echo $_LANG->_( 'State' ); ?>:
					</td>
					<td>
					<?php echo $row->state > 0 ? $_LANG->_( 'Published' ) : $_LANG->_( 'Draft Unpublished' ); ?>
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
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Author Alias' ); ?>:
					</td>
					<td>
					<input type="text" name="created_by_alias" size="30" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="inputbox" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Change Creator' ); ?>:
					</td>
					<td>
					<?php echo $lists['created_by']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Override Created Date' ); ?>
					</td>
					<td>
					<input class="inputbox" type="text" name="created" id="created" size="25" maxlength="19" value="<?php echo $row->created; ?>" />
					<input name="reset" type="reset" class="button" onClick="return showCalendar('created', 'y-mm-dd');" value="...">
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Start Publishing' ); ?>:
					</td>
					<td width="80%">
					<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
					<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');">
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Finish Publishing' ); ?>:
					</td>
					<td width="80%">
					<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
					<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');">
					</td>
				</tr>
				</table>
				<br />
				<table class="adminform" width="100%">
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
					<strong><?php echo $_LANG->_( 'State' ); ?></strong>
					</td>
					<td>
					<?php echo $row->state > 0 ? $_LANG->_( 'Published' ) : ($row->state < 0 ? $_LANG->_( 'Archived' ) : $_LANG->_( 'Draft Unpublished' ) );?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<strong><?php echo $_LANG->_( 'Hits' ); ?></strong>
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
					<strong><?php echo $_LANG->_( 'Version' ); ?></strong>
					</td>
					<td>
					<?php echo "$row->version";?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<strong><?php echo $_LANG->_( 'Created' ); ?></strong>
					</td>
					<td>
					<?php echo $row->created ? "$row->created</td></tr><tr><td valign='top' align='right'><strong>". $_LANG->_( 'By' ) ."</strong></td><td>". $row->creator : $_LANG->_( 'New document' );?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<strong><?php echo $_LANG->_( 'Last Modified' ); ?></strong>
					</td>
					<td>
					<?php echo $row->modified ? "$row->modified</td></tr><tr><td valign='top' align='right'><strong>". $_LANG->_( 'By' ) ."</strong></td><td>". $row->modifier : $_LANG->_( 'Not modified' );?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<strong><?php echo $_LANG->_( 'Expires' ); ?></strong>
					</td>
					<td>
					<?php echo "$row->publish_down";?>
					</td>
				</tr>
				</table>
				<?php
       	   		$title = $_LANG->_( 'Images' );
				$tabs->endTab();
				$tabs->startTab( $title, "images-page" );
				?>
				<table class="adminform">
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
					<td>
					<?php echo $_LANG->_( 'Edit the image selected' ); ?>:
						<table>
						<tr>
							<td align="right">
							<?php echo $_LANG->_( 'Source' ); ?>
							</td>
							<td>
							<input type="text" name= "_source" value="" />
							</td>
						</tr>
						<tr>
							<td align="right">
							<?php echo $_LANG->_( 'Align' ); ?>
							</td>
							<td>
							<?php echo $lists['_align']; ?>
							</td>
						</tr>
						<tr>
							<td align="right">
							<?php echo $_LANG->_( 'Alt Text' ); ?>
							</td>
							<td>
							<input type="text" name="_alt" value="" />
							</td>
						</tr>
						<tr>
							<td align="right">
							<?php echo $_LANG->_( 'Border' ); ?>
							</td>
							<td>
							<input type="text" name="_border" value="" size="3" maxlength="1" />
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
							<?php echo $_LANG->_( 'Width' ); ?>:
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
					<td align="left">
					<?php echo $_LANG->_( 'Description' ); ?>:<br />
					<textarea class="inputbox" cols="40" rows="5" name="metadesc" style="width:300px"><?php echo str_replace('&','&amp;',$row->metadesc); ?></textarea>
					</td>
				</tr>
				<tr>
					<td align="left">
					<?php echo $_LANG->_( 'Keywords' ); ?>:<br />
					<textarea class="inputbox" cols="40" rows="5" name="metakey" style="width:300px"><?php echo str_replace('&','&amp;',$row->metakey); ?></textarea>
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
					<?php echo $_LANG->_( 'DESCLINKSTATIC' ); ?>
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

		<input type="hidden" name="images" value="" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="hits" value="<?php echo $row->hits; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>