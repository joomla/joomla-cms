<?php
/**
* @version $Id: admin.frontpage.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * @subpackage Languages
 */
class frontpageScreens {
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
		$tmpl =& frontpageScreens::createTemplate( array( 'components.html' ) );

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
		$tmpl->addVar( 'body2', 'lists_sectionid', $lists['sectionid'] );
		$tmpl->addVar( 'body2', 'lists_catid', $lists['catid'] );
		$tmpl->addVar( 'body2', 'lists_access', $lists['access'] );


		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Content
*/
class HTML_content {
	/**
	* Writes a list of the content items
	* @param array An array of content objects
	*/
	function showList( &$rows, $search, $pageNav, $option, $lists ) {
		global $my, $mainframe;
		global $_LANG, $mosConfig_zero_date;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="frontpageform" class="adminform">

		<?php
		frontpageScreens::view( $rows, $lists, $search );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="5">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'c.title' ); ?>
					</th>
					<th width="15%" align="left" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Section' ), 'sect_name' ); ?>
					</th>
					<th width="15%" align="left" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category' ), 'cc.name' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'c.state' ); ?>
					</th>
					<th colspan="2" nowrap="nowrap" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'f.ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th width="8%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Access' ), 'c.access' ); ?>
					</th>
					<th width="1%" align="left" nowrap="nowrap">
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

					if ( $row->sect_name ) {
						$title		= $_LANG->_( 'Edit Content' );
						$overlibT	= $_LANG->_( 'Content Information' );
						$link 		= 'index2.php?option=com_content&amp;sectionid=0&amp;task=edit&amp;id='. $row->id;
					} else {
						$title		= $_LANG->_( 'Edit Static Content' );
						$overlibT	= $_LANG->_( 'Static Content Information' );
						$link 		= 'index2.php?option=com_typedcontent&amp;task=edit&amp;id='. $row->id;
					}

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
						$img = "publish_x.png";
						$alt = $_LANG->_( 'Unpublished' );
					}

					$times = '';
					if ( isset( $row->publish_up ) ) {
						  if ( $row->publish_up == $mosConfig_zero_date ) {
								$times .= '<tr><td>'. $_LANG->_( 'Start: Always' ) .'</td></tr>';
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

					$access 	= mosAdminHTML::accessProcessing( $row, $i );
					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );

					if ( $row->created_by_alias ) {
						$author = $row->created_by_alias;
					} else {
						$author = $row->author;
					}

					if ( $row->sect_name ) {
						$row->sect_link = 'index2.php?option=com_sections&amp;task=editA&amp;id='. $row->sectionid;
						$row->sect		= '<a href="'. $row->sect_link.'" title="'. $_LANG->_( 'Edit Section' ) .'" class="editlink">'. $row->sect_name .'</a>';
					} else {
						$row->sect 		= $_LANG->_( 'Static Content' );
					}

					if ( $row->name ) {
						$row->cat_link 	= 'index2.php?option=com_categories&amp;task=editA&amp;id='. $row->catid;
						$row->cat		= '<a href="'. $row->cat_link.'" title="'. $_LANG->_( 'Edit Category' ) .'" class="editlink">'. $row->name .'</a>';
					} else {
						$row->cat 		= '';
					}
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
						<td width="10">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $overlibT; ?>', BELOW, RIGHT);" onmouseout="return nd();">
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
						<td>
							<?php echo $row->sect; ?>
						</td>
						<td>
							<?php echo $row->cat; ?>
						</td>
						<?php
						if ( $times ) {
							?>
							<td align="center" onmouseover="return overlib('<table><?php echo $times; ?></table>', CAPTION, 'Publish Information', BELOW, RIGHT);" onMouseOut="return nd();" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? "unpublish" : "publish";?>')">
								<a href="javascript:void(0);">
									<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt;?>" />
								</a>
							</td>
							<?php
						}
						?>
						<td>
							<?php
							if ( ( $lists['tOrder'] == 'f.ordering' ) && ( $lists['tOrderDir'] == 'DESC' ) ) {
								echo $pageNav->orderUpIcon( $i );
							}
							?>
						</td>
						<td>
							<?php
							if ( ( $lists['tOrder'] == 'f.ordering' ) && ( $lists['tOrderDir'] == 'DESC' ) ) {
								echo $pageNav->orderDownIcon( $i, $n );
							}
							?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->fpordering;?>" class="text_area" style="text-align: center" />
						</td>
						<td align="center">
							<?php echo $access; ?>
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
}
?>