<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Sections
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Sections
*/
class sections_html
{
	/**
	* Writes a list of the categories for a section
	* @param array An array of category objects
	* @param string The name of the category section
	*/
	function show( &$rows, $scope, $myid, &$page, $option, &$lists )
	{
		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$user =& JFactory::getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 's.ordering');

		jimport('joomla.html.tooltips');
		?>
		<form action="index.php?option=com_sections&amp;scope=<?php echo $scope; ?>" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<thead>
			<tr>
				<th width="10">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="10">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
				</th>
				<th class="title">
					<?php JCommonHTML::tableOrdering( 'Title', 's.title', $lists ); ?>
				</th>
				<th width="10%">
					<?php JCommonHTML::tableOrdering( 'Published', 's.published', $lists ); ?>
				</th>
				<th width="80" nowrap="nowrap">
					<a href="javascript:tableOrdering('s.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
						<?php echo JText::_( 'Order' ); ?>
					</a>
				</th>
				<th width="1%">
					<?php JCommonHTML::saveorderButton( $rows ); ?>
				</th>
				<th width="8%">
					<?php JCommonHTML::tableOrdering( 'Access', 'groupname', $lists ); ?>
				</th>
				<th width="2%" nowrap="nowrap">
					<?php JCommonHTML::tableOrdering( 'ID', 's.id', $lists ); ?>
				</th>
				<th width="9%" nowrap="nowrap">
					<?php echo JText::_( 'Num Categories' ); ?>
				</th>
				<th width="9%" nowrap="nowrap">
					<?php echo JText::_( 'Num Active' ); ?>
				</th>
				<th width="9%" nowrap="nowrap">
					<?php echo JText::_( 'Num Trash' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<td colspan="13">
				<?php echo $page->getListFooter(); ?>
			</td>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
			$row = &$rows[$i];

			$link 		= 'index.php?option=com_sections&scope=content&task=edit&cid[]='. $row->id;

			$access 	= JCommonHTML::AccessProcessing( $row, $i );
			$checked 	= JCommonHTML::CheckedOutProcessing( $row, $i );
			$published 	= JCommonHTML::PublishedProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center">
					<?php echo $page->getRowOffset( $i ); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'Title' );?>::<?php echo $row->title; ?>">
					<?php
					if (  JTable::isCheckedOut($user->get ('id'), $row->checked_out ) ) {
						echo $row->name;
					} else {
						?>
						<a href="<?php echo JRoute::_( $link ); ?>">
							<?php echo $row->title; ?></a>
						<?php
					}
					?></span>
				</td>
				<td align="center">
					<?php echo $published;?>
				</td>
				<td class="order" colspan="2">
					<span><?php echo $page->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering ); ?></span>
					<span><?php echo $page->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
					<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
				</td>
				<td align="center">
					<?php echo $access;?>
				</td>
				<td align="center">
					<?php echo $row->id; ?>
				</td>
				<td align="center">
					<?php echo $row->categories; ?>
				</td>
				<td align="center">
					<?php echo $row->active; ?>
				</td>
				<td align="center">
					<?php echo $row->trash; ?>
				</td>
				<?php
				$k = 1 - $k;
				?>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="scope" value="<?php echo $scope;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="chosen" value="" />
		<input type="hidden" name="act" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing categories
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.  Note that the <var>section</var> property <b>must</b> be defined
	* even for a new record.
	* @param JTableCategory The category object
	* @param string The html for the image list select list
	* @param string The html for the image position select list
	* @param string The html for the ordering list
	* @param string The html for the groups select list
	*/
	function edit( &$row, $option, &$lists )
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		global $mainframe;

		$editor =& JFactory::getEditor();

		if ( $row->name != '' ) {
			$name = $row->name;
		} else {
			$name = JText::_( 'New Section' );
		}
		if ($row->image == '') {
			$row->image = 'blank.png';
		}

		jimport('joomla.filter.output');
		JOutputFilter::objectHTMLSafe( $row, ENT_QUOTES, 'description' );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == '' ) {
					alert( "<?php echo JText::_( 'Please select a Menu', true ); ?>" );
					return;
				} else if ( form.link_type.value == "" ) {
					alert( "<?php echo JText::_( 'Please select a menu type', true ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo JText::_( 'Please enter a Name for this menu item', true ); ?>" );
					return;
				}
			}

			if ( form.title.value == '' ){
				alert("<?php echo JText::_( 'Section must have a title', true ); ?>");
			} else {
				<?php
				echo $editor->save( 'description' ) ; ?>
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index.php" method="post" name="adminForm">

		<div class="col60">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
				<tr>
					<td width="100" class="key">
						<?php echo JText::_( 'Scope' ); ?>:
					</td>
					<td colspan="2">
						<strong>
						<?php echo $row->scope; ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_( 'Title' ); ?>:
						</label>
					</td>
					<td colspan="2">
						<input class="text_area" type="text" name="title" id="title" value="<?php echo $row->title; ?>" size="50" maxlength="50" title="<?php echo JText::_( 'TIPTITLEFIELD' ); ?>" />
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap" class="key">
						<label for="name">
							<?php echo JText::_( 'Alias' ); ?>:
						</label>
					</td>
					<td colspan="2">
						<input class="text_area" type="text" name="name" id="name" value="<?php echo $row->name; ?>" size="50" maxlength="255" title="<?php echo JText::_( 'TIPNAMEFIELD' ); ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_( 'Published' ); ?>:
					</td>
					<td colspan="2">
						<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="ordering">
							<?php echo JText::_( 'Ordering' ); ?>:
						</label>
					</td>
					<td colspan="2">
						<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="top" class="key">
						<label for="access">
							<?php echo JText::_( 'Access Level' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['access']; ?>
					</td>
					<td rowspan="4" width="50%">
						<?php
							$path = $mainframe->getSiteURL() . "/images/";
							if ($row->image != "blank.png") {
								$path.= "stories/";
							}
						?>
						<img src="<?php echo $path;?><?php echo $row->image;?>" name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="image">
							<?php echo JText::_( 'Image' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['image']; ?>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap" class="key">
						<label for="image_position">
							<?php echo JText::_( 'Image Position' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['image_position']; ?>
					</td>
				</tr>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
				<tr>
					<td valign="top" colspan="3">
						<?php
						// parameters : areaname, content, width, height, cols, rows
						echo $editor->display( 'description',  $row->description, '100%', '300', '60', '20', false ) ;
						?>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="scope" value="<?php echo $row->scope; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="oldtitle" value="<?php echo $row->title ; ?>" />
		</form>
		<?php
	}


	/**
	* Form to select Section to copy Category to
	*/
	function copySectionSelect( $option, $cid, $categories, $contents, $section )
	{
		?>
		<form action="index.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Copy to Section' ); ?>:</strong>
			<br />
			<input class="text_area" type="text" name="title" value="" size="35" maxlength="50" title="<?php echo JText::_( 'The new Section name' ); ?>" />
			<br /><br />
			</td>
			<td  valign="top" width="20%">
			<strong><?php echo JText::_( 'Categories being copied' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $categories as $category ) {
				echo "<li>". $category->name ."</li>";
				echo "\n <input type=\"hidden\" name=\"category[]\" value=\"$category->id\" />";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top" width="20%">
			<strong><?php echo JText::_( 'Articles being copied' ); ?>:</strong>
			<br />
			<?php
			echo "<ol>";
			foreach ( $contents as $content ) {
				echo "<li>". $content->title ."</li>";
				echo "\n <input type=\"hidden\" name=\"content[]\" value=\"$content->id\" />";
			}
			echo "</ol>";
			?>
			</td>
			<td valign="top">
			<?php echo JText::_( 'This will copy the Categories listed' ); ?>
			<br />
			<?php echo JText::_( 'DESCALLITEMSWITHINCAT' ); ?>
			<br />
			<?php echo JText::_( 'to the new Section created.' ); ?>
			</td>.
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="section" value="<?php echo $section;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="scope" value="content" />
		<?php
		foreach ( $cid as $id ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}

}
?>
