<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
* @subpackage Weblinks
*/
class HTML_weblinks {

	function showWeblinks( $option, &$rows, &$lists, &$pageNav ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_weblinks" method="post" name="adminForm">

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

		<div id="editcell">
			<table class="adminlist">
			<tr>
				<th width="5">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th class="title">
					<?php mosCommonHTML::tableOrdering( 'Title', 'a.title', $lists ); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'Published', 'a.published', $lists ); ?>
				</th>
				<th colspan="2" width="5%">
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
				<th width="25%"  class="title">
					<?php mosCommonHTML::tableOrdering( 'Category', 'category', $lists ); ?>
				</th>
				<th width="5%">
					<?php mosCommonHTML::tableOrdering( 'Hits', 'a.hits', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];

				$link 	= ampReplace( 'index2.php?option=com_weblinks&task=editA&hidemainmenu=1&id='. $row->id );

				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );

				$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&section=com_weblinks&task=editA&hidemainmenu=1&id='. $row->catid );
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
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Weblinks' ); ?>">
								<?php echo $row->title; ?></a>
							<?php
						}
						?>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td>
						<?php echo $pageNav->orderUpIcon( $i, ($row->catid == @$rows[$i-1]->catid) ); ?>
					</td>
		  			<td>
						<?php echo $pageNav->orderDownIcon( $i, $n, ($row->catid == @$rows[$i+1]->catid) ); ?>
					</td>
					<td align="center" colspan="2">
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td>
					<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
						<?php echo $row->category; ?>
					</a>
					</td>
					<td align="center">
						<?php echo $row->hits; ?>
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
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing record
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosWeblink The weblink object
	* @param array An array of select lists
	* @param object Parameters
	* @param string The option
	*/
	function editWeblink( &$row, &$lists, &$params, $option ) {
		mosMakeHtmlSafe( $row, ENT_QUOTES, 'description' );

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (form.title.value == ""){
				alert( "<?php echo JText::_( 'Weblink item must have a title', true ); ?>" );
			} else if (form.catid.value == "0"){
				alert( "<?php echo JText::_( 'You must select a category', true ); ?>" );
			} else if (form.url.value == ""){
				alert( "<?php echo JText::_( 'You must have a url.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<style type="text/css">
		table.paramlist td.paramlist_key {
			width: 92px;
			text-align: left;
			height: 30px;
		}
		</style>

		<form action="index2.php" method="post" name="adminForm" id="adminForm">

		<div id="tablecell">
			<table width="100%">
			<tr>
				<td width="45%" valign="top">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo JText::_( 'Details' ); ?>
						</th>
					</tr>
					<tr>
						<td width="100" align="right">
							<label for="title">
								<?php echo JText::_( 'Name' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $row->title;?>" />
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
						<td valign="top" align="right">
							<label for="catid">
								<?php echo JText::_( 'Category' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['catid']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
							<label for="url">
								<?php echo JText::_( 'URL' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="url" id="url" value="<?php echo $row->url; ?>" size="50" maxlength="250" />
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">
							<label for="ordering">
								<?php echo JText::_( 'Ordering' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['ordering']; ?>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php echo $params->render();?>
						</td>
					</tr>
					</table>
				</td>
				<td width="55%" valign="top">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo JText::_( 'Description' ); ?>
						</th>
					</tr>
					<tr>
						<td>
							<textarea class="text_area" cols="20" rows="9" name="description" id="description" style="width:500px"><?php echo $row->description; ?></textarea>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
