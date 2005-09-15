 <?php
/**
* @version $Id: admin.sections.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Sections
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
 * @subpackage Sections
 */
class sectionsScreens {
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
	function view( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& sectionsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_access', $lists['access'] );
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editSection() {
		global $mosConfig_lang;

		$tmpl =& sectionsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editSection.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Sections
*/
class sections_html {
	/**
	* Writes a list of the categories for a section
	* @param array An array of category objects
	* @param string The name of the category section
	*/
	function show( &$rows, $scope, $myid, &$pageNav, $option, $lists ) {
		global $my;
  		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="sectionsform" class="adminform">

		<?php
		sectionsScreens::view( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10">
					#
					</th>
					<th width="10">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Section Name' ), 'name' ); ?>
					</th>
					<th width="2%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'published' ); ?>
					</th>
					<th colspan="2" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th width="10%" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Access' ), 'access' ); ?>
					</th>
					<th width="2%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'id' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="14" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="14" class="center">
						<?php echo $_LANG->_( 'Display Num' ) ?>
						<?php echo  $pageNav->getLimitBox() ?>
						<?php echo $pageNav->getPagesCounter() ?>
					</td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$k = 0;
				for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
					$row = &$rows[$i];

					$link = 'index2.php?option=com_sections&amp;scope=content&amp;task=editA&amp;id='. $row->id;

					$access 	= mosAdminHTML::accessProcessing( $row, $i );
					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );
					$published 	= mosAdminHTML::publishedProcessing( $row, $i );

					$info 		= '<tr><td>'. $_LANG->_( 'Name' ) .':</td><td>'. $row->name .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Title' ) .':</td><td>'. $row->title .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Num Categories' ) .':</td><td>'. $row->categories .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Num Active' ) .':</td><td>'. $row->active .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Num Trash' ) .':</td><td>'. $row->trash .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Num Links' ) .':</td><td>'. $row->links .'</td></tr>';
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="10">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Section Information' ); ?>', BELOW, RIGHT, WIDTH, 250);" onmouseout="return nd();">
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								?>
								<span class="editlinktip">
									<?php echo $row->name; ?>
								</span>
								<?php
							} else {
								?>
								<a href="<?php echo $link; ?>" class="editlink">
									<span class="editlinktip">
										<?php echo $row->name; ?>
									</span>
								</a>
								<?php
							}
							?>
						</td>
						<td align="center">
							<?php echo $published;?>
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'ordering' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i );
							}
							?>
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'ordering' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderDownIcon( $i, $n );
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
						<?php
						$k = 1 - $k;
						?>
					</tr>
					<?php
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
		<input type="hidden" name="scope" value="<?php echo $scope;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="chosen" value="" />
		<input type="hidden" name="act" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing categories
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.  Note that the <var>section</var> property <b>must</b> be defined
	* even for a new record.
	* @param mosCategory The category object
	* @param string The html for the image list select list
	* @param string The html for the image position select list
	* @param string The html for the ordering list
	* @param string The html for the groups select list
	*/
	function edit( &$row, $option, &$lists, &$menus ) {
		global $mosConfig_live_site;
  		global $_LANG;

		if ( $row->name != '' ) {
			$name = $row->name;
		} else {
			$name = 'New Section';
		}
		if ( $row->image == '' ) {
			$row->image = 'blank.png';
		}
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
					alert( "<?php echo $_LANG->_( 'Please select a Menu' ); ?>" );
					return;
				} else if ( form.link_type.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please select a menu type' ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please enter a Name for this menu item' ); ?>" );
					return;
				}
			}

			if ( form.name.value == '' ){
				alert("<?php echo $_LANG->_( 'Section must have a name' ); ?>");
			} else if ( form.title.value == '' ){
				alert("<?php echo $_LANG->_( 'Section must have a title' ); ?>");
			} else {
				<?php getEditorContents( 'editor1', 'description' ) ; ?>
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">
		<?php
		sectionsScreens::editSection();
		?>
		<table width="100%">
		<tr>
			<td valign="top" width="60%">
				<table class="adminform">
				<tr>
					<th colspan="3">
					<?php echo $_LANG->_( 'Section Details' ); ?>
					</th>
				<tr>
				<tr>
					<td width="150">
					<?php echo $_LANG->_( 'Scope' ); ?>:
					</td>
					<td width="85%" colspan="2">
					<strong>
					<?php echo $row->scope; ?>
					</strong>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Title' ); ?>:
					</td>
					<td colspan="2">
					<input class="text_area" type="text" name="title" value="<?php echo $row->title; ?>" size="50" maxlength="50" title="<?php echo $_LANG->_( 'tipTitleField' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo (isset($row->section) ? $_LANG->_( 'Category' ) : $_LANG->_( 'Section' ));?> <?php echo $_LANG->_( 'Name' ); ?>:
					</td>
					<td colspan="2">
					<input class="text_area" type="text" name="name" value="<?php echo $row->name; ?>" size="50" maxlength="255" title="<?php echo $_LANG->_( 'tipNameField' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Image' ); ?>:
					</td>
					<td>
					<?php echo $lists['image']; ?>
					</td>
					<td rowspan="4" width="50%">
					<?php
						$path = $mosConfig_live_site . "/images/";
						if ($row->image != "blank.png") {
							$path.= "stories/";
						}
					?>
					<img src="<?php echo $path;?><?php echo $row->image;?>" name="imagelib" width="80" height="80" border="2" alt="<?php echo $_LANG->_( 'Preview' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Image Position' ); ?>:
					</td>
					<td>
					<?php echo $lists['image_position']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Ordering' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Access Level' ); ?>:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo $_LANG->_( 'Description' ); ?>:
					</td>
					<td colspan="2">
					<?php
					// parameters : areaname, content, hidden field, width, height, rows, cols
					editorArea( 'editor1',  $row->description , 'description', '100%;', '300', '60', '20', 0 ) ; ?>
					</td>
				</tr>
				</table>
			</td>
			<td valign="top">
			<?php // Link to Menu ?>
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Link to Menu' ); ?>
					</th>
				<tr>
				<tr>
					<td colspan="2">
					<?php echo $_LANG->_( 'descNewMenuItem' ); ?>
					<br /><br />
					</td>
				<tr>
				<tr>
					<td valign="top" width="100px">
					<?php echo $_LANG->_( 'Select a Menu' ); ?>
					</td>
					<td>
					<?php echo $lists['menuselect']; ?>
					</td>
				<tr>
				<tr>
					<td valign="top" width="100px">
					<?php echo $_LANG->_( 'Select Menu Type' ); ?>
					</td>
					<td>
					<?php echo $lists['link_type']; ?>
					</td>
				<tr>
				<tr>
					<td valign="top" width="100px">
					<?php echo $_LANG->_( 'Menu Item Name' ); ?>
					</td>
					<td>
					<input type="text" name="link_name" class="inputbox" value="" size="25" />
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
					mosFS::load( '@class', 'com_menus' );
					mosMenuFactory::buildLinksSecCat( $menus );
				}
				?>
				<tr>
					<td colspan="2">
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		</fieldset>
		</div>
		<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
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
	function copySectionSelect( $option, $cid, $categories, $contents, $section ) {
  		global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td align="left" valign="top" width="30%">
			<strong><?php echo $_LANG->_( 'Copy to Section' ); ?>:</strong>
			<br />
			<input class="text_area" type="text" name="title" value="" size="35" maxlength="50" title="<?php echo $_LANG->_( 'The new Section name' ); ?>" />
			<br /><br />
			</td>
			<td align="left" valign="top" width="20%">
			<strong><?php echo $_LANG->_( 'Categories being copied' ); ?>:</strong>
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
			<strong><?php echo $_LANG->_( 'Content Items being copied' ); ?>:</strong>
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
			<?php echo $_LANG->_( 'This will copy the Categories listed' ); ?>
			<br />
			<?php echo $_LANG->_( 'DESCALLITEMSWITHINCAT' ); ?>
			<br />
			<?php echo $_LANG->_( 'to the new Section created.' ); ?>
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