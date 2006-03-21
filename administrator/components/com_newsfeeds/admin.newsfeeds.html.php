<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Newsfeeds
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
* @subpackage Newsfeeds
*/
class HTML_newsfeeds {

	function showNewsFeeds( &$rows, &$lists, &$pageNav, $option ) {
		global $my, $mosConfig_cachepath;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_newsfeeds" method="post" name="adminForm">
		
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
				echo $lists['catid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">				
			<table class="adminlist">
			<tr>
				<th width="10">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="10">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML::tableOrdering( 'News Feed', 'a.name', $lists ); ?>
				</th>
				<th width="7%">
					<?php mosCommonHTML::tableOrdering( 'Published', 'a.published', $lists ); ?>
				</th>
				<th colspan="2" width="2%">
					<?php echo JText::_( 'Reorder' ); ?>
				</th>
				<th width="2%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'Order', 'a.ordering', $lists ); ?>
	 			</th>
				<th width="1%">
					<?php mosCommonHTML::saveorderButton( $rows ); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'ID', 'a.id', $lists ); ?>
				</th>
				<th class="title" width="17%">
					<?php mosCommonHTML::tableOrdering( 'Category', 'catname', $lists ); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'Num Articles', 'a.numarticles', $lists ); ?>
				</th>
				<th width="10%">
					<?php mosCommonHTML::tableOrdering( 'Cache time', 'a.cache_time', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$link 		= ampReplace( 'index2.php?option=com_newsfeeds&task=editA&hidemainmenu=1&id='. $row->id );
	
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );

				$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&section=com_newsfeeds&task=editA&hidemainmenu=1&id='. $row->catid );
				?>
				<tr class="<?php echo 'row'. $k; ?>">
					<td align="center">
						<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<?php
						if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
							?>
							<?php echo $row->name; ?>
							&nbsp;[ <i><?php echo JText::_( 'Checked Out' ); ?></i> ]
							<?php
						} else {
							?>
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Newsfeed' ); ?>">
								<?php echo $row->name; ?></a>
							<?php
						}
						?>
					</td>
					<td width="10%" align="center">
						<?php echo $published;?>
					</td>
					<td align="center">
						<?php echo $pageNav->orderUpIcon($i, ($row->catid == @$rows[$i-1]->catid) ); ?>
					</td>
					<td align="center">
						<?php echo $pageNav->orderDownIcon($i, $n, ($row->catid == @$rows[$i+1]->catid) ); ?>
					</td>
					<td align="center" colspan="2">
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
							<?php echo $row->catname;?></a>
					</td>
					<td align="center">
						<?php echo $row->numarticles;?>
					</td>
					<td align="center">
						<?php echo $row->cache_time;?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>			
			
			<?php echo $pageNav->getListFooter(); ?>		
				
			<table class="adminform">
			<tr>
				<td>
					<table align="center">
					<?php
					$visible = 0;
					// check to hide certain paths if not super admin
					if ( $my->gid == 25 ) {
						$visible = 1;
					}
					mosHTML::writableCell( $mosConfig_cachepath, 0, '<strong>Cache Directory</strong> ', $visible );
					?>
					</table>
				</td>
			</tr>
			</table>
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


	function editNewsFeed( &$row, &$lists, $option ) {
		mosMakeHtmlSafe( $row, ENT_QUOTES );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (form.name.value == '') {
				alert( "<?php echo JText::_( 'Please fill in the newsfeed name.', true ); ?>" );
			} else if (form.catid.value == 0) {
				alert( "<?php echo JText::_( 'Please select a Category.', true ); ?>" );
			} else if (form.link.value == '') {
				alert( "<?php echo JText::_( 'Please fill in the newsfeed link.', true ); ?>" );
			} else if (getSelectedValue('adminForm','catid') < 0) {
				alert( "<?php echo JText::_( 'Please select a category.', true ); ?>" );
			} else if (form.numarticles.value == "" || form.numarticles.value == 0) {
				alert( "<?php echo JText::_( 'VALIDARTICLESDISPLAY', true ); ?>" );
			} else if (form.cache_time.value == "" || form.cache_time.value == 0) {
				alert( "<?php echo JText::_( 'Please fill in the cache refresh time.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">
		
		<div id="editcell">				
			<table class="adminform">
			<tr>
				<th colspan="2">
					<?php echo JText::_( 'Details' ); ?>
				</th>
			</tr>
			<tr>
				<td width="170">
					<label for="name">
						<?php echo JText::_( 'Name' ); ?>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" size="40" name="name" id="name" value="<?php echo $row->name; ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top" align="right">
					<?php echo JText::_( 'Published' ); ?>:
				</td>
				<td>
					<?php echo $lists['published']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Category' ); ?>
					</label>
				</td>
				<td>
					<?php echo $lists['category']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="link">
						<?php echo JText::_( 'Link' ); ?>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" size="60" name="link" id="link" value="<?php echo $row->link; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="numarticles">
						<?php echo JText::_( 'Number of Articles' ); ?>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" size="2" name="numarticles" id="numarticles" value="<?php echo $row->numarticles; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="cache_time">
						<?php echo JText::_( 'Cache time (in seconds)' ); ?>
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" size="4" name="cache_time" id="cache_time" value="<?php echo $row->cache_time; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="ordering">
						<?php echo JText::_( 'Ordering' ); ?>
					</label>
				</td>
				<td>
					<?php echo $lists['ordering']; ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
	<?php
	}
}
?>