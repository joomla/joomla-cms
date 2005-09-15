<?php
/**
 * @version $Id: content.class.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * @subpackage Content
 */
class mosContentFactory {

	/**
	 * Shows a content legend
	 */
	function buildContentLegend( ) {
		global $_LANG;
		?>
		<table cellspacing="0" cellpadding="4" border="0" align="center">
		<tr align="center">
			<td>
				<img src="images/tick.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_('Visible'); ?>" />
			</td>
			<td>
				<?php echo $_LANG->_('Published and is <u>Current</u>'); ?> |
			</td>
			<td>
				<img src="images/publish_y.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_('Pending'); ?>" />
			</td>
			<td>
				<?php echo $_LANG->_('Published, but is <u>Pending</u>'); ?> |
			</td>
			<td>
				<img src="images/publish_r.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_('Finished'); ?>" />
			</td>
			<td>
				<?php echo $_LANG->_('Published, but has <u>Expired</u>'); ?> |
			</td>
			<td>
				<img src="images/publish_x.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_('Finished'); ?>" />
			</td>
			<td>
				<?php echo $_LANG->_('Not Published'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="8" align="center">
				<?php echo $_LANG->_('Click on icon to toggle state.'); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	/**
	 * Shows menu links
	 */
	function buildMenuLinks( &$menus ) {
		global $option;
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
			<?php
			if ( $option == 'com_content' ) {
				getEditorContents( 'editor2', 'fulltext' ) ;
			}
			?>
			submitform( pressbutton );
			return;
		}
		</script>
		<?php
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
				<hr />
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top"><?php echo $_LANG->_('Menu'); ?>
				</td>
				<td>
				<a href="javascript:go2( 'go2menu', '<?php echo $menu->menutype; ?>' );" title="<?php echo $_LANG->_('Go to Menu'); ?>">
				<?php echo $menu->menutype; ?>
				</a>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top"><?php echo $_LANG->_('Link Name'); ?>
				</td>
				<td>
				<strong>
				<a href="javascript:go2( 'go2menuitem', '<?php echo $menu->menutype; ?>', '<?php echo $menu->id; ?>' );" title="<?php echo $_LANG->_('Go to Menu Item'); ?>">
				<?php echo $menu->name; ?>
				</a>
				</strong>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top"><?php echo $_LANG->_('State'); ?>
				</td>
				<td>
				<?php
				switch ( $menu->published ) {
					case -2:
						echo "<font color=\"red\">". $_LANG->_('Trashed') ."</font>";
						break;
					case 0:
						echo $_LANG->_('UnPublished');
						break;
					case 1:
					default:
						echo "<font color=\"green\">". $_LANG->_('Published') ."</font>";
						break;
				}
				?>
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
	 * Select list of menus
	 */
	function buildMenuSelect( $name='menuselect', $javascript=NULL ) {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		;
		$database->setQuery( $query );
		$menus = $database->loadObjectList();
		$total = count( $menus );
		for( $i = 0; $i < $total; $i++ ) {
			$params = mosParseParams( $menus[$i]->params );
			$menuselect[$i]->value 	= $params->menutype;
			$menuselect[$i]->text 	= $params->menutype;
		}
		// sort array of objects
		SortArrayObjects( $menuselect, 'text', 1 );

		$menus = mosHTML::selectList( $menuselect, $name, 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $menus;
	}

	/**
	 * Select list of menu items for a specific menu
	 */
	function buildLinksToMenu( $type, $and ) {
		global $database;

		$query = "SELECT *"
		. "\n FROM #__menu"
		. "\n WHERE type = '". $type ."'"
		. "\n AND published = '1'"
		. $and
		;
		$database->setQuery( $query );
		$menus = $database->loadObjectList();

		return $menus;
	}

	/**
	 * Select list of active sections
	 */
	function buildSelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' ) {
		global $database, $_LANG;

		$categories[] = mosHTML::makeOption( '0', '- ' . $_LANG->_( 'Section' ) . ' -', 'id', 'title' );
		$query = "SELECT id, title"
		. "\n FROM #__sections"
		. "\n WHERE published = '1'"
		. "\n ORDER BY ". $order
		;
		$database->setQuery( $query );
		$sections = array_merge( $categories, $database->loadObjectList() );

		$category = mosHTML::selectList( $sections, $name, 'class="inputbox" size="1" '. $javascript, 'id', 'title', $active );

		return $category;
	}

	/**
	 * build the select list to choose a category
	 */
	function buildCategoryList( &$menu, $id, $javascript='' ) {
		global $database;

		$query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
		. "\n FROM #__sections AS s"
		. "\n INNER JOIN #__categories AS c ON c.section = s.id"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name,c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$category = '';
		if ( $id ) {
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$category = $row->text;
				}
			}
			$category .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
			$category .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$category = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"'. $javascript, 'value', 'text' );
			$category .= '<input type="hidden" name="link" value="" />';
		}
		return $category;
	}

	/**
	 * build the select list to choose a section
	 */
	function buildSectionList( &$menu, $id, $all=0 ) {
		global $database, $_LANG;

		$query = "SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`"
		. "\n FROM #__sections AS s"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name"
		;
		$database->setQuery( $query );
		if ( $all ) {
			$rows[] = mosHTML::makeOption( 0, '- ' . $_LANG->_( 'All Sections' ) . ' -');
			$rows = array_merge( $rows, $database->loadObjectList() );
		} else {
			$rows = $database->loadObjectList();
		}

		if ( $id ) {
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$section = $row->text;
				}
			}
			$section .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
			$section .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$section = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text' );
			$section .= '<input type="hidden" name="link" value="" />';
		}
		return $section;
	}

	function contenttreeQueries( &$lists ) {
		global $database;

		// get list of sections for tree
		$query = "SELECT id, title"
		. "\n FROM #__sections"
		. "\n ORDER BY id"
		;
		$database->setQuery( $query );
		$lists['sections'] = $database->loadObjectList();

		$total = count( $lists['sections'] );
		for( $i=0; $i < $total; $i++  ) {
			// apostrophe handling
			$lists['sections'][$i]->title = addslashes( $lists['sections'][$i]->title );
		}

		// get list of categories for tree
		$query = "SELECT c.id, c.title, c.section"
		. "\n FROM #__categories AS c"
		. "\n INNER JOIN #__sections AS s ON s.id = c.section"
		. "\n ORDER BY c.id"
		;
		$database->setQuery( $query );
		$lists['categories'] = $database->loadObjectList();

		$total = count( $lists['categories'] );
		for( $i=0; $i < $total; $i++  ) {
			// apostrophe handling
			$lists['categories'][$i]->title = addslashes( $lists['categories'][$i]->title );
		}

		// get trash count for tree
	   $query = "SELECT count(id)"
	   . "\n FROM #__content"
	   . "\n WHERE state = '-2'"
	   ;
	   $database->setQuery( $query );
	   $lists['trash'] = $database->loadResult();
	}
}
?>