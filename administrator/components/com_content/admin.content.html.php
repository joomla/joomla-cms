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
class HTML_content {

	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showContent( &$rows, $section, &$lists, $pageNav, $all=NULL, $redirect ) {
		global $my, $mainframe, $database;
		
		/*
		 * Initialize variables
		 */
		$user	= & $mainframe->getUser();

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_content&amp;sectionid=<?php echo $section->id; ?>" method="post" name="adminForm">

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
				if ( $all ) {
					echo $lists['sectionid'];
				}
				echo $lists['catid'];
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
					<?php echo JText::_( 'Num' ); ?>
				</th>
				<th width="5">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Title', 'c.title', $lists ); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Published', 'c.state', $lists ); ?>
				</th>
				<th nowrap="nowrap" width="1%">
					<?php mosCommonHTML :: tableOrdering( 'Front Page', 'frontpage', $lists ); ?>
				</th>
				<?php
				if ( $lists['order'] == 'section_name' || !$lists['order'] ) {
					?>
					<th colspan="2" align="center" width="5%">
						<?php echo JText::_( 'Reorder' ); ?>
					</th>
					<th width="2%">
						<?php echo JText::_( 'Order' ); ?>
					</th>
					<th width="1%">
						<?php mosCommonHTML :: saveorderButton( $rows ); ?>
					</th>
					<?php
				}
				?>
				<th width="7%">
					<?php mosCommonHTML :: tableOrdering( 'Access', 'groupname', $lists ); ?>
				</th>
				<th width="2%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'c.id', $lists ); ?>
				</th>
				<?php
				if ( $all ) {
					?>
					<th class="title" width="8%" nowrap="nowrap">
						<?php mosCommonHTML :: tableOrdering( 'Section', 'section_name', $lists ); ?>
					</th>
					<?php
				}
				?>
				<th  class="title" width="8%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Category', 'cc.name', $lists ); ?>
				</th>
				<th  class="title" width="8%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Author', 'author', $lists ); ?>
				</th>
				<th align="center" width="10">
					<?php mosCommonHTML :: tableOrdering( 'Date', 'c.created', $lists ); ?>
				</th>
			  </tr>
			<?php
			$k = 0;
			$nullDate = $database->getNullDate();
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$link 	= 'index2.php?option=com_content&sectionid='. $redirect .'&task=edit&hidemainmenu=1&id='. $row->id;
	
				$row->sect_link = ampReplace( 'index2.php?option=com_sections&task=editA&hidemainmenu=1&id='. $row->sectionid );
				$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&task=editA&hidemainmenu=1&id='. $row->catid );
	
				$now = date( 'Y-m-d H:i:s' );
				if ( $now <= $row->publish_up && $row->state == "1" ) {
					$img = 'publish_y.png';
					$alt = JText::_( 'Published' );
				} else if ( ( $now <= $row->publish_down || $row->publish_down == $nullDate ) && $row->state == "1" ) {
					$img = 'publish_g.png';
					$alt = JText::_( 'Published' );
				} else if ( $now > $row->publish_down && $row->state == "1" ) {
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
	
				if ( $user->authorize( 'com_users', 'manage' ) ) {
					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$linkA 	= 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->created_by;
						$author = '<a href="'. ampReplace( $linkA ) .'" title="'. JText::_( 'Edit User' ) .'">'. $row->author .'</a>';
					}
				} else {
					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$author = $row->author;
					}
				}
	
				$date = mosFormatDate( $row->created, JText::_( 'DATE_FORMAT_LC4' ) );
	
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
		    			<?php
		    			if ( $row->title_alias ) {
		                    ?>
		                    <td onmouseover="return overlib('<?php echo $row->title_alias; ?>', CAPTION, '<?php echo JText::_( 'Title Alias' ); ?>', BELOW, RIGHT);" onmouseout="return nd();" >
		                    <?php
		    			}
		    			else{
							echo "<td>";
		                }
						if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
							echo $row->title;
						}
		                else {
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
						<td align="center" onmouseover="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo JText::_( 'Publish Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
							<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? 'unpublish' : 'publish' ?>')">
								<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" /></a>
						</td>
						<?php
					}
					?>
					<td align="center">
						<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','toggle_frontpage')">
							<img src="images/<?php echo ( $row->frontpage ) ? 'tick.png' : 'publish_x.png';?>" width="12" height="12" border="0" alt="<?php echo ( $row->frontpage ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" /></a>
					</td>
					<?php
					if ( $lists['order'] == 'section_name' || !$lists['order'] ) {
						?>
						<td align="right">
							<?php echo $pageNav->orderUpIcon( $i, ($row->catid == @$rows[$i-1]->catid) ); ?>
						</td>
						<td >
							<?php echo $pageNav->orderDownIcon( $i, $n, ($row->catid == @$rows[$i+1]->catid) ); ?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<?php
					}
					?>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td>
						<?php echo $row->id; ?>
					</td>
					<?php
					if ( $all ) {
						?>
						<td>
							<a href="<?php echo $row->sect_link; ?>" title="<?php echo JText::_( 'Edit Section' ); ?>">
								<?php echo $row->section_name; ?></a>
						</td>
						<?php
					}
					?>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
							<?php echo $row->name; ?></a>
					</td>
					<td>
						<?php echo $author; ?>
					</td>
					<td nowrap="nowrap">
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
		</div>

		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="sectionid" value="<?php echo $section->id;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showArchive( &$rows, $section, &$lists, $pageNav, $option, $all=NULL, $redirect ) {
		global $my, $mainframe;
		
		/*
		 * Initialize variables
		 */
		$user	= & $mainframe->getUser();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'remove') {
				if (document.adminForm.boxchecked.value == 0) {
					alert("<?php echo JText::_( 'VALIDSELECTIONLISTSENDTRASH', true ); ?>");
				} else if ( confirm("<?php echo JText::_( 'VALIDTRASHSELECTEDITEMS', true ); ?>")) {
					submitform('remove');
				}
			} else {
				submitform(pressbutton);
			}
		}
		</script>
		<form action="index2.php?option=com_content&amp;task=showarchive&amp;sectionid=0" method="post" name="adminForm">

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
				if ( $all ) {
					echo $lists['sectionid'];
				}
				echo $lists['catid'];
				echo $lists['authorid'];
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">				
			<table class="adminlist">
			<tr>
				<th width="5">
					<?php echo JText::_( 'Num' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Title', 'c.title', $lists, 'showarchive' ); ?>
				</th>
				<th width="3%"  class="title">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'c.id', $lists, 'showarchive' ); ?>
				</th>
				<th width="15%"  class="title">
					<?php mosCommonHTML :: tableOrdering( 'Section', 'sectname', $lists, 'showarchive' ); ?>
				</th>
				<th width="15%"  class="title">
					<?php mosCommonHTML :: tableOrdering( 'Category', 'cc.name', $lists, 'showarchive' ); ?>
				</th>
				<th width="15%"  class="title">
					<?php mosCommonHTML :: tableOrdering( 'Author', 'author', $lists, 'showarchive' ); ?>
				</th>
				<th align="center" width="10">
					<?php mosCommonHTML :: tableOrdering( 'Date', 'c.created', $lists, 'showarchive' ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&task=editA&hidemainmenu=1&id='. $row->catid );
				$row->sec_link 	= ampReplace( 'index2.php?option=com_sections&task=editA&hidemainmenu=1&id='. $row->sectionid );
	
				if ( $user->authorize( 'com_users', 'manage' ) ) {
					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$linkA 	= ampReplace( 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->created_by );
						$author = '<a href="'. $linkA .'" title="'. JText::_( 'Edit User' ) .'">'. $row->author .'</a>';
					}
				} else {
					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$author = $row->author;
					}
				}
	
				$date = mosFormatDate( $row->created, JText::_( 'DATE_FORMAT_LC4' ) );
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
					<td>
						<?php echo $row->id; ?>
					</td>
					<td>
						<a href="<?php echo $row->sec_link; ?>" title="<?php echo JText::_( 'Edit Section' ); ?>">
							<?php echo $row->sectname; ?></a>
					</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
							<?php echo $row->name; ?></a>
					</td>
					<td>
						<?php echo $author; ?>
					</td>
					<td nowrap="nowrap">
						<?php echo $date; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
	
			<?php echo $pageNav->getListFooter(); ?>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $section->id;?>" />
		<input type="hidden" name="task" value="showarchive" />
		<input type="hidden" name="returntask" value="showarchive" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	/**
	* Writes the edit form for new and existing content item
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param JModelContent The category object
	* @param string The html for the groups select list
	*/
	function editContent( &$row, $section, &$lists, &$sectioncategories, &$images, &$params, $option, $redirect, &$menus ) {
		mosMakeHtmlSafe( $row );

		$create_date = null;
		if ( $row->created != '0000-00-00 00:00:00' ) {
			$create_date 	= mosFormatDate( $row->created, '%A, %d %B %Y %H:%M', '0' );
		}
		$mod_date = null;
		if ( $row->modified != '0000-00-00 00:00:00' ) {
			$mod_date 		= mosFormatDate( $row->modified, '%A, %d %B %Y %H:%M', '0' );
		}

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
					alert( "<?php echo JText::_( 'Please select a Menu', true ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo JText::_( 'Please enter a Name for this menu item', true ); ?>" );
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
				alert( "<?php echo JText::_( 'Content item must have a title', true ); ?>" );
			} else if (form.sectionid.value == "-1"){
				alert( "<?php echo JText::_( 'You must select a Section.', true ); ?>" );
			} else if (form.catid.value == "-1"){
				alert( "<?php echo JText::_( 'You must select a Category.', true ); ?>" );
 			} else if (form.catid.value == ""){
 				alert( "<?php echo JText::_( 'You must select a Category.', true ); ?>" );
			} else {
				<?php 
				$editor =& JEditor::getInstance();
				echo $editor->getEditorContents( 'editor1', 'introtext' );
				echo $editor->getEditorContents( 'editor2', 'fulltext' ); ?>
				submitform( pressbutton );
			}
		}
		//-->
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
							echo $editor->getEditor( 'editor1',  $row->introtext , 'introtext', '100%;', '350', '75', '20' ) ; 
							?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Main Text: (optional)' ); ?>
							<br />
							<?php
							// parameters : areaname, content, hidden field, width, height, rows, cols
							$editor =& JEditor::getInstance();
							echo $editor->getEditor( 'editor2',  $row->fulltext , 'fulltext', '100%;', '400', '75', '30' ) ; 
							?>
						</td>
					</tr>
					</table>
				</td>
				<td valign="top" width="40%">
					<?php
					$title = JText::_( 'Details' );
					$tabs->startPane("content-pane");
					$tabs->startTab( $title, "detail-page" );
					?>				
						
						<table class="adminform">
						<tr>
							<td width="130">
								<label for="title">
									<?php echo JText::_( 'Title' ); ?>:
								</label>
							</td>
							<td valign="top" align="right">
								<input class="inputbox" type="text" name="title" id="title" size="40" maxlength="255" value="<?php echo $row->title; ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<label for="title_alias">
									<?php echo JText::_( 'Title Alias' ); ?>:
								</label>
							</td>
							<td valign="top" align="right">
								<input class="inputbox" type="text" name="title_alias" id="title_alias" size="40" maxlength="255" value="<?php echo $row->title_alias; ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<label for="sectionid">
									<?php echo JText::_( 'Section' ); ?>:
								</label>
							</td>
							<td>
								<?php echo $lists['sectionid']; ?>
							</td>
						</tr>
						<tr>
							<td>
								<label for="catid">
									<?php echo JText::_( 'Category' ); ?>:
								</label>
							</td>
							<td>
								<?php echo $lists['catid']; ?>
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
								<?php echo JText::_( 'Show on Frontpage' ); ?>:
							</td>
							<td>
								<?php echo $lists['frontpage']; ?> 
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<label for="access">
									<?php echo JText::_( 'Access Level' ); ?>:
								</label>
							</td>
							<td>
								<?php echo $lists['access']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<label for="created_by_alias">
									<?php echo JText::_( 'Author Alias' ); ?>:
								</label>
							</td>
							<td>
								<input type="text" name="created_by_alias" id="created_by_alias" size="30" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="inputbox" />
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<label for="created_by">
									<?php echo JText::_( 'Change Creator' ); ?>:
								</label>
							</td>
							<td>
								<?php echo $lists['created_by']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<label for="created">
									<?php echo JText::_( 'Override Created Date' ); ?>
								</label>
							</td>
							<td>
								<input class="inputbox" type="text" name="created" id="created" size="25" maxlength="19" value="<?php echo $row->created; ?>" />
								<input name="reset" type="reset" class="button" onclick="return showCalendar('created', 'y-mm-dd');" value="..." />
							</td>
						</tr>
						<tr>
							<td align="right">
								<label for="publish_up">
									<?php echo JText::_( 'Start Publishing' ); ?>:
								</label>
							</td>
							<td>
								<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
								<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');" />
							</td>
						</tr>
						<tr>
							<td align="right">
								<label for="publish_down">
									<?php echo JText::_( 'Finish Publishing' ); ?>:
								</label>
							</td>
							<td>
								<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
								<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');" />
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
								<strong><?php echo JText::_( 'State' ); ?>:</strong>
							</td>
							<td>
								<?php echo $row->state > 0 ? JText::_( 'Published' ) : ($row->state < 0 ? JText::_( 'Archived' ) : JText::_( 'Draft Unpublished' ) );?>
							</td>
						</tr>
						<tr >
							<td valign="top" align="right">
								<strong>
								<?php echo JText::_( 'Hits' ); ?>
								</strong>:
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
								<strong>
								<?php echo JText::_( 'Revised' ); ?>
								</strong>:
							</td>
							<td>
								<?php echo $row->version;?> <?php echo JText::_( 'times' ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<strong>
								<?php echo JText::_( 'Created' ); ?>
								</strong>
							</td>
							<td>
								<?php
								if ( !$create_date ) {
									echo JText::_( 'New document' );
								} else {
									echo $create_date;
								}
								?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<strong>
								<?php echo JText::_( 'Last Modified' ); ?>
								</strong>
							</td>
							<td>
								<?php
								if ( !$mod_date ) {
									echo JText::_( 'Not modified' );
								} else {
									echo $mod_date;
									?>
									<br />
									<?php
									echo $row->modifier;
								}
								?>
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
											<label for="imagefiles">
												<?php echo JText::_( 'Gallery Images' ); ?>:
											</label>
											<br />
											<?php echo $lists['imagefiles'];?>
											<br />
											<label for="folders">
												<?php echo JText::_( 'Sub-folder' ); ?>: 
											</label>
											<?php echo $lists['folders'];?>
										</div>
									</td>
									<td width="2%">
										<input class="button" type="button" value=">>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" title="<?php echo JText::_( 'Add' ); ?>" />
										<br />
										<input class="button" type="button" value="<<" onclick="delSelectedFromList('adminForm','imagelist')" title="<?php echo JText::_( 'Remove' ); ?>" />
									</td>
									<td width="48%">
										<div align="center">
											<label for="imagelist">
												<?php echo JText::_( 'Content Images' ); ?>:
											</label>
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
										<label for="Isource">
											<?php echo JText::_( 'Source' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name= "_source" id= "Isource" value="" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Ialign">
											<?php echo JText::_( 'Align' ); ?>
										</label>
									</td>
									<td>
										<?php echo $lists['_align']; ?>
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Ialt">
											<?php echo JText::_( 'Alt Text' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="_alt" id="Ialt" value="" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Iborder">
											<?php echo JText::_( 'Border' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="_border" id="Iborder" value="" size="3" maxlength="1" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Icaption">
											<?php echo JText::_( 'Caption' ); ?>:
										</label>
									</td>
									<td>
										<input class="text_area" type="text" name="_caption" id="Icaption" value="" size="30" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Icaption_position">
											<?php echo JText::_( 'Caption Position' ); ?>:
										</label>
									</td>
									<td>
										<?php echo $lists['_caption_position']; ?>
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Icaption_align">
											<?php echo JText::_( 'Caption Align' ); ?>:
										</label>
									</td>
									<td>
										<?php echo $lists['_caption_align']; ?>
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="Iwidth">
											<?php echo JText::_( 'Width' ); ?>:
										</label>
									</td>
									<td>
										<input class="text_area" type="text" name="_width" id="Iwidth" value="" size="5" maxlength="5" />
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
								<?php echo JText::_( 'DESCPARAMCONTROLWHATSEE' ); ?>
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
					$title = JText::_( 'Meta Info' );
					$tabs->endTab();
					$tabs->startTab( $title, "metadata-page" );
					?>
					
						<table class="adminform">
						<tr>
							<td >
								<label for="metadesc">
									<?php echo JText::_( 'Description' ); ?>:
								</label>
								<br />
								<textarea class="inputbox" cols="40" rows="5" name="metadesc" id="metadesc" style="width:300"><?php echo str_replace('&','&amp;',$row->metadesc); ?></textarea>
							</td>
						</tr>
						<tr>
							<td >
								<label for="metakey">
									<?php echo JText::_( 'Keywords' ); ?>:
								</label>
								<br />
								<textarea class="inputbox" cols="40" rows="5" name="metakey" id="metakey" style="width:300"><?php echo str_replace('&','&amp;',$row->metakey); ?></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" class="button" value="<?php echo JText::_( 'Add Sect/Cat/Title' ); ?>" onclick="f=document.adminForm;f.metakey.value=document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].text+', '+getSelectedText('adminForm','catid')+', '+f.title.value+f.metakey.value;" />
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
								<?php echo JText::_( 'DESCWILLCREATELINKINMENU' ); ?>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td valign="top" width="90">
								<label for="menuselect">
									<?php echo JText::_( 'Select a Menu' ); ?>
								</label>
							</td>
							<td>
								<?php echo $lists['menuselect']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="90">
								<label for="link_name">
									<?php echo JText::_( 'Menu Item Name' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="link_name" id="link_name" class="inputbox" value="" size="30" />
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

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
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

		<form action="index2.php" method="post" name="adminForm">

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
				alert( "<?php echo JText::_( 'VALIDSELECTSECTCATCOPYITEMS', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td  valign="top" width="40%">
			<strong><?php echo JText::_( 'Copy to Section/Category' ); ?>:</strong>
			<br />
			<?php echo $sectCatList; ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong><?php echo JText::_( 'Items being copied' ); ?>:</strong>
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

	function previewContent()
	{
		?>
		<script>
		var form = window.opener.document.adminForm
		var title = form.title.value;

		var alltext = form.introtext.value;
		if (form.fulltext) {
			alltext += form.fulltext.value;
		}

		// do the images
		var temp = new Array();
		for (var i=0, n=form.imagelist.options.length; i < n; i++) {
			value = form.imagelist.options[i].value;
			parts = value.split( '|' );

			temp[i] = '<img src="../images/stories/' + parts[0] + '" align="' + parts[1] + '" border="' + parts[3] + '" alt="' + parts[2] + '" hspace="6" />';
		}

		var temp2 = alltext.split( '{mosimage}' );

		var alltext = temp2[0];

		for (var i=0, n=temp2.length-1; i < n; i++) {
			alltext += temp[i] + temp2[i+1];
		}
		</script>

		<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
			<tr>
				<td class="contentheading" colspan="2"><script>document.write(title);</script></td>
			</tr>
		<tr>
			<script>document.write("<td valign=\"top\" height=\"90%\" colspan=\"2\">" + alltext + "</td>");</script>
		</tr>
		<tr>
			<td align="right"><a href="#" onclick="window.close()"><?php echo JText::_( 'Close' ); ?></a></td>
			<td ><a href="javascript:;" onclick="window.print(); return false"><?php echo JText::_( 'Print' ); ?></a></td>
		</tr>
		</table>
		<?php
	}
}
?>
