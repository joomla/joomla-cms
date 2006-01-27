<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
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
* @subpackage Weblinks
*/
class HTML_weblinks {

	function showWeblinks( $option, &$rows, &$lists, &$search, &$pageNav ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_weblinks" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<td>
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onchange="document.adminForm.submit();" />
			</td>
			<td align="right">
				<?php 
				echo $lists['catid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="5">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th class="title">
				<?php mosCommonHTML :: tableOrdering( 'Title', 'a.title', $lists ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'Published', 'a.published', $lists ); ?>
			</th>
			<th colspan="2" width="5%">
				<?php echo JText::_( 'Reorder' ); ?>
			</th>
			<th width="25%"  class="title">
				<?php mosCommonHTML :: tableOrdering( 'Category', 'cc.name', $lists ); ?>
			</th>
			<th width="5%">
				<?php mosCommonHTML :: tableOrdering( 'Hits', 'a.hits', $lists ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$link 	= ampReplace( 'index2.php?option=com_weblinks&task=editA&hidemainmenu=1&id='. $row->id );

			$task 	= $row->published ? 'unpublish' : 'publish';
			$img 	= $row->published ? 'publish_g.png' : 'publish_x.png';
			$alt 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );

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
					<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
						<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" /></a>
				</td>
				<td>
					<?php echo $pageNav->orderUpIcon( $i, ($row->catid == @$rows[$i-1]->catid) ); ?>
				</td>
	  			<td>
					<?php echo $pageNav->orderDownIcon( $i, $n, ($row->catid == @$rows[$i+1]->catid) ); ?>
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
		<form action="index2.php" method="post" name="adminForm" id="adminForm">
		<table width="100%">
		<tr>
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Details' ); ?>
					</th>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td width="80%">
					<input class="text_area" type="text" name="title" size="50" maxlength="250" value="<?php echo $row->title;?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Category' ); ?>:
					</td>
					<td>
					<?php echo $lists['catid']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'URL' ); ?>:
					</td>
					<td>
					<input class="text_area" type="text" name="url" value="<?php echo $row->url; ?>" size="50" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Description' ); ?>:
					</td>
					<td>
					<textarea class="text_area" cols="50" rows="5" name="description" style="width:500px" width="500"><?php echo $row->description; ?></textarea>
					</td>
				</tr>

				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Ordering' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
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
				</table>
			</td>
			<td width="40%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="1">
					<?php echo JText::_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php echo $params->render();?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
