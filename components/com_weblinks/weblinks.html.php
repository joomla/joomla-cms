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
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksView {

	/**
	 * Displays a web link category
	 *
	 * @param array $categories An array of categories to display
	 * @param array $rows An array of weblinks to display
	 * @param int $catid Category id of the current category
	 * @param object $category Category model of the current category
	 * @param object $params Parameters object for the current category
	 * @param array $tabclass Two element array of the two CSS classes used for alternating rows in a table
	 */
	function showCategory( &$categories, &$rows, $catid, &$category, &$params, $tabclass, &$lists, &$page ) {
		global $hide_js;

		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->name; ?>
			</div>
			<?php
		}
		?>
		<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php
		if ( @$category->imgTag || @$category->description ) {
			?>
			<tr>
				<td valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php
					// show image
					if ( isset($category->imgTag) ) {
						echo $category->imgTag;
					}
					echo $category->description;
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td width="60%" colspan="2">
				<?php
				if ( count( $rows ) ) {
					WeblinksView::showTable( $params, $rows, $catid, $tabclass, $lists, $page  );
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				// Displays listing of Categories
				if ( ( $params->get( 'type' ) == 'category' ) && $params->get( 'other_cat' ) ) {
					WeblinksView::showCategories( $params, $categories, $catid );
				} else if ( ( $params->get( 'type' ) == 'section' ) && $params->get( 'other_cat_section' ) ) {
					WeblinksView::showCategories( $params, $categories, $catid );
				}
				?>
			</td>
		</tr>
		</table>
		<?php
	}

	/**
	 * Helper function to display a table of web link items
	 *
	 * @param object $params Parameters object
	 * @param array $rows Array of web link objects to show
	 * @param int $catid Category id of the web link category to show
	 * @param array $tabclass Two element array with the CSS classnames of the alternating table rows
	 * @since 1.0
	 */
	function showTable( &$params, &$rows, $catid, $tabclass, &$lists, &$page  ) {
		global $Itemid;

		// icon in table display
		if ( $params->get( 'weblink_icons' ) <> -1 ) {
			$img = mosAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $params->get( 'weblink_icons' ), '/images/M_images/', 'Link', 'Link' );
		} else {
			$img = NULL;
		}
		?>
		<script language="javascript" type="text/javascript">
		function tableOrdering( order, dir, task ) {
			var form = document.adminForm;

			form.filter_order.value 	= order;
			form.filter_order_Dir.value	= dir;
			document.adminForm.submit( task );
		}
		</script>

		<form action="index.php?option=com_weblinks&amp;catid=<?php echo $catid;?>&amp;Itemid=<?php echo $Itemid;?>" method="post" name="adminForm">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td align="right" colspan="4">
				<?php
				if ($params->get('display')) {
					echo JText::_('Display Num') .'&nbsp;';
					$link = "index.php?option=com_weblinks&amp;catid=$catid&amp;Itemid=$Itemid";
					echo $page->getLimitBox($link);
				}
				?>
			</td>
		</tr>
		<?php
		if ( $params->get( 'headings' ) ) {
			?>
			<tr>
				<td width="10" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo JText::_('Num'); ?>
				</td>
				<?php
				if ( $img ) {
					?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
						&nbsp;
					</td>
					<?php
				}
				?>
				<td width="90%" height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php mosCommonHTML::tableOrdering( 'Web Link', 'title', $lists ); ?>
				</td>
				<?php
				if ( $params->get( 'hits' ) ) {
					?>
					<td width="30" height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" align="right" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Hits', 'hits', $lists ); ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
		}

		$k = 0;
		$i = 0;
		foreach ($rows as $row) {
			$iparams = new JParameter( $row->params );

			$link = sefRelToAbs( 'index.php?option=com_weblinks&task=view&catid='. $catid .'&id='. $row->id );
			$link = ampReplace( $link );

			$menuclass = 'category'.$params->get( 'pageclass_sfx' );

			switch ($iparams->get( 'target' )) {
				// cases are slightly different
				case 1:
					// open in a new window
					$txt = '<a href="'. $link .'" target="_blank" class="'. $menuclass .'">'. $row->title .'</a>';
					break;

				case 2:
					// open in a popup window
					$txt = "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">". $row->title ."</a>\n";
					break;

				default:	// formerly case 2
					// open in parent window
					$txt = '<a href="'. $link .'" class="'. $menuclass .'">'. $row->title .'</a>';
					break;
			}
			?>
			<tr class="<?php echo $tabclass[$k]; ?>">
				<td align="center">
					<?php echo $page->rowNumber( $i ); ?>
				</td>
				<?php
				if ( $img ) {
					?>
					<td width="100" height="20" align="center">
						&nbsp;&nbsp;<?php echo $img;?>&nbsp;&nbsp;
					</td>
					<?php
				}
				?>
				<td height="20">
					<?php echo $txt; ?>
					<?php
					if ( $params->get( 'item_description' ) ) {
						?>
						<br />
						<?php echo $row->description; ?>
						<?php
					}
					?>
				</td>
				<?php
				if ( $params->get( 'hits' ) ) {
					?>
					<td align="center">
						<?php echo $row->hits; ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
		}
		?>
		<tr>
			<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php
				$link = "index.php?option=com_weblinks&amp;catid=$catid&amp;Itemid=$Itemid";
				echo $page->writePagesLinks($link);
				?>
			</td>
		</tr>
		<tr>
			<td colspan="4" align="right">
				<?php echo $page->writePagesCounter(); ?>
			</td>
		</tr>
		</table>

		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	 * Helper function to display a list of categories
	 *
	 * @param object $params Parameters object for the current category
	 * @param array $categories Array of categories to display
	 * @param int $catid Category id of current category
	 * @since 1.0
	 */
	function showCategories( &$params, &$categories, $catid ) {
		global $Itemid;
		?>
		<ul>
		<?php
		foreach ( $categories as $cat ) {
			if ( $catid == $cat->catid ) {
				?>
				<li>
					<b>
					<?php echo $cat->name;?>
					</b>
					&nbsp;
					<span class="small">
					(<?php echo $cat->numlinks;?>)
					</span>
				</li>
				<?php
			} else {
				$link = 'index.php?option=com_weblinks&amp;catid='. $cat->catid .'&amp;Itemid='. $Itemid;
				?>
				<li>
					<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo $cat->name;?>
					</a>
					&nbsp;
					<span class="small">
					(<?php echo $cat->numlinks;?>)
					</span>
				</li>
				<?php
			}
		}
		?>
		</ul>
		<?php
	}

	/**
	 * Displays the edit form for new and existing web links (FRONTEND)
	 *
	 * A new record is defined when <var>$row</var> is passed with the <var>id</var>
	 * property set to 0.
	 *
	 * @param object $row The JWeblinkModel object to edit
	 * @param string $categories The html for the categories select list
	 * @since 1.0
	 */
	function editWeblink( &$row, &$categories ) {
		global $mainframe;

		$option = JRequest::getVar('option');
		require_once( JPATH_SITE . '/includes/HTML_toolbar.php' );

		$Returnid = JRequest::getVar( 'Returnid', 0, '', 'int' );
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
			} else if (getSelectedValue('adminForm','catid') < 1) {
				alert( "<?php echo JText::_( 'You must select a category.', true ); ?>" );
			} else if (form.url.value == ""){
				alert( "<?php echo JText::_( 'You must have a url.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="<?php echo sefRelToAbs("index.php"); ?>" method="post" name="adminForm" id="adminForm">

		<div class="componentheading">
			<?php echo JText::_( 'Submit A Web Link' );?>
		</div>

		<div style="float: right;">
			<?php
			mosToolBar::startTable();
			mosToolBar::spacer();
			mosToolBar::save();
			mosToolBar::cancel();
			mosToolBar::endtable();
			?>
		</div>

		<table cellpadding="4" cellspacing="1" border="0" width="100%">
		<tr>
			<td width="10%">
				<label for="title">
					<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td width="80%">
				<input class="inputbox" type="text" id="title" name="title" size="50" maxlength="250" value="<?php echo htmlspecialchars( $row->title, ENT_QUOTES );?>" />
			</td>
		</tr>
		<tr>
			<td valign="top">
				<label for="catid">
					<?php echo JText::_( 'Section' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $categories['catid']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<label for="url">
					<?php echo JText::_( 'URL' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" id="url" name="url" value="<?php echo $row->url; ?>" size="50" maxlength="250" />
			</td>
		</tr>
		<tr>
			<td valign="top">
				<label for="description">
					<?php echo JText::_( 'Description' ); ?>:
				</label>
			</td>
			<td>
				<textarea class="inputbox" cols="30" rows="6" id="description" name="description" style="width:300px"><?php echo htmlspecialchars( $row->description, ENT_QUOTES );?></textarea>
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ordering" value="<?php echo $row->ordering; ?>" />
		<input type="hidden" name="approved" value="<?php echo $row->approved; ?>" />
		<input type="hidden" name="Returnid" value="<?php echo $Returnid; ?>" />
		</form>
		<?php
	}

	/**
	 * Method to show an empty container if there is no data to display
	 *
	 * @static
	 * @param string $msg The message to show
	 * @return void
	 * @since 1.5
	 */
	function emptyContainer($msg) {
		echo '<p>'.$msg.'</p>';
	}

	/**
	 * Writes a user input error message and if javascript is enabled goes back
	 * to the previous screen to try again.
	 *
	 * @param string $msg The error message to display
	 * @return void
	 * @since 1.5
	 */
	function userInputError($msg) {
		josErrorAlert($msg);
	}
}
?>