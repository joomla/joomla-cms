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
	function showList( &$rows, $pageNav, $option, $lists ) {
		global $my, $acl, $database;

		mosCommonHTML::loadOverlib();
		$nullDate = $database->getNullDate();
		?>
		<form action="index2.php?option=com_frontpage" method="post" name="adminForm">
		
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
				echo $lists['sectionid'];
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
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Title', 'c.title', $lists ); ?>
				</th>
				<th width="10%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Published', 'c.state', $lists ); ?>
				</th>
				<th colspan="2" nowrap="nowrap" width="5%">
					<?php echo JText::_( 'Reorder' ); ?>
				</th>
				<th width="2%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Order', 'fpordering', $lists ); ?>
	 			</th>
				<th width="1%">
					<?php mosCommonHTML :: saveorderButton( $rows ); ?>
				</th>
				<th width="8%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Access', 'groupname', $lists ); ?>
				</th>
				<th width="2%" class="title" align="center" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'ID', 'c.id', $lists ); ?>
				</th>
				<th width="10%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'Section', 'sect_name', $lists ); ?>
				</th>
				<th width="10%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'Category', 'cc.name', $lists ); ?>
				</th>
				<th width="10%" class="title">
					<?php mosCommonHTML :: tableOrdering( 'Author', 'author', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$link 			= ampReplace( 'index2.php?option=com_content&sectionid=0&task=edit&hidemainmenu=1&id='. $row->id );
	
				$row->sect_link = ampReplace( 'index2.php?option=com_sections&task=editA&hidemainmenu=1&id='. $row->sectionid );
				$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&task=editA&hidemainmenu=1&id='. $row->catid );
	
				$now = date( 'Y-m-d H:i:s' );
				if ( $now <= $row->publish_up && $row->state == '1' ) {
					$img = 'publish_y.png';
					$alt = JText::_( 'Published' );
				} else if (($now <= $row->publish_down || $row->publish_down == $nullDate) && $row->state == '1') {
					$img = 'publish_g.png';
					$alt = JText::_( 'Published' );
				} else if ( $now > $row->publish_down && $row->state == '1' ) {
					$img = 'publish_r.png';
					$alt = JText::_( 'Expired' );
				} elseif ( $row->state == "0" ) {
					$img = "publish_x.png";
					$alt = JText::_( 'Unpublished' );
				}
	
				$times = '';
				if ( isset( $row->publish_up ) ) {
					  if ( $row->publish_up == $nullDate) {
							$times .= '<tr><td>'. JText::_( 'Start: Always' ) .'</td></tr>';
					  } else {
							$times .= '<tr><td>'. JText::_( 'Start' ) .': '. $row->publish_up .'</td></tr>';
					  }
				}
				if ( isset( $row->publish_down ) ) {
					  if ($row->publish_down == $nullDate) {
							$times .= '<tr><td>'. JText::_( 'Finish: No Expiry' ) .'</td></tr>';
					  } else {
					  $times .= '<tr><td>'. JText::_( 'Finish' ) .': '. $row->publish_down .'</td></tr>';
					  }
				}
	
				$access 	= mosCommonHTML::AccessProcessing( $row, $i );
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
	
				if ( $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype ) ) {
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
						} else {
							?>
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Content' ); ?>">
								<?php echo $row->title; ?></a>
							<?php
						}
						?>
					</td>
					<?php
					if ( $times ) {
						?>
						<td align="center">
							<a href="javascript:void(0);" onmouseover="return overlib('<table><?php echo $times; ?></table>', CAPTION, '<?php echo JText::_( 'Publish Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? 'unpublish' : 'publish' ?>')">
								<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt;?>" /></a>
						</td>
						<?php
					}
					?>
					<td>
						<?php echo $pageNav->orderUpIcon( $i ); ?>
					</td>
					<td>
						<?php echo $pageNav->orderDownIcon( $i, $n ); ?>
					</td>
					<td align="center" colspan="2">
						<input type="text" name="order[]" size="5" value="<?php echo $row->fpordering;?>" class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td align="center">
						<?php echo $row->id;?>
					</td>
					<td>
						<?php
						if ( $row->sect_name ) {
							?>
							<a href="<?php echo $row->sect_link; ?>" title="<?php echo JText::_( 'Edit Section' ); ?>">
								<?php echo $row->sect_name; ?></a>
							<?php
						} else {
							echo JText::_( 'Static' );
						}
						?>
					</td>
					<td>
						<?php
						if ( $row->name ) {
							?>
							<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
								<?php echo $row->name; ?></a>
							<?php
						} else {
							echo JText::_( 'Static' );
						}
						?>
					</td>
					<td>
						<?php echo $author; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
	
			<?php
			echo $pageNav->getListFooter();
			mosCommonHTML::ContentLegend();
			?>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}
}
?>