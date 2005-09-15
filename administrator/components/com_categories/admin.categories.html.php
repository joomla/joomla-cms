<?php
/**
* @version $Id: admin.categories.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Categories
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
 * @subpackage Categories
 */
class categoriesScreens {
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
	function view( &$lists, $section, $sectionbacklink ) {
		global $mosConfig_lang;

		$tmpl =& categoriesScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'section', $section );

		// Backlink from components using category table
		$tmpl->addVar( 'body2', 'backlink', $sectionbacklink );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );
		$tmpl->addVar( 'body2', 'title', $lists['title'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_section', $lists['sectionid'] );
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_access', $lists['access'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editCategory() {
		global $mosConfig_lang;

		$tmpl =& categoriesScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editCategory.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Categories
*/
class categories_html {

	/**
	* Writes a list of the categories for a section
	* @param array An array of category objects
	* @param string The name of the category section
	*/
	function show( &$rows, $section, &$pageNav, &$lists, $type, $sectionbacklink ) {
		global $my, $mosConfig_live_site;
	   global $_LANG;

	   $sec_test = ( $lists['tOrder'] == 'c.section' ) && ( $section == 'content' );
	   $cat_test = ( $lists['tOrder'] == 'c.ordering' ) && ( $section <> 'content' );

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="categoriesform" class="adminform">

		<?php
		categoriesScreens::view( $lists, $section, $sectionbacklink );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10" align="left">
						#
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category Name' ), 'c.name' ); ?>
					</th>
					<?php
					if ( $section == 'content') {
						?>
						<th width="18%" align="left">
							<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Section' ), 'c.section' ); ?>
						</th>
						<?php
					}
					?>
					<th width="5%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'c.published' ); ?>
					</th>
					<th colspan="2" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
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
					<th width="5%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'c.id' ); ?>
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
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row 	= &$rows[$i];

					$row->sect_link = 'index2.php?option=com_sections&amp;task=editA&amp;id='. $row->section;

					$link = 'index2.php?option=com_categories&amp;section='. $section .'&amp;task=editA&amp;id='. $row->id;

					$access 	= mosAdminHTML::accessProcessing( $row, $i );
					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );
					$published 	= mosAdminHTML::publishedProcessing( $row, $i );

					$info 		= '<tr><td>'. $_LANG->_( 'Name' ) .':</td><td>'. $row->name .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Title' ) .':</td><td>'. $row->title .'</td></tr>';
					$info 		.= '<tr><td>'. $_LANG->_( 'Num Active' ) .':</td><td>'. $row->active .'</td></tr>';
					if ( $type == 'content') {
						$info 		.= '<tr><td>'. $_LANG->_( 'Num Trash' ) .':</td><td>'. $row->trash .'</td></tr>';
					}
					$info 		.= '<tr><td>'. $_LANG->_( 'Num Links' ) .':</td><td>'. $row->links .'</td></tr>';
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
							<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $_LANG->_( 'Category Information' ); ?>', BELOW, RIGHT, WIDTH, 250);" onmouseout="return nd();">
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
						<?php
						if ( $section == 'content' ) {
							?>
							<td align="left">
								<a href="<?php echo $row->sect_link; ?>" title="<?php echo $_LANG->_( 'Edit Section' ); ?>" class="editlink">
									<?php echo $row->section_name; ?>
								</a>
							</td>
							<?php
						}
						?>
						<td align="center">
							<?php echo $published;?>
						</td>
						<td>
							<?php
							if ( ( $sec_test || $cat_test ) && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i );
							}
							?>
						</td>
						<td>
							<?php
							if ( ( $sec_test || $cat_test ) && ( $lists['tOrderDir'] == 'DESC' )  ) {
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
		<input type="hidden" name="option" value="com_categories" />
		<input type="hidden" name="section" value="<?php echo $section;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="chosen" value="" />
		<input type="hidden" name="act" value="" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing categories
	* @param mosCategory The category object
	* @param string
	* @param array
	*/
	function edit( &$row, &$lists, $redirect, $menus ) {
		global $_LANG;

	   if ( $row->image == '' ) {
			$row->image = 'blank.png';
		}

		if ( $redirect == 'content' ) {
			$component = 'Content';
		} else {
			$component = ucfirst( substr( $redirect, 4 ) );
			if ( $redirect == 'com_contact_details' ) {
				$component = 'Contact';
			}
		}
		mosMakeHtmlSafe( $row, ENT_QUOTES, 'description' );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton, section) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			if ( pressbutton == 'menulink' ) {
				if ( form.menuselect.value == '' ) {
					alert( "<?php echo $_LANG->_( 'Please select a Menu' ); ?>" );
					return;
				} else if ( form.link_type.value == '' ) {
					alert( "<?php echo $_LANG->_( 'Please select a menu type' ); ?>" );
					return;
				} else if ( form.link_name.value == "" ) {
					alert( "<?php echo $_LANG->_( 'Please enter a Name for this menu item' ); ?>" );
					return;
				}
			}

			if ( form.name.value == '' ) {
				alert("<?php echo $_LANG->_( 'Category must have a name' ); ?>");
			} else if ( form.title.value == '' ) {
				alert("<?php echo $_LANG->_( 'Category must have a title' ); ?>");
			} else {
				<?php getEditorContents( 'editor1', 'description' ) ; ?>
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">
		<?php
		categoriesScreens::editCategory();
		?>
		<table width="100%">
		<tr>
			<td valign="top" width="60%">
				<table class="adminform">
				<tr>
					<th colspan="3">
						<?php echo $_LANG->_( 'Category Details' ); ?>
					</th>
				</tr>
				<tr>
					<td nowrap>
						<?php echo $_LANG->_( 'Category Title' ); ?>:
					</td>
					<td colspan="2">
						<input class="text_area" type="text" name="title" value="<?php echo $row->title; ?>" size="50" maxlength="50" title="<?php echo $_LANG->_( 'tipTitleField' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $_LANG->_( 'Category Name' ); ?>:
					</td>
					<td colspan="2">
						<input class="text_area" type="text" name="name" value="<?php echo $row->name; ?>" size="50" maxlength="255" title="<?php echo $_LANG->_( 'tipNameField' ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $_LANG->_( 'Section' ); ?>:
					</td>
					<td colspan="2">
						<?php echo $lists['section']; ?>
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
						<script language="javascript" type="text/javascript">
						if (document.forms[0].image.options.value!=''){
						  jsimg='../images/stories/' + getSelectedValue( 'adminForm', 'image' );
						} else {
						  jsimg='../images/M_images/blank.png';
						}
						document.write('<img src=' + jsimg + ' name="imagelib" width="80" height="80" border="2" alt="Image" />');
						</script>
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
						editorArea( 'editor1',  $row->description , 'description', '100%;', '300', '60', '20', 0 ) ;
						?>
					</td>
				</tr>
				</table>
			</td>
			<td valign="top" width="40%">
				<?php
				if ( $lists['links'] ) {
					?>
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo $_LANG->_( 'Link to Menu' ); ?>
						</th>
					</tr>
					<tr>
						<td colspan="2">
							<?php echo $_LANG->_( 'descNewMenuItem' ); ?>
							<br /><br />
						</td>
					</tr>
					<tr>
						<td valign="top" width="100">
							<?php echo $_LANG->_( 'Select a Menu' ); ?>
						</td>
						<td>
							<?php echo $lists['menuselect']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" width="100">
							<?php echo $_LANG->_( 'Select Menu Type' ); ?>
						</td>
						<td>
							<?php echo $lists['link_type']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" width="100">
							<?php echo $_LANG->_( 'Menu Item Name' ); ?>
						</td>
						<td>
							<input type="text" name="link_name" class="inputbox" value="" size="25" />
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<input name="menu_link" type="button" class="button" value="<?php echo $_LANG->_( 'Link to Menu' ); ?>" onClick="submitbutton('menulink');" />
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
						mosFS::load( '@class', 'com_menus' );
						mosMenuFactory::buildLinksSecCat( $menus );
					}
					?>
					<tr>
						<td colspan="2">
						</td>
					</tr>
					</table>
					<?php
				}
				?>
			</td>
		</tr>
		</table>
		</fieldset>
		</div>

		<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
		<input type="hidden" name="option" value="com_categories" />
		<input type="hidden" name="oldtitle" value="<?php echo $row->title ; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $row->section; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		</form>
		<?php
	}


	/**
	* Form to select Section to move Category to
	*/
	function moveCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect ) {
		global $_LANG;
	   ?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="30%">
				<strong>
				<?php echo $_LANG->_( 'Move to Section' ); ?>:
				</strong>
				<br />
				<?php echo $SectionList ?>
				<br /><br />
			</td>
			<td align="left" valign="top" width="20%">
				<strong>
				<?php echo $_LANG->_( 'Categories being moved' ); ?>:
				</strong>
				<br />
				<?php
				echo "<ol>";
				foreach ( $items as $item ) {
					echo "<li>". $item->name ."</li>";
				}
				echo "</ol>";
				?>
			</td>
			<td valign="top" width="20%">
				<strong>
				<?php echo $_LANG->_( 'Content Items being moved' ); ?>:
				</strong>
				<br />
				<?php
				echo "<ol>";
				foreach ( $contents as $content ) {
					echo "<li>". $content->title ."</li>";
				}
				echo "</ol>";
				?>
			</td>
			<td valign="top">
				<?php echo $_LANG->_( 'This will move the Categories listed' ); ?>
				<br />
				<?php echo $_LANG->_( 'DESCANDALLITEMSWITHINCAT' ); ?>
				<br />
				<?php echo $_LANG->_( 'to the selected Section' ); ?>
			</td>.
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="section" value="<?php echo $sectionOld;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		<input type="hidden" name="task" value="" />
		<?php
		foreach ( $cid as $id ) {
			echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
		}
		?>
		</form>
		<?php
	}


	/**
	* Form to select Section to copy Category to
	*/
	function copyCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect ) {
		global $_LANG;
	   ?>
		<form action="index2.php" method="post" name="adminForm">

		<br />
		<table class="adminform">
		<tr>
			<td width="3%">
			</td>
			<td align="left" valign="top" width="30%">
				<strong>
				<?php echo $_LANG->_( 'Copy to Section' ); ?>:
				</strong>
				<br />
				<?php echo $SectionList ?>
				<br /><br />
			</td>
			<td align="left" valign="top" width="20%">
				<strong>
				<?php echo $_LANG->_( 'Categories being copied' ); ?>:
				</strong>
				<br />
				<?php
				echo "<ol>";
				foreach ( $items as $item ) {
					echo "<li>". $item->name ."</li>";
				}
				echo "</ol>";
				?>
			</td>
			<td valign="top" width="20%">
				<strong>
				<?php echo $_LANG->_( 'Content Items being copied' ); ?>:
				</strong>
				<br />
				<?php
				echo "<ol>";
				foreach ( $contents as $content ) {
					echo "<li>". $content->title ."</li>";
					echo "\n <input type=\"hidden\" name=\"item[]\" value=\"$content->id\" />";
				}
				echo "</ol>";
				?>
			</td>
			<td valign="top">
				<?php echo $_LANG->_( 'This will copy the Categories listed' ); ?>
				<br />
				<?php echo $_LANG->_( 'and all the items within the category (also listed)' ); ?>
				<br />
				<?php echo $_LANG->_( 'to the selected Section' ); ?>
			</td>
		</tr>
		</table>
		<br /><br />

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="section" value="<?php echo $sectionOld;?>" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		<input type="hidden" name="task" value="" />
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