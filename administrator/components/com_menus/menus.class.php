<?php
/**
* @version $Id: menus.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Menus
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
 * @subpackage Menus
 */
class mosMenuFactory {

	/**
	 * build the multiple select list for Menu Links/Pages
	 */
	function buildMenuLinks( &$lookup, $all=NULL, $none=NULL, $style=0 ) {
		global $database;

		// get a list of the menu items
		$database->setQuery( "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE type != 'separator'"
		. "\n AND type != 'url'"
		. "\n AND published = '1'"
		. "\n ORDER BY menutype, parent, ordering"
		);
		$mitems = $database->loadObjectList();
		$mitems_temp = $mitems;

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v ) {
			$id = $v->id;
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( intval( $mitems[0]->parent ), '', array(), $children, 9999, 0, 0 );

		// Code that adds menu name to Display of Page(s)
		$text_count = '0';
		$mitems_spacer = $mitems_temp[0]->menutype;
		foreach ($list as $list_a) {
			foreach ($mitems_temp as $mitems_a) {
				if ($mitems_a->id == $list_a->id) {
					// Code that inserts the blank line that seperates different menus
					if ($mitems_a->menutype <> $mitems_spacer) {
						$list_temp[] = mosHTML::makeOption( -999, '----' );
						$mitems_spacer = $mitems_a->menutype;
					}
					$text = $mitems_a->menutype." | ".$list_a->treename;
					$list_temp[] = mosHTML::makeOption( $list_a->id, $text );
					if ( strlen($text) > $text_count) {
						$text_count = strlen($text);
					}
				}
			}
		}
		$list = $list_temp;

		$mitems = array();
		if ( $all ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 0, 'All' );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( -999, 'None' );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		// append the rest of the menu items to the array
		foreach ($list as $item) {
			$mitems[] = mosHTML::makeOption( $item->value, $item->text );
		}

		if ( $style ) {
			$pages = mosHTML::selectList( $mitems, 'selections[]', 'class="inputbox" size="26" multiple="multiple"', 'value', 'text', $lookup );
		} else {
			$html		= '<div class="menulist">';
			$total 		= count( $mitems );
			for ( $i=0; $i < $total; $i++ ) {
				if ( $mitems[$i]->value == -999 ) {
					$html .= '<hr/>';
				} else {
					$checked = '';
					foreach ( $lookup as $a ) {
						if ( $a->value == $mitems[$i]->value ) {
							$checked = 'checked="checked"';
							break;
						}
					}

					$html .= '<input type="checkbox" value="'. $mitems[$i]->value .'" name="selections[]" id="selections'. $i .'" '. $checked .'>';
					$html .= '<label for="selections'. $i .'">';
					$html .= $mitems[$i]->text;
					$html .= '</label>';
					$html .= '<br/>';
				}
			}
			$html		.= '</div>';
			$pages = $html;
		}

		return $pages;
	}

	/**
	 * build the select list for parent item
	 * @param object
	 * @param string Alternative name for the control
	 * @return string The HTML list element
	 */
	function buildParentList( &$row, $ctrlName='parent' ) {
		global $database;

		// get a list of the menu items
		$query = "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE menutype='$row->menutype'"
		. "\n AND published <> -2"
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems = array();
		$mitems[] = mosHTML::makeOption( '0', 'Top' );
		$this_treename = '';
		foreach ( $list as $item ) {
			if ( $this_treename ) {
				if ( $item->id != $row->id && strpos( $item->treename, $this_treename ) === false) {
					$mitems[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ( $item->id != $row->id ) {
					$mitems[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		$parent = mosHTML::selectList( $mitems, $ctrlName, 'class="inputbox" size="1"', 'value', 'text', $row->parent );
		return $parent;
	}

	/**
	* build the select list for Menu Ordering
	*/
	function buildOrderingList( &$row, $id ) {
		global $database, $_LANG;

		if ( $id ) {
			$order = mosGetOrderingList( "SELECT ordering AS value, name AS text"
			. "\n FROM #__menu"
			. "\n WHERE menutype='". $row->menutype ."'"
			. "\n AND parent='". $row->parent ."'"
			. "\n AND published != '-2'"
			. "\n ORDER BY ordering"
			);
			$ordering = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $_LANG->_( 'descNewItemsLast' );
		}
		return $ordering;
	}

	/**
	 * Outputs link to menu form for sections and categories
	 * @param array
	 */
	function buildLinksSecCat( &$menus ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function go2( pressbutton, menu, id ) {
			var form = document.adminForm;

			if (pressbutton == 'go2menu') {
				form.menu.value = menu;
			}

			if (pressbutton == 'go2menuitem') {
				form.menu.value 	= menu;
				form.menuid.value 	= id;
			}

			<?php getEditorContents( 'editor1', 'introtext' ) ; ?>
			submitform( pressbutton );
			return;
		}
		</script>
		<?php
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
					<hr/>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
					<?php echo $_LANG->_( 'Menu' ); ?>
				</td>
				<td>
					<a href="javascript:go2( 'go2menu', '<?php echo $menu->menutype; ?>' );" title="Go to Menu">
					<?php echo $menu->menutype; ?>
				</a>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
					<?php echo $_LANG->_( 'Type' ); ?>
				</td>
				<td>
				<?php echo $menu->type; ?>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
					<?php echo $_LANG->_( 'Item Name' ); ?>
				</td>
				<td>
					<strong>
					<a href="javascript:go2( 'go2menuitem', '<?php echo $menu->menutype; ?>', '<?php echo $menu->id; ?>' );" title="Go to Menu Item">
						<?php echo $menu->name; ?>
					</a>
					</strong>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
					<?php echo $_LANG->_( 'State' ); ?>
				</td>
				<td>
					<strong>
					<?php
					switch ( $menu->published ) {
						case -2:
							echo '<font color="red">Trashed</font>';
							break;
						case 0:
							echo 'UnPublished';
							break;
						case 1:
						default:
							echo '<font color="green">Published</font>';
							break;
					}
					?>
					</strong>
				</td>
			</tr>
			<?php
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	/**
	 * Get's the menu types in use?? why so complex
	 * Complexity due to the fact that there is no overall menu mapping table so
	 * menus are derived from active mod_mainmenu modules (to ensure empty menus shown)
	 * menu types from mos_menu for menus without a mod_mainmenu module
	 * complexity would be reduced by a mos_menutype table to map created menus
	 */
	function getMenuTypes() {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		. "\n ORDER BY title"
		;
		$database->setQuery( $query	);
		$modMenus = $database->loadObjectList();

		$query = "SELECT menutype"
		. "\n FROM #__menu"
		. "\n GROUP BY menutype"
		. "\n ORDER BY menutype"
		;
		$database->setQuery( $query	);
		$menuMenus = $database->loadObjectList();

		$menuTypes = '';
		foreach ( $modMenus as $modMenu ) {
			$check = 1;
			mosMakeHtmlSafe( $modMenu );
			$modParams 	= mosParseParams( $modMenu->params );
			$menuType 	= @$modParams->menutype;
			// special handling of '
			$menuType 	= str_replace( '&#039;', "'", $menuType );
			if (!$menuType) {
				$menuType = 'mainmenu';
			}

			// stop duplicate menutype being shown
			if ( !is_array( $menuTypes) ) {
				// handling to create initial entry into array
				$menuTypes[] = $menuType;
			} else {
				$check = 1;
				foreach ( $menuTypes as $a ) {
					if ( $a == $menuType ) {
						$check = 0;
					}
				}
				if ( $check ) {
					$menuTypes[] = $menuType;
				}
			}

		}
		// add menutypes from mos_menu
		foreach ( $menuMenus as $menuMenu ) {
			$check = 1;
			foreach ( $menuTypes as $a ) {
				if ( $a == $menuMenu->menutype ) {
					$check = 0;
				}
			}
			if ( $check ) {
				$menuTypes[] = $menuMenu->menutype;
			}
		}

		// sorts menutypes
		asort( $menuTypes );

		return $menuTypes;
	}

	function setValues( &$menu, $menutype ) {
		$menu->menutype 	= $menutype;
		$menu->browserNav 	= 0;
		$menu->ordering 	= 9999;
		$menu->parent 		= intval( mosGetParam( $_POST, 'parent', 0 ) );
		$menu->published 	= 1;
	}

	function buildLists( &$lists, &$menu, $uid, $link=NULL  ) {
		global $mainframe;

		$mainframe->set('disableMenu', true);

		// build html select list for target window
		$lists['target'] 		= mosAdminMenus::Target( $menu );
		// build the html select list for ordering
		$lists['ordering'] 		= mosAdminMenus::Ordering( $menu, $uid );
		// build the html select list for the group access
		$lists['access'] 		= mosAdminMenus::Access( $menu );
		// build the html select list for paraent item
		$lists['parent'] 		= mosAdminMenus::Parent( $menu );
		// build published button option
		$lists['published'] 	= mosAdminMenus::Published( $menu );
		// build the url link output
		$lists['link'] 			= mosAdminMenus::Link( $menu, $uid, $link );
	}

	function menutreeQueries( &$lists ) {
		global $database;

		// get list of menus for tree
		$menus	= mosMenuFactory::getMenuTypes();
		$i = 0;
		foreach ( $menus AS $menu ) {
			$lists['menus'][$i]->type = addslashes( $menu );
			$lists['menus'][$i]->num = $i + 1;
			$i++;
		}

		// get trash count for tree
	   $query = "SELECT COUNT( id )"
	   . "\n FROM #__menu"
	   . "\n WHERE published = '-2'"
	   ;
	   $database->setQuery( $query );
	   $lists['trash'] = $database->loadResult();
	}

	function formStart( $legend='' ) {
		global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">

		<fieldset>
			<legend>
				<?php echo $_LANG->_( 'Menu Item Type' ); ?> ::
				<?php echo $legend; ?>
			</legend>

			<table width="100%">
			<tr valign="top">
				<td width="60%">
		<?php
	}

	function formParams( $params='', $combine=2 ) {
		global $_LANG;

		?>
				</td>
				<td width="40%">
					<table class="adminform">
					<tr>
						<th>
						<?php echo $_LANG->_( 'Parameters' ); ?>
						</th>
					</tr>
					<tr>
						<td>
						<?php
						switch ( $combine ) {
							case 3:
								// Global Common Params
								echo $params[0]->render( 'params', 0 );
								// Type Common Params
								echo $params[1]->render( 'params', 0 );
								// Specific Params
								echo $params[2]->render( 'params', 0 );
								break;

							case 2:
								// Global Common Params
								echo $params[0]->render( 'params', 0 );
								// Specific Params
								echo $params[1]->render( 'params', 0 );
								break;

							case 1:
								// Specific Params only
								echo $params->render( 'params', 0 );
								break;

							default:
								echo $params;
								break;
						}
						?>
						</td>
					</tr>
					<tr>
						<td>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</fieldset>
		<?php
	}

	function tableStart() {
		global $_LANG;

		?>
		<table class="adminform">
		<tr>
			<th colspan="2">
			<?php echo $_LANG->_( 'Details' ); ?>
			</th>
		</tr>
		<?php
	}

	function tableEnd() {
		?>
		<tr>
			<td colspan="2">
			</td>
		</tr>
		</table>
		<?php
	}

	function formElementName( $value, $text='', $tip='' ) {
		global $_LANG;

		$value = htmlspecialchars( $value, ENT_QUOTES );

		if ( $tip ) {
			$tip = mosToolTip( $tip );
		}

		if ( empty( $text ) ) {
			$text = $_LANG->_( 'Name' );
		}
		?>
		<tr valign="top">
			<td width="10%" align="right" valign="top">
			<?php echo $text; ?>:
			</td>
			<td width="80%">
			<input type="text" name="name" size="30" maxlength="100" class="inputbox" value="<?php echo $value; ?>"/>
			<?php echo $tip; ?>
			</td>
		</tr>
		<?php
	}

	function formElementInput( $text, $name, $value, $tip='' ) {
		global $_LANG;

		if ( $tip ) {
			$tip = mosToolTip( $tip );
		}

		?>
		<tr valign="top">
			<td align="right">
			<?php echo $text; ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="<?php echo $name; ?>" size="50" maxlength="250" value="<?php echo $value; ?>" />
			<?php echo $tip; ?>
			</td>
		</tr>
		<?php
	}

	function formElement( $lists, $title, $tip='' ) {
		global $_LANG;

		if ( $tip ) {
			$tip = mosToolTip( $tip );
		}

		switch ( $title ) {
			case 'CAT':
				$title = $_LANG->_( 'Category' );
				break;

			case 'ORD':
				$title = $_LANG->_( 'Ordering' );
				break;

			case 'ACC':
				$title = $_LANG->_( 'Access Level' );
				break;

			case 'PAR':
				$title = $_LANG->_( 'Parent Item' );
				break;

			case 'PUB':
				$title = $_LANG->_( 'Published' );
				break;

			case 'SEC':
				$title = $_LANG->_( 'Section' );
				break;

			case 'TAR':
				$title = $_LANG->_( 'On Click, open' );
				break;

			case 'URL':
				$title = $_LANG->_( 'Url' );
				break;
		}
		?>
		<tr valign="top">
			<td valign="top" align="right">
			<?php echo $title; ?>:
			</td>
			<td>
			<?php echo $lists; ?>
			<?php echo $tip; ?>
			</td>
		</tr>
		<?php
	}

	function formElementHdden( &$menu, $option ) {
		global $_LANG;
		?>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<?php
	}
}
?>