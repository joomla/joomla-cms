<?php
/**
* @version $Id: admin.typedcontent.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Typed Content
 */
class typedcontentScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$rows, &$lists, $search ) {
		$tmpl =& typedcontentScreens::createTemplate( array( 'components.html' ) );

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addObject( 'sections-list', $lists['sections'], 'row_' );
		$tmpl->addObject( 'categories-list', $lists['categories'], 'row_' );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );
		$tmpl->addVar( 'body2', 'search', $search );

		if ( $lists['trash'] ) {
			$trash = '&nbsp<small>[';
			$trash .= $lists['trash'];
			$trash .= ']</small>';
		} else {
			$trash = '';
		}
		$tmpl->addVar( 'trash', 'trash', $trash );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_authorid', $lists['authorid'] );
		$tmpl->addVar( 'body2', 'lists_access', $lists['access'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}
}

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
		global $my, $mainframe;
  		global $_LANG, $mosConfig_zero_date;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="typedcontentform" class="adminform">

		<?php
		typedcontentScreens::view( $rows, $lists, $search );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="5">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="5">
						<input type="checkbox" name="toggle" value=""  />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'c.title' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'c.state' ); ?>
					</th>
					<th nowrap="nowrap" width="5%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Front Page' ), 'frontpage' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'c.ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th width="10%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Access' ), 'c.access' ); ?>
					</th>
					<th width="1%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'c.id' ); ?>
					</th>
					<th width="1%" align="left">
						<?php echo $_LANG->_( 'Links' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="13" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="13" class="center">
						<?php echo $_LANG->_( 'Display Num' ) ?>
						<?php echo  $pageNav->getLimitBox() ?>
						<?php echo $pageNav->getPagesCounter() ?>
					</td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row = &$rows[$i];

					$now = $mainframe->getDateTime();
					if ( $now <= $row->publish_up && $row->state == 1 ) {
						$img = 'publish_y.png';
						$alt = $_LANG->_( 'Published' );
					} else if ( ( $now <= $row->publish_down || $row->publish_down == $mosConfig_zero_date ) && $row->state == 1 ) {
						$img = 'tick.png';
						$alt = $_LANG->_( 'Published' );
					} else if ( $now > $row->publish_down && $row->state == 1 ) {
						$img = 'publish_r.png';
						$alt = $_LANG->_( 'Expired' );
					} elseif ( $row->state == 0 ) {
						$img = 'publish_x.png';
						$alt = $_LANG->_( 'Unpublished' );
					}
					$times = '';
					if ( isset( $row->publish_up ) ) {
						if ( $row->publish_up == $mosConfig_zero_date ) {
							$times .= '<tr><td>'. $_LANG->_( 'Start' ) .': '. $_LANG->_( 'Always' ) .'</td></tr>';
						} else {
							$times .= '<tr><td>'. $_LANG->_( 'Start' ) .': '. $row->publish_up .'</td></tr>';
						}
					}
					if ( isset( $row->publish_down ) ) {
						if ( $row->publish_down == $mosConfig_zero_date ) {
							$times .= '<tr><td>'. $_LANG->_( 'Finish: No Expiry' ) .'</td></tr>';
						} else {
							$times .= '<tr><td>'. $_LANG->_( 'Finish' ) .': '. $row->publish_down .'</td></tr>';
						}
					}

					$link = 'index2.php?option=com_typedcontent&amp;task=edit&amp;id='. $row->id;

					if ( $row->checked_out ) {
						$checked	= mosAdminHTML::checkedOut( $row );
					} else {
						$checked	= mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
					}

					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$author = $row->creator;
					}

					$access 	= mosAdminHTML::accessProcessing( $row, $i );

					$title			= htmlspecialchars( $row->title, ENT_QUOTES );
					$title_alias	= htmlspecialchars( $row->title_alias, ENT_QUOTES );

					$Cdate 	= mosFormatDate( $row->created, $_LANG->_( 'DATE_FORMAT_LC3') );
					$Ctime 	= mosFormatDate( $row->created, '%H:%M' );
					$Mdate 	= mosFormatDate( $row->modified, $_LANG->_( 'DATE_FORMAT_LC3') );
					$Mtime 	= mosFormatDate( $row->modified, '%H:%M' );
					$info 	= '<tr><td>'. $_LANG->_( 'Title Alias' ) .':</td><td>'. $title_alias .'</td></tr>';
					$info 	.= '<tr><td>'. $_LANG->_( 'Author' ) .':</td><td>'. $author .'</td></tr>';
					$info 	.= '<tr><td>'. $_LANG->_( 'Created' ) .':</td><td>'. $Cdate .'</td></tr>';
					$info 	.= '<tr><td></td><td>'. $Ctime .'</td></tr>';
					$info 	.= '<tr><td>'. $_LANG->_( 'Modified' ) .':</td><td>'. $Mdate .'</td></tr>';
					$info 	.= '<tr><td></td><td>'. $Mtime .'</td></tr>';
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Static Content Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								?>
								<span class="editlinktip">
									<?php echo $title; ?>
								</span>
								<?php
							} else {
								?>
								<a href="<?php echo $link; ?>" class="editlink">
									<span class="editlinktip">
										<?php echo $title; ?>
									</span>
								</a>
								<?php
							}
							?>
						</td>
						<?php
						if ( $times ) {
							?>
							<td align="center">
								<a href="javascript:void(0);" onmouseover="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Publish Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? 'unpublish' : 'publish';?>')">
									<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
								</a>
							</td>
							<?php
						}
						?>
						<td align="center">
							<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','toggle_frontpage')">
								<img src="images/<?php echo ( $row->frontpage ) ? 'tick.png' : 'disabled.png';?>" width="12" height="12" border="0" alt="<?php echo ( $row->frontpage ) ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' );?>" title="<?php echo ( $row->frontpage ) ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' );?>"/>
							</a>
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
						<td align="center">
							<?php echo $row->links;?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
				</table>
			</fieldset>

			<?php
			mosFS::load( '@class', 'com_content' );
			mosContentFactory::buildContentLegend();
			?>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function edit( &$row, &$images, &$lists, &$params, $option, &$menus ) {
 		global $_LANG;

		mosMakeHtmlSafe( $row );


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
		<!--
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
				if ( confirm('<?php echo $_LANG->_( 'WARNWANTRESETHITSTOZERO' ); ?>') ) {
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
			if (trim(form.title.value) == ''){
				alert( "<?php echo $_LANG->_( 'Content item must have a title' ); ?>" );
			} else if (trim(form.name.value) == ''){
				alert( "<?php echo $_LANG->_( 'Content item must have a name' ); ?>" );
			} else {
				if ( form.reset_hits.checked ) {
					form.hits.value = 0;
				}
				<?php getEditorContents( 'editor1', 'introtext' ) ; ?>
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
<div id="datacellfull">
		<fieldset>
			<legend>
				<?php echo $_LANG->_( 'Static content' ); ?>
			</legend>
		<table width="100%">
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
						<?php echo $_LANG->_( 'Text: (required)' ); ?>
						<br />
						<?php
						// parameters : areaname, content, hidden field, width, height, rows, cols
						editorArea( 'editor1',  $row->introtext, 'introtext', '100%;', '400', '65', '50' );
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
						<td valign="top" align="right" width="120">
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
							<?php echo $lists['state']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
							<?php echo $_LANG->_( 'Show on Frontpage' ); ?>:
						</td>
						<td>
							<?php echo $lists['frontpage']; ?>
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
						<td align="right">
							<?php echo $_LANG->_( 'Start Publishing' ); ?>:
						</td>
						<td>
							<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
							<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');">
						</td>
					</tr>
					<tr>
						<td align="right">
							<?php echo $_LANG->_( 'Finish Publishing' ); ?>:
						</td>
						<td>
							<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
							<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');">
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
								<?php echo $_LANG->_( 'Content ID' ); ?>:
							</td>
							<td>
								<?php echo $row->id; ?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td valign="top" align="right" width="120">
							<?php echo $_LANG->_( 'Hits' ); ?>:
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
							<?php echo $_LANG->_( 'Version' ); ?>
						</td>
						<td>
							<?php echo $row->version; ?>
						</td>
					</tr>

					<?php
					if ( $row->created ) {
						?>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Created' ); ?>:
							</td>
							<td>
								<?php echo $row->created; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'By' ); ?>:
							</td>
							<td>
								<?php echo $row->creator; ?>
							</td>
						</tr>
						<?php
					} else {
						?>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Created' ); ?>:
							</td>
							<td>
								<?php echo $_LANG->_( 'New document' ); ?>
							</td>
						</tr>
						<?php
					}
					?>

					<?php
					if ( $row->modified ) {
						?>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Last Modified' ); ?>:
							</td>
							<td>
							<?php echo $row->modified; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'By' ); ?>:
							</td>
							<td>
								<?php echo  $row->modifier;?>
							</td>
						</tr>
						<?php
					} else {
						?>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Last Modified' ); ?>:
							</td>
							<td>
								<?php echo $_LANG->_( 'Not modified' ); ?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td valign="top" align="right">
							<?php echo $_LANG->_( 'Expires' ); ?>
						</td>
						<td>
							<?php echo $row->publish_down;?>
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
					<tr>
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
									<br/>
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
								<img name="view_imagefiles" src="../images/M_images/blank.png" width="100" />
							</div>
						</td>
						<td valign="top">
							<div align="center">
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
									<label for="_width"  onmouseover="return overlib('<?php echo $_LANG->_( 'TIPSMOSIMAGEWIDTH' ); ?>');" onmouseout="return nd();">
										<span class="editlinktip">
											<?php echo $_LANG->_( 'Caption Width' ); ?>:
										</span>
									</label>
								</td>
								<td>
									<input class="text_area" type="text" name="_width" value="" size="5" maxlength="5" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<label for="_width"  onmouseover="return overlib('<?php echo $_LANG->_( 'TIPSMOSIMAGELINK' ); ?>');" onmouseout="return nd();">
										<span class="editlinktip">
											<?php echo $_LANG->_( 'Link Url' ); ?>:
										</span>
									</label>
								</td>
								<td>
									<input class="text_area" type="text" name="_link" value="" size="30" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<?php echo $_LANG->_( 'Link Target' ); ?>:
								</td>
								<td>
									<?php echo $lists['_link_target']; ?>
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
							<?php echo $params->render( 'params', 0 );?>
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
							<?php echo $_LANG->_( 'Description' ); ?>:
							<br />
							<textarea class="inputbox" cols="40" rows="10" name="metadesc" style="width:350px; height:100px"><?php echo ampReplace( $row->metadesc); ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="left">
							<?php echo $_LANG->_( 'Keywords' ); ?>:
							<br />
							<textarea class="inputbox" cols="40" rows="5" name="metakey" style="width:350px; height:60px"><?php echo ampReplace( $row->metakey); ?></textarea>
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
							<?php echo $_LANG->_( "DESCWILLCREATELINKSTATICMENU" ); ?>
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
						mosFS::load( '@class', 'com_content' );
						mosContentFactory::buildMenuLinks( $menus );
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
		</fieldset>
		</div>

		<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
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