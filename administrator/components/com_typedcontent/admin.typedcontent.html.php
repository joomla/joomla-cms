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
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Content
*/
class HTML_typedcontent {

	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showContent( &$rows, &$pageNav, $option, &$lists ) {
		global $my, $mainframe, $database;
		
		/*
		 * Initialize variables
		 */
		$user	= & $mainframe->getUser();

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_typedcontent" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['authorid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">				
			<table class="adminlist">
			<tr>
				<th width="5">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="5">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Title', 'c.title', $lists ); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Published', 'c.state', $lists ); ?>
				</th>
				<th nowrap="nowrap" width="1%">
					<?php mosCommonHTML :: tableOrdering( 'Front Page', 'frontpage', $lists ); ?>
				</th>
				<th width="2%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Order', 'c.ordering', $lists ); ?>
				</th>
				<th width="1%">
					<?php mosCommonHTML :: saveorderButton( $rows ); ?>
				</th>
				<th width="7%">
					<?php mosCommonHTML :: tableOrdering( 'Access', 'groupname', $lists ); ?>
				</th>
				<th width="3%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'c.id', $lists ); ?>
				</th>
				<th width="15%"  class="title">
					<?php mosCommonHTML :: tableOrdering( 'Author', 'creator', $lists ); ?>
				</th>
				<th align="center" width="10%">
					<?php mosCommonHTML :: tableOrdering( 'Date', 'c.created', $lists ); ?>
				</th>
				<th width="1%" >
					<?php echo JText::_( 'Links' ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			$nullDate = $database->getNullDate();
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$now = date( 'Y-m-d H:i:s' );
				if ( $now <= $row->publish_up && $row->state == 1 ) {
					$img = 'publish_y.png';
					$alt = JText::_( 'Published' );
				} else if ( ( $now <= $row->publish_down || $row->publish_down == $nullDate ) && $row->state == 1 ) {
					$img = 'publish_g.png';
					$alt = JText::_( 'Published' );
				} else if ( $now > $row->publish_down && $row->state == 1 ) {
					$img = 'publish_r.png';
					$alt = JText::_( 'Expired' );
				} elseif ( $row->state == "0" ) {
					$img = "publish_x.png";
					$alt = JText::_( 'Unpublished' );
				}
				$times = '';
				if (isset($row->publish_up)) {
					if ($row->publish_up == $nullDate) {
						$times .= "<tr><td>". JText::_( 'Start: Always' ) ."</td></tr>";
					} else {
						$times .= "<tr><td>". JText::_( 'Start' ) .": ". $row->publish_up ."</td></tr>";
					}
				}
				if (isset($row->publish_down)) {
					if ($row->publish_down == $nullDate) {
						$times .= "<tr><td>". JText::_( 'Finish: No Expiry' ) ."</td></tr>";
					} else {
						$times .= "<tr><td>". JText::_( 'Finish' ) .": ". $row->publish_down ."</td></tr>";
					}
				}
	
				$link = 'index2.php?option=com_typedcontent&task=edit&hidemainmenu=1&id='. $row->id;
	
				if ( $row->checked_out ) {
					$checked	 		= mosCommonHTML::checkedOut( $row );
				} else {
					$checked	 		= mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
				}
	
				if ( $user->authorize( 'com_users', 'manage' ) ) {
					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$linkA 	= 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->created_by;
						$author = '<a href="'. ampReplace( $linkA ) .'" title="'. JText::_( 'Edit User' ) .'">'. $row->creator .'</a>';
					}
				} else {
					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$author = $row->creator;
					}
				}
	
				$access = mosCommonHTML::AccessProcessing( $row, $i );
				$date 	= mosFormatDate( $row->created, JText::_( 'DATE_FORMAT_LC4' ) );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
	    			<?php
	    			if ( $row->title_alias ) {
	                    ?>
	                    <td onmouseover="return overlib('<?php echo $row->title_alias; ?>', CAPTION, '<?php echo JText::_( 'Title Alias' ); ?>', BELOW, RIGHT);" onmouseout="return nd();" >
	                    <?php
	    			} else {
						echo "<td>";
	                }
					if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
						echo $row->title;
					} else {
						?>
						<a href="<?php echo ampReplace( $link ); ?>">
							<?php echo htmlspecialchars($row->title, ENT_QUOTES); ?></a>
						<?php
					}
					?>
					</td>
					<?php
					if ( $times ) {
						?>
						<td align="center">
						<a href="javascript:void(0);" onMouseOver="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo JText::_( 'Publish Information' ); ?>', BELOW, RIGHT);" onMouseOut="return nd();" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? "unpublish" : "publish";?>')">
							<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" /></a>
						</td>
						<?php
					}
					?>
					<td align="center">
						<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','toggle_frontpage')">
							<img src="images/<?php echo ( $row->frontpage ) ? 'tick.png' : 'publish_x.png';?>" width="12" height="12" border="0" alt="<?php echo ( $row->frontpage ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" /></a>
					</td>
					<td align="center" colspan="2">
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td align="center">
						<?php echo $row->id;?>
					</td>
					<td>
						<?php echo $author;?>
					</td>
					<td align="center">
						<?php echo $date; ?>
					</td>
					<td align="center">
						<?php echo $row->links;?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
	
			<?php echo $pageNav->getListFooter(); ?>
			<?php mosCommonHTML::ContentLegend(); ?>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	function edit( &$row, &$images, &$lists, &$params, $option, &$menus ) {

		$tabs = new mosTabs(0);
		
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

			if ( pressbutton == 'resethits' ) {
				if (confirm('<?php echo JText::_( 'WARNWANTRESETHITSTOZERO', true ); ?>')){
					submitform( pressbutton );
					return;
				} else {
					return;
				}
			}

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == "" ) {
					alert( "<?php echo JText::_( 'Please select a Menu', true ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo JText::_( 'Please enter a Name for this menu item', true ); ?>" );
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
				alert( "<?php echo JText::_( 'Content item must have a title', true ); ?>" );
			} else if (trim(form.name.value) == ""){
				alert( "<?php echo JText::_( 'Content item must have a name', true ); ?>" );
			} else {
				if ( form.reset_hits.checked ) {
					form.hits.value = 0;
				} else {
				}
				<?php 
				$editor =& JEditor::getInstance();
				echo $editor->getEditorContents( 'editor1', 'introtext' ) ; ?>
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<div id="editcell">				
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td width="60%" valign="top">
					<table class="adminform">
					<tr>
						<td>
							<?php
							// parameters : areaname, content, hidden field, width, height, rows, cols
							$editor =& JEditor::getInstance();
							echo $editor->getEditor( 'editor1',  $row->introtext, 'introtext', '100%;', '500', '75', '50' );
							?>
						</td>
					</tr>
					</table>
				</td>
				<td width="40%" valign="top">
					<?php
	    	   		$title = JText::_( 'Details' );
					$tabs->startPane("content-pane");
					$tabs->startTab( $title, "detail-page" );
					?>
					
						<table class="adminform">
						<tr>
							<td width="130">
								<?php echo JText::_( 'Title' ); ?>:
							</td>
							<td valign="top" align="right">
								<input class="inputbox" type="text" name="title" size="40" maxlength="255" value="<?php echo $row->title; ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'Title Alias' ); ?>:
							</td>
							<td valign="top" align="right">
								<input class="inputbox" type="text" name="title_alias" size="40" maxlength="255" value="<?php echo $row->title_alias; ?>" />
							</td>
						</tr>
						</table>
						
						<br />
						
						<table class="adminform">
						<tr>
							<td valign="top" align="right" width="130">
								<?php echo JText::_( 'Published' ); ?>:
							</td>
							<td>
								<?php echo $lists['state']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Frontpage' ); ?>:
							</td>
							<td>
								<?php echo $lists['frontpage']; ?> 
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Access Level' ); ?>:
							</td>
							<td>
								<?php echo $lists['access']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Author Alias' ); ?>:
							</td>
							<td>
								<input type="text" name="created_by_alias" size="30" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="inputbox" />
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
							<?php echo JText::_( 'Change Creator' ); ?>:
							</td>
							<td>
							<?php echo $lists['created_by']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Override Created Date' ); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="created" id="created" size="25" maxlength="19" value="<?php echo $row->created; ?>" />
								<input name="reset" type="reset" class="button" onclick="return showCalendar('created', 'y-mm-dd');" value="..." />
							</td>
						</tr>
						<tr>
							<td align="right">
								<?php echo JText::_( 'Start Publishing' ); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
								<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');" />
							</td>
						</tr>
						<tr>
							<td align="right">
								<?php echo JText::_( 'Finish Publishing' ); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
								<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');" />
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
									<strong><?php echo JText::_( 'Content ID' ); ?>:</strong>
								</td>
								<td>
									<?php echo $row->id; ?>
								</td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td valign="top" align="right" width="130">
								<strong><?php echo JText::_( 'Hits' ); ?></strong>
							</td>
							<td>
								<?php echo $row->hits;?>
								<div <?php echo $visibility; ?>>
									<input name="reset_hits" type="button" class="button" value="<?php echo JText::_( 'Reset Hit Count' ); ?>" onclick="submitbutton('resethits');" />
								</div>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<strong><?php echo JText::_( 'Version' ); ?></strong>
							</td>
							<td>
								<?php echo "$row->version";?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<strong><?php echo JText::_( 'Created' ); ?></strong>
							</td>
							<td>
								<?php echo $row->created ? "$row->created</td></tr><tr><td valign='top' align='right'><strong>". JText::_( 'By' ) ."</strong></td><td>". $row->creator : JText::_( 'New document' );?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<strong><?php echo JText::_( 'Last Modified' ); ?></strong>
							</td>
							<td>
								<?php echo $row->modified ? "$row->modified</td></tr><tr><td valign='top' align='right'><strong>". JText::_( 'By' ) ."</strong></td><td>". $row->modifier : JText::_( 'Not modified' );?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<strong><?php echo JText::_( 'Expires' ); ?></strong>
							</td>
							<td>
								<?php echo "$row->publish_down";?>
							</td>
						</tr>
						</table>
					
					<?php
	       	   		$title = JText::_( 'Images' );
					$tabs->endTab();
					$tabs->startTab( $title, "images-page" );
					?>
					
						<table class="adminform">
						<tr>
							<td colspan="2">
								<table width="100%">
								<tr>
									<td width="48%">
										<div align="center">
											<?php echo JText::_( 'Gallery Images' ); ?>:
											<br />
											<?php echo $lists['imagefiles'];?>
											<br />
											<?php echo JText::_( 'Sub-folder' ); ?>: <?php echo $lists['folders'];?>
										</div>
									</td>
									<td width="2%">
										<input class="button" type="button" value=">>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" title="<?php echo JText::_( 'Add' ); ?>"/>
										<br />
										<input class="button" type="button" value="<<" onclick="delSelectedFromList('adminForm','imagelist')" title="<?php echo JText::_( 'Remove' ); ?>"/>
									</td>
									<td width="48%">
										<div align="center">
											<?php echo JText::_( 'Content Images' ); ?>:
											<br />
											<?php echo $lists['imagelist'];?>
											<br />
											<input class="button" type="button" value="<?php echo JText::_( 'Up' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,-1)" />
											<input class="button" type="button" value="<?php echo JText::_( 'Down' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,+1)" />
										</div>
									</td>
								</tr>
								</table>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<div align="center" style="border: 1px solid #d5d5d5;">
									<?php echo JText::_( 'Sample Image' ); ?>:<br />
									<img name="view_imagefiles" src="../images/M_images/blank.png" width="100" />
								</div>
							</td>
							<td valign="top">
								<div align="center" style="border: 1px solid #d5d5d5;">
									<?php echo JText::_( 'Active Image' ); ?>:<br />
									<img name="view_imagelist" src="../images/M_images/blank.png" width="100" />
								</div>
							</td>
						</tr>
						</table>
						
						<br />
						
						<table class="adminform">
						<tr>
							<td>
							<?php echo JText::_( 'Edit the image selected' ); ?>:
								<table>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Source' ); ?>
									</td>
									<td>
										<input type="text" name= "_source" value="" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Align' ); ?>
									</td>
									<td>
										<?php echo $lists['_align']; ?>
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Alt Text' ); ?>
									</td>
									<td>
										<input type="text" name="_alt" value="" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Border' ); ?>
									</td>
									<td>
										<input type="text" name="_border" value="" size="3" maxlength="1" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Caption' ); ?>:
									</td>
									<td>
										<input class="text_area" type="text" name="_caption" value="" size="30" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Caption Position' ); ?>:
									</td>
									<td>
										<?php echo $lists['_caption_position']; ?>
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Caption Align' ); ?>:
									</td>
									<td>
										<?php echo $lists['_caption_align']; ?>
									</td>
								</tr>
								<tr>
									<td align="right">
										<?php echo JText::_( 'Width' ); ?>:
									</td>
									<td>
										<input class="text_area" type="text" name="_width" value="" size="5" maxlength="5" />
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<input class="button" type="button" value="<?php echo JText::_( 'Apply' ); ?>" onclick="applyImageProps()" />
									</td>
								</tr>
								</table>
							</td>
						</tr>
						</table>
					
					<?php
	       	   		$title = JText::_( 'Parameters' );
					$tabs->endTab();
					$tabs->startTab( $title, "params-page" );
					?>
					
						<table class="adminform">
						<tr>
							<td>
								<?php echo $params->render();?>
							</td>
						</tr>
						</table>
					
					<?php					
	       	   		$title = JText::_( 'Meta Info' );
					$tabs->endTab();
					$tabs->startTab( $title, "metadata-page" );
					?>
					
						<table class="adminform">
						<tr>
							<td >
								<?php echo JText::_( 'Description' ); ?>:
								<br />
								<textarea class="inputbox" cols="40" rows="5" name="metadesc" style="width:300"><?php echo str_replace('&','&amp;',$row->metadesc); ?></textarea>
							</td>
						</tr>
						<tr>
							<td >
								<?php echo JText::_( 'Keywords' ); ?>:
								<br />
								<textarea class="inputbox" cols="40" rows="5" name="metakey" style="width:300"><?php echo str_replace('&','&amp;',$row->metakey); ?></textarea>
							</td>
						</tr>
						</table>
					
					<?php
	       	   		$title = JText::_( 'Link to Menu' );
					$tabs->endTab();
					$tabs->startTab( $title, "link-page" );
					?>
					
						<table class="adminform">
						<tr>
							<td colspan="2">
								<?php echo JText::_( 'DESCLINKSTATIC' ); ?>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td valign="top" width="90">
								<?php echo JText::_( 'Select a Menu' ); ?>
							</td>
							<td>
								<?php echo $lists['menuselect']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="90">
								<?php echo JText::_( 'Menu Item Name' ); ?>
							</td>
							<td>
								<input type="text" name="link_name" class="inputbox" value="" size="30" />
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
						
						<?php
						if ( $menus != NULL ) {
							?>
							<br />
							
							<table class="adminform">
							<tr>
								<td colspan="2">
									<?php mosCommonHTML::menuLinksContent( $menus ); ?>
								</td>
							</tr>
							</table>
							<?php
						}
						?>
					
					<?php
					$tabs->endTab();
					$tabs->endPane();
					?>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="images" value="" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="hits" value="<?php echo $row->hits; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	* Form to select Section/Category to move item(s) to
	* @param array An array of selected objects
	* @param int The current section we are looking at
	* @param array The list of sections and categories to move to
	*/
	function move( $cid, $sectCatList, $items ) {
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
				alert( "<?php echo JText::_( 'Please select something', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php?option=com_typedcontent" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td  valign="top" width="40%">
			<strong><?php echo JText::_( 'Move to Section/Category' ); ?>:</strong>
			<br />
			<?php echo $sectCatList; ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong><?php echo JText::_( 'Items being Moved' ); ?>:</strong>
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

		<input type="hidden" name="option" value="com_typedcontent" />
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