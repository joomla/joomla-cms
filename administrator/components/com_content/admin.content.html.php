<?php
/**
* @version $Id: admin.content.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
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
 * @package Mambo
 * @subpackage Languages
 */
class contentScreens {
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
	function view( &$rows, $section, &$lists, $search, $pageNav, $all=NULL ) {
	   $tmpl =& contentScreens::createTemplate( array('components.html' ) );

	   $tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addObject( 'sections-list', $lists['sections'], 'row_' );
		$tmpl->addObject( 'categories-list', $lists['categories'], 'row_' );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );
		$tmpl->addObject( 'body2', $section, 'section_' );
		$tmpl->addVar( 'body2', 'search', $search );

		$tmpl->addVar( 'body2', 'all', intval( $all ) );

		if ( $lists['trash'] ) {
			$trash = '&nbsp<small>[';
			$trash .= $lists['trash'];
			$trash .= ']</small>';
		} else {
			$trash = '';
		}
		$tmpl->addVar( 'trash', 'trash', $trash );

		// setup the page navigation footer
		$pageNav->setTemplateVars( $tmpl, 'body2' );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_access', $lists['access'] );
		$tmpl->addVar( 'body2', 'lists_authorid', $lists['authorid'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	/**
	* List languages
	* @param array
	*/
	function viewArchive( &$rows, $section, &$lists, $search, $pageNav, $all=NULL ) {
	   $tmpl =& contentScreens::createTemplate( array( 'components.html' ) );

		$tmpl->readTemplatesFromInput( 'viewArchive.html' );

		$tmpl->addObject( 'sections-list', $lists['sections'], 'row_' );
		$tmpl->addObject( 'categories-list', $lists['categories'], 'row_' );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );
		$tmpl->addObject( 'body2', $section, 'section_' );
		$tmpl->addVar( 'body2', 'search', $search );

		$tmpl->addVar( 'body2', 'all', intval( $all ) );

		if ( $lists['trash'] ) {
			$trash = '&nbsp<small>[';
			$trash .= $lists['trash'];
			$trash .= ']</small>';
		} else {
			$trash = '';
		}
		$tmpl->addVar( 'trash', 'trash', $trash );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_sectionid', $lists['sectionid'] );
		$tmpl->addVar( 'body2', 'lists_catid', $lists['catid'] );
		$tmpl->addVar( 'body2', 'lists_authorid', $lists['authorid'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	/**
	* List languages
	* @param array
	*/
	function trashView($lists) {
		$tmpl =& contentScreens::createTemplate( array( 'components.html' ));

		$tmpl->readTemplatesFromInput( 'viewTrash.html' );

		$tmpl->addObject( 'sections-list', $lists['sections'], 'row_' );
		$tmpl->addObject( 'categories-list', $lists['categories'], 'row_' );

		//$tmpl->addVar( 'body2', 'client', $lists['client'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Mambo
* @subpackage Content
*/
class HTML_content {

	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showContent( &$rows, $section, &$lists, $search, $pageNav, $all=NULL, $redirect ) {
		global $my, $mainframe;
	   global $_LANG, $mosConfig_zero_date;

	   $sec_test = ( $lists['tOrder'] == 's.title' ) && $all;
	   $cat_test = ( $lists['tOrder'] == 'cc.name' ) && !$all;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="contentform" class="adminform">

		<?php
		contentScreens::view( $rows, $section, $lists, $search, $pageNav, $all );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="1%">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="1%">
						<input type="checkbox" class="selector" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'c.title' ); ?>
					</th>
					<?php
					if ( $all ) {
						?>
						<th width="10%" align="left" nowrap="nowrap">
							<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Section' ), 's.title' ); ?>
						</th>
						<?php
					}
					?>
					<th width="10%" align="left" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category' ), 'cc.name' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'c.state' ); ?>
					</th>
					<th nowrap="nowrap" width="5%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Front Page' ), 'frontpage' ); ?>
					</th>
					<th colspan="2" align="center" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'c.ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th width="7%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Access' ), 'c.access' ); ?>
					</th>
					<th width="2%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'c.id' ); ?>
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

					$row->sect_link = 'index2.php?option=com_sections&amp;task=editA&amp;id='. $row->sectionid;
					$row->cat_link 	= 'index2.php?option=com_categories&amp;task=editA&amp;id='. $row->catid;

					// published icon
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
					} elseif ( $row->state == '0' ) {
						$img = 'publish_x.png';
						$alt = $_LANG->_( 'Unpublished' );
					}
					$times = '';
					if ( isset( $row->publish_up ) ) {
						$date_time = mosFormatDate( $row->publish_up, $_LANG->_( 'DATE_FORMAT_LC2' ) );
						if ( $row->publish_up == $mosConfig_zero_date ) {
							$times .= '<tr><td>'. $_LANG->_( 'Start: Always' ) .'</td></tr>';
						} else {
							$times .= '<tr><td>'. $_LANG->_( 'Start' ) .': '. $date_time .'</td></tr>';
						}
					}
					if ( isset( $row->publish_down ) ) {
						$date_time = mosFormatDate( $row->publish_down, $_LANG->_( 'DATE_FORMAT_LC2' ) );
						if ( $row->publish_down == $mosConfig_zero_date ) {
							$times .= '<tr><td>'. $_LANG->_( 'Finish: No Expiry' ) .'</td></tr>';
						} else {
							$times .= '<tr><td>'. $_LANG->_( 'Finish' ) .': '. $date_time .'</td></tr>';
						}
					}

					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$author = $row->author;
					}

					$access 	= mosAdminHTML::accessProcessing( $row, $i );
					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );

					$title			= htmlspecialchars( $row->title, ENT_QUOTES );
					$title_alias	= htmlspecialchars( $row->title_alias, ENT_QUOTES );

					$Cdate 		= mosFormatDate( $row->created, $_LANG->_( 'DATE_FORMAT_LC3') );
					$Ctime 		= mosFormatDate( $row->created, '%H:%M' );
					$Mdate 		= mosFormatDate( $row->modified, $_LANG->_( 'DATE_FORMAT_LC3') );
					$Mtime 		= mosFormatDate( $row->modified, '%H:%M' );
					$info 		= '<tr><td>'. $_LANG->_( 'Title Alias' ) .':</td><td>'. $title_alias .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Author' ) .':</td><td>'. $author .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Created' ) .':</td><td>'. $Cdate .'</td></tr>';
					$info 		.= '<tr><td></td><td>'. $Ctime .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Modified' ) .':</td><td>'. $Mdate .'</td></tr>';
					$info 		.= '<tr><td></td><td>'. $Mtime .'</td></tr>';
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="1%">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td width="1%" align="left">
							<?php echo $checked; ?>
						</td>
						<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Content Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();" >
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								?>
								<span class="editlinktip">
									<?php echo $title; ?>
								</span>
								<?php
							} else {
								?>
								<a href="#" onclick="goDoTask(this, 'submit-edit', 'task=editA,id=cb<?php echo $i ?>,hide=1')" class="editlink">
									<span class="editlinktip">
										<?php echo $title; ?>
									</span>
								</a>
								<?php
							}
							?>
						</td>
						<?php
						if ( $all ) {
							?>
							<td align="left" nowrap="nowrap">
								<a href="<?php echo $row->sect_link; ?>" title="<?php echo $_LANG->_( 'Edit Section' ); ?>" class="editlink">
									<?php echo $row->section_name; ?>
								</a>
							</td>
							<?php
						}
						?>
						<td align="left" nowrap="nowrap">
							<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>" class="editlink">
								<?php echo $row->name; ?>
							</a>
						</td>
						<?php
						if ( $times ) {
							?>
							<td align="center">
								<a href="#" onMouseOver="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Publish Information' ); ?>', BELOW, RIGHT);" onMouseOut="return nd();" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? 'unpublish' : 'publish';?>')">
									<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>"/>
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
						<td align="right">
							<?php
							if ( ( $sec_test || $cat_test ) && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i, ($row->catid == @$rows[$i-1]->catid) );
							}
							?>
						</td>
						<td align="left">
							<?php
							if ( ( $sec_test || $cat_test ) && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderDownIcon( $i, $n, ($row->catid == @$rows[$i+1]->catid) );
							}
							?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<td align="center">
							<?php echo $access;?>
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
			</fieldset>

		<?php
		mosFS::load( '@class', 'com_content' );
		mosContentFactory::buildContentLegend();
		?>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="sectionid" value="<?php echo $section->id;?>" />
		<input type="hidden" name="task" value="" />
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

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			if (pressbutton == 'remove') {
				if (document.adminForm.boxchecked.value == 0) {
					alert('<?php echo $_LANG->_( 'VALIDSELECTIONLISTSENDTRASH', true ); ?>');
				} else if ( confirm('<?php echo $_LANG->_( 'VALIDTRASHSELECTEDITEMS', true ); ?>')) {
					submitform('remove');
				}
			} else {
				submitform(pressbutton);
			}
		}

		function tableOrdering_alt( order, dir ) {
			var form = document.adminForm;

			form.tOrder.value 		= order;
			form.tOrderDir.value 	= dir;
			submitform( 'showarchive' );
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm" id="contentform" class="adminform">

		<?php
		contentScreens::viewArchive( $rows, $section, $lists, $search, $pageNav, $all );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="5">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'c.title' ); ?>
					</th>
					<?php
					if ( $all ) {
						?>
						<th width="15%" align="left" nowrap="nowrap">
							<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Section' ), 's.title' ); ?>
						</th>
						<?php
					}
					?>
					<th align="left" nowrap="nowrap" width="15%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category' ), 'cc.name' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'c.ordering' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'c.id' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="7" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="7" class="center">
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

					$row->sect_link = 'index2.php?option=com_sections&amp;task=editA&amp;&amp;id='. $row->sectionid;
					$row->cat_link 	= 'index2.php?option=com_categories&task=editA&id='. $row->catid;

					if ( $acl->acl_check( 'administration', 'manage', 'users', $my->usertype, 'components', 'com_users' ) ) {
						if ( $row->created_by_alias ) {
							$author = $row->created_by_alias;
						} else {
							$linkA 	= 'index2.php?option=com_users&task=editA&id='. $row->created_by;
							$author = '<a href="'. $linkA .'" title="'. $_LANG->_( 'Edit User' ) .'">'. $row->author .'</a>';
						}
					} else {
						if ( $row->created_by_alias ) {
							$author = $row->created_by_alias;
						} else {
							$author = $row->author;
						}
					}

					$title	= htmlspecialchars( $row->title, ENT_QUOTES );

					$Cdate 	= mosFormatDate( $row->created, $_LANG->_( 'DATE_FORMAT_LC3') );
					$Ctime 	= mosFormatDate( $row->created, '%H:%M' );
					$Mdate 	= mosFormatDate( $row->modified, $_LANG->_( 'DATE_FORMAT_LC3') );
					$Mtime 	= mosFormatDate( $row->modified, '%H:%M' );
					$info 	= '<tr><td>'. $_LANG->_( 'Author' ) .':</td><td>'. $author .'</td></tr>';
					$info 	.= '<tr><td>'. $_LANG->_( 'Created' ) .':</td><td>'. $Cdate .'</td></tr>';
					$info 	.= '<tr><td></td><td>'. $Ctime .'</td></tr>';
					$info 	.= '<tr><td>'. $_LANG->_( 'Modified' ) .':</td><td>'. $Mdate .'</td></tr>';
					$info 	.= '<tr><td></td><td>'. $Mtime .'</td></tr>';
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td width="20">
							<?php echo mosHTML::idBox( $i, $row->id ); ?>
						</td>
						<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Content Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
							<?php echo $title; ?>
						</td>
						<?php
						if ( $all ) {
							?>
							<td align="left">
								<a href="<?php echo $row->sect_link; ?>" title="<?php echo $_LANG->_( 'Edit Section' ); ?>" class="editlink">
									<?php echo $row->sect_name; ?>
								</a>
							</td>
							<?php
						}
						?>
						<td>
							<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>" class="editlink">
								<?php echo $row->name; ?>
							</a>
						</td>
						<td align="center">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<td>
							<?php echo $row->id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
				</table>
			</fieldset>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $section->id;?>" />
		<input type="hidden" name="task" value="showarchive" />
		<input type="hidden" name="returntask" value="showarchive" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		</form>
		<?php
	}

	/**
	* Writes a list of the Trash items
	*/
	function trashShow( $rows, $lists, $pageNav, $option ) {
		global $my;
  		global $_LANG;

		?>
		<form action="index2.php" method="post" name="adminForm" id="trashform" class="adminform">

		<?php
		contentScreens::trashView($lists);
		?>

			<table class="adminlist" id="moslist" width="90%">
				<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo $_LANG->_( 'Title' ); ?>
					</th>
					<th width="20%">
						<?php echo $_LANG->_( 'Section' ); ?>
					</th>
					<th width="20%">
						<?php echo $_LANG->_( 'Category' ); ?>
					</th>
					<th width="20">
						<?php echo $_LANG->_( 'ID' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="8" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="8" class="center">
						<?php echo $_LANG->_( 'Display Num' ) ?>
						<?php echo  $pageNav->getLimitBox() ?>
						<?php echo $pageNav->getPagesCounter() ?>
					</td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$k = 0;
				$i = 0;
				foreach ( $rows as $row ) {
					?>
					<tr class="<?php echo "row". $k; ?>">
						<td align="center">
							<?php echo $i + 1 + $pageNav->limitstart;?>
						</td>
						<td align="center">
							<?php echo mosHTML::idBox( $i, $row->id ); ?>
						</td>
						<td nowrap="nowrap">
							<?php echo $row->title; ?>
						</td>
						<td>
							<?php echo $row->sectname; ?>
						</td>
						<td>
							<?php echo $row->catname; ?>
						</td>
						<td>
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
			</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}


	/**
	* A delete confirmation page
	* Writes list of the items that have been selected for deletion
	*/
	function trashDelete( $option, $cid, $items, $type ) {
	  	global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="20%">
				<strong>
					<?php echo $_LANG->_( 'Number of Items' ); ?>:
				</strong>
				<br />
				<font color="#000066">
					<strong>
						<?php echo count( $cid ); ?>
					</strong>
				</font>
				<br /><br />
			</td>
			<td align="left" valign="top" width="25%">
				<strong>
					<?php echo $_LANG->_( 'Items being Deleted' ); ?>:
				</strong>
				<br />
				<ol>
					<?php
					foreach ( $items as $item ) {
						echo '<li>'. $item->name .'</li>';
					}
					?>
				</ol>
			</td>
			 <td valign="top">
				<?php echo $_LANG->_( '* This will' ); ?>
				<strong>
					<font color="#FF0000">
						<?php echo $_LANG->_( 'Permanently Delete' ); ?>
					</font>
				</strong>
				<br />
				<?php echo $_LANG->_( 'these Items from the Database *' ); ?>
				<br /><br /><br />
				<div style="border: 1px dotted gray; width: 70px; padding: 10px; margin-left: 50px;">
					<a class="toolbar" href="javascript:if (confirm('<?php echo $_LANG->_( 'WARNWANTDELLISTEDITEMS' ); ?>')){ submitbutton('trashdelete');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('remove','','images/delete_f2.png',1);">
						<img name="remove" src="images/delete.png" alt="<?php echo $_LANG->_( 'Delete' ); ?>" border="0" align="middle" />
						<?php echo $_LANG->_( 'Delete' ); ?>
					</a>
				</div>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}


	/**
	* A restore confirmation page
	* Writes list of the items that have been selected for restore
	*/
	function trashRestore( $option, $cid, $items, $type ) {
	  	global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="20%">
				<strong>
					<?php echo $_LANG->_( 'Number of Items' ); ?>:
				</strong>
				<br />
				<font color="#000066">
					<strong>
						<?php echo count( $cid ); ?>
					</strong>
				</font>
				<br /><br />
			</td>
			<td align="left" valign="top" width="25%">
				<strong>
					<?php echo $_LANG->_( 'Items being Restored' ); ?>:
				</strong>
				<br />
				<ol>
					<?php
					foreach ( $items as $item ) {
						echo '<li>'. $item->name .'</li>';
					}
					?>
				</ol>
			</td>
			 <td valign="top">
				<?php echo $_LANG->_( '* This will' ); ?>
				<strong>
					<font color="#FF0000">
						<?php echo $_LANG->_( 'Restore' ); ?>
					</font>
				</strong>
				<?php echo $_LANG->_( 'these Items,' ); ?>
				<br />
				<?php echo $_LANG->_( 'TIPWILLBERETURNEDPLACESUNPUBLISHEDITEMS' ); ?>
				<br /><br /><br />
				<div style="border: 1px dotted gray; width: 80px; padding: 10px; margin-left: 50px;">
					<a class="toolbar" href="javascript:if (confirm('Are you sure you want to Restore the listed items?.')){ submitbutton('trashrestore');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('restore','','images/restore_f2.png',1);">
						<img name="restore" src="images/restore.png" alt="<?php echo $_LANG->_( 'Restore' ); ?>" border="0" align="middle" />
						<?php echo $_LANG->_( 'Restore' ); ?>
					</a>
				</div>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sectionid; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
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

		$tabs = new mosTabs(0);

		$title = ( $row->id ? $_LANG->_( 'Edit' ) : $_LANG->_( 'New' ) );

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
				echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->value )."','".addslashes( $v->text )."' );\n\t\t";
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
				alert( "<?php echo $_LANG->_( 'You must select a Section' ); ?>" );
			} else if (form.catid.value == "-1"){
				alert( "<?php echo $_LANG->_( 'You must select a Category' ); ?>" );
 			} else if (form.catid.value == ""){
 				alert( "<?php echo $_LANG->_( 'You must select a Category' ); ?>" );
			} else {
				<?php getEditorContents( 'editor1', 'introtext' ) ; ?>
				<?php getEditorContents( 'editor2', 'fulltext' ) ; ?>
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
<div id="datacellfull">
		<fieldset>
			<legend>
				<?php echo $_LANG->_( 'Content' ); ?>
			</legend>
		<table width="100%">
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
						</tr>
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
						<br />
						<?php
						// parameters : areaname, content, hidden field, width, height, rows, cols
						editorArea( 'editor1',  $row->introtext , 'introtext', '100%;', '200', '75', '20' );
						?>
					</td>
				</tr>
				<tr>
					<td width="100%">
						<?php echo $_LANG->_( 'Main Text: (optional)' ); ?>
						<br />
						<?php
						// parameters : areaname, content, hidden field, width, height, rows, cols
						editorArea( 'editor2',  $row->fulltext , 'fulltext', '100%;', '350', '75', '30' );
						?>
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
						</tr>
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
								<input type="text" name="created_by_alias" size="30" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="text_area" />
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
								<?php echo $_LANG->_( 'Ordering' ); ?>:
							</td>
							<td>
								<?php echo $lists['ordering']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Override Created Date' ); ?>
							</td>
							<td>
								<input class="text_area" type="text" name="created" id="created" size="25" maxlength="19" value="<?php echo $row->created; ?>" />
								<input name="reset" type="reset" class="button" onclick="return showCalendar('created', 'y-mm-dd');" value="..." />
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Start Publishing' ); ?>:
							</td>
							<td>
								<input class="text_area" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
								<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');" />
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Finish Publishing' ); ?>:
							</td>
							<td>
								<input class="text_area" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
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
									<input name="reset_hits" type="button" class="button" value="<?php echo $_LANG->_( 'Reset Hit Count' ); ?>" onclick="submitbutton('resethits');" />
								</div>
							</td>
						</tr>
						<tr>
							<td valign="top" align="right">
								<?php echo $_LANG->_( 'Revised' ); ?>:
							</td>
							<td>
								<?php echo $row->version;?> times
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
									<?php echo $create_date; ?>
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
								<?php echo $mod_date; ?>
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
										<label for="_width"  onmouseover="return overlib('<?php echo $_LANG->_( 'TIPSMOSIMAGEWIDTH' ); ?>');" onmouseout="return nd();">
											<span class="editlinktip">
												<?php echo $_LANG->_( 'Caption Width' ); ?>:
											</span>
										</label>
									</td>
									<td>
										<input class="text_area" type="text" id="_widh" name="_width" value="" size="5" maxlength="5" />
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
										<input class="button" type="button" value="<?php echo $_LANG->_( 'Apply' ); ?>" onclick="applyImageProps()" />
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
						</tr>
						<tr>
							<td>
								<?php echo $_LANG->_( 'DESCPARAMCONTROLWHATSEEWHENCLICKTOVIEWITEM' ); ?>
								<br /><br />
							</td>
						</tr>
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
						</tr>
						<tr>
							<td>
								<?php echo $_LANG->_( 'Description' ); ?>:
								<br />
								<textarea class="text_area" cols="40" rows="10" style="width:350px; height:100px" name="metadesc"><?php echo ampReplace( $row->metadesc); ?></textarea>
							</td>
						</tr>
							<tr>
							<td>
								<?php echo $_LANG->_( 'Keywords' ); ?>:
								<br />
								<textarea class="text_area" cols="40" rows="5" style="width:350px; height:60px" name="metakey"><?php echo ampReplace( $row->metakey); ?></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" class="button" value="<?php echo $_LANG->_( 'Add Sect/Cat/Title' ); ?>" onclick="f=document.adminForm;f.metakey.value=document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].text+', '+getSelectedText('adminForm','catid')+', '+f.title.value+f.metakey.value;" />
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
						</tr>
						<tr>
							<td colspan="2">
								<?php echo $_LANG->_( "DescWillCreateLinkInMenu" ); ?>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td valign="top" width="90">
								<?php echo $_LANG->_( 'Select a Menu' ); ?>
							</td>
							<td>
								<?php echo $lists['menuselect']; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="90">
								<?php echo $_LANG->_( 'Menu Item Name' ); ?>
							</td>
							<td>
								<input type="text" name="link_name" class="inputbox" value="" size="30" />
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<input name="menu_link" type="button" class="button" value="<?php echo $_LANG->_( 'Link to Menu' ); ?>" onclick="submitbutton('menulink');" />
							</td>
						</tr>
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
			</td>
		</tr>
		</table>
		</fieldset>
		</div>

		<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $row->version; ?>" />
		<input type="hidden" name="mask" value="0" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="images" value="" />
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
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (!getSelectedValue( 'adminForm', 'sectcat' )) {
				alert( "<?php echo $_LANG->_( 'Please select something', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td align="left" valign="top" width="40%">
				<strong>
					<?php echo $_LANG->_( 'Move to Section/Category' ); ?>:
				</strong>
				<br />
				<?php echo $sectCatList; ?>
				<br /><br />
			</td>
			<td align="left" valign="top">
				<strong>
					<?php echo $_LANG->_( 'Items being Moved' ); ?>:
				</strong>
				<br />
				<ol>
					<?php
					foreach ( $items as $item ) {
						echo "<li>". $item->title ."</li>";
					}
					?>
				</ol>
			</td>
		</tr>
		</table>
		<br /><br />

		<?php
		foreach ($cid as $id) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sectionid; ?>" />
		<input type="hidden" name="task" value="" />
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
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (!getSelectedValue( 'adminForm', 'sectcat' )) {
				alert( "<?php echo $_LANG->_( 'VALIDSELECTSECTCATCOPYITEMS', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td align="left" valign="top" width="40%">
				<strong>
					<?php echo $_LANG->_( 'Copy to Section/Category' ); ?>:
				</strong>
				<br />
				<?php echo $sectCatList; ?>
				<br /><br />
			</td>
			<td align="left" valign="top">
				<strong>
					<?php echo $_LANG->_( 'Items being copied' ); ?>:
				</strong>
				<br />
				<ol>
					<?php
					foreach ( $items as $item ) {
						echo "<li>". $item->title ."</li>";
					}
					?>
				</ol>
			</td>
		</tr>
		</table>
		<br /><br />

		<?php
		foreach ( $cid as $id ) {
			echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sectionid; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function popupPreview( ) {
		global $database, $_MAMBOTS;
		global $mosConfig_live_site;
	   global $_LANG;

		$id 	= mosGetParam( $_REQUEST, 'id', '' );

		// load site template
		$sql = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = '0'"
		. "\n AND menuid = '0'";
		$database->setQuery( $sql );
		$template = $database->loadResult();

		if ( $id ) {
			$query = "SELECT *"
			. "\n FROM #__content"
			. "\n WHERE id = '$id'"
			;
			$database->setQuery( $query );
			$rows = $database->loadObjectList();

			$row = $rows[0];

			$row->text = '<fieldset><legend>'. $_LANG->_( 'Intro Text' ) .'</legend>';
			$row->text .= $row->introtext;
			$row->text .= '</fieldset>';

			if ( $row->fulltext ) {
				$row->text .= '<fieldset><legend>'. $_LANG->_( 'Full Text' ) .'</legend>';
				$row->text .= $row->fulltext;
				$row->text .= '</fieldset>';
			}

		 	$params = new mosParameters( '' );
		 	$params->set( 'image', 1 );
		 	$params->set( 'introtext', 1 );
		 	$params->set( 'popup', 1 );

			// process the new bots
			$_MAMBOTS->loadBotGroup( 'content' );
			$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$row, &$params ), true );

			// xml prolog
			echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
			?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
			<title><?php echo $_LANG->_( 'Content Preview' ); ?></title>
			<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/templates/<?php echo $template; ?>/css/template_css<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" type="text/css" />
			</head>
			<body>
				<table class="contentpaneopen">
				<tr>
				    <td class="contentheading" colspan="2">
				    	<?php echo $row->title; ?>
				    </td>
				</tr>
				<?php
				$results = $_MAMBOTS->trigger( 'onBeforeDisplayContent', array( &$row, &$params ) );
				echo trim( implode( "\n", $results ) );
				?>
				<tr>
			    	<td colspan="2">
			 			<?php echo $row->text; ?>
					</td>
				</tr>
				<tr>
				    <td align="center">
					    <a href="#" onClick="window.close()">
						    <?php echo $_LANG->_( 'Close' ); ?>
						</a>
				    </td>
					<td align="left">
						<a href="javascript:;" onClick="window.print(); return false">
							<?php echo $_LANG->_( 'Print' ); ?>
						</a>
					</td>
				</tr>
				</table>

				<?php
				$results = $_MAMBOTS->trigger( 'onAfterDisplayContent', array( &$row, &$params ) );
				echo trim( implode( "\n", $results ) );
				?>
			</body>
			</html>
			<?php
		} else {
			// xml prolog
			echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
			?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
			<title><?php echo $_LANG->_( 'Content Preview' ); ?></title>
			<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/templates/<?php echo $template;?>/css/template_css<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" type="text/css" />
			<script language="javascript" type="text/javascript">
			<!--
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

				temp[i] = '<img src="<?php echo $mosConfig_live_site; ?>/images/stories/' + parts[0] + '" align="' + parts[1] + '" border="' + parts[3] + '" alt="' + parts[2] + '" hspace="6" />';
			}

			var temp2 = alltext.split( '{mosimage}' );

			var alltext = temp2[0];

			for (var i=0, n=temp2.length-1; i < n; i++) {
				alltext += temp[i] + temp2[i+1];
			}
			//-->
			</script>
			</head>
			<body style="background-color:#FFFFFF">
				<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
				<tr>
					<td class="contentheading" colspan="2">
						<script language="javascript" type="text/javascript">
						<!--
						document.write( title );
						//-->
						</script>
					</td>
				</tr>
				<tr>
					<td valign="top" height="90%" colspan="2">
						<script language="javascript" type="text/javascript">
						<!--
						document.write( alltext );
						//-->
						</script>
					</td>
				</tr>
				<tr>
					<td align="right">
						<a href="#" onClick="window.close()">
							<?php echo $_LANG->_( 'Close' ); ?>
						</a>
					</td>
					<td align="left">
						<a href="javascript:;" onClick="window.print(); return false">
							<?php echo $_LANG->_( 'Print' ); ?>
						</a>
					</td>
				</tr>
				</table>
			</body>
			</html>
			<?php
		}
	}
}
?>