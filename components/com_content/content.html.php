<?php
/**
* @version $Id: content.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
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
* Utility class for writing the HTML for content
* @package Mambo
* @subpackage Content
*/
class HTML_content {
	/**
	* Function depreciated and moved
	* This is being kept for Backward compatability
	*/
	function show( &$row, &$params, &$access, $page=0, $option, $ItemidCount=NULL ) {
		contentScreens_front::item( $row, $params, $access, $page, $option, $ItemidCount );
	}

	/**
	* Draws a Content List
	* Used by Content Category & Content Section
	*/
	function showContentList( $title, $items, $access, $id=0, $sectionid=NULL, $gid, $params, $pageNav=NULL, $other_categories, $lists, $task='section' ) {
		if ( $task == 'section' ) {
			contentScreens_front::list_section( $params, $title, $other_categories, $access );
		} else {
			contentScreens_front::table_category( $params, $title, $other_categories, $access, $items, $lists );
		}
	}

	/**
	* Display links to content items
	*/
	function showLinks( &$rows, $links, $total, $i=0, $show=1, $ItemidCount, &$params, $access ) {
		global $mainframe, $_LANG;

		if ( $show ) {
			?>
			<div>
			<strong>
			<?php echo $_LANG->_('More'); ?>
			</strong>
			</div>
			<ul>
			<?php
		}
		for ( $z = 0; $z < $links; $z++ ) {
			if ( $i >= $total ) {
				// stops loop if total number of items is less than the number set to display as intro + leading
				break;
			}
			// needed to reduce queries used by getItemid
			$_Itemid = $mainframe->getItemid( $rows[$i]->id, 0, 0, $ItemidCount['bs'], $ItemidCount['bc'], $ItemidCount['gbs']  );
			$link = sefRelToAbs( 'index.php?option=com_content&amp;task=view&amp;id='. $rows[$i]->id .'&amp;Itemid='. $_Itemid )
			?>
			<li>
			<?php HTML_content::EditIcon( $rows[$i], $params, $access ); ?>
			<a class="blogsection" href="<?php echo $link; ?>">
			<?php echo $rows[$i]->title; ?>
			</a>
			</li>
			<?php
			$i++;
		}
		?>
		</ul>
		<?php
	}


	/**
	* Writes Edit icon that links to Section edit page
	*/
	function EditIconSection( $row, $params, $access, $output=1 ) {
		global $mosConfig_live_site, $mosConfig_edit_popup;
		global $Itemid, $my, $_LANG;

		if ( $params->get( 'popup' ) ) {
			return;
		}
		if ( $row->published < 0 ) {
			return;
		}
		if ( !$access->canEdit ) {
			return;
		}
		$link 		= 'index.php?option=com_content&amp;task=edit_section&amp;id='. $row->id .'&amp;Itemid='. $Itemid .'&amp;Returnid='. $Itemid;
		$link 		= sefRelToAbs( $link );
		$image 		= mosAdminMenus::ImageCheck( 'edit.png', '/images/M_images/', NULL, NULL );

		$state		= ( $row->published == 1 ? $_LANG->_( 'CMN_PUBLISHED' ) : $_LANG->_( 'CMN_UNPUBLISHED' ) );
		$info		= '<table>';
		$info		.= '<tr><td>'. $_LANG->_( 'Access Level' ) .':</td><td>'. $row->groups .'</td></tr>';
		$info		.= '<tr><td>'. $_LANG->_( 'State' ) .':</td><td>'. $state .'</td></tr>';
		$info 		.= '<tr><td><br/>* '. $_LANG->_( 'Click to Edit' ) .' *</td></tr>';
		$info		.= '</table>';
		$overlib	= 'onMouseOver="return overlib(\''. $info .'\', ABOVE, RIGHT);" onMouseOut="return nd();"';

		// popup handling
		$onclick = '';
		if ( $mosConfig_edit_popup ) {
			$link 		= '#';
			$linkpop	= 'index2.php?option=com_content&task=edit_sectionpop&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid;
			$onclick	= "javascript:window.open( '$linkpop', 'win1', 'status=yes,toolbar=no,scrollbars=yes,titlebar=yes,menubar=no,resizable=yes,width=800,height=600,directories=no,location=no' );";
		}

		if ( $output ) {
			?>
			<a href="<?php echo $link; ?>" onclick="<?php echo $onclick; ?>" <?php echo $overlib; ?> style="font-weight: normal;">
				<?php echo $image; ?>
				<strong><?php echo $_LANG->_( 'E_EDIT_SECTION' ); ?></strong>
			</a>
			<?php
		} else {
			$output = '
			<a href="'. $link .'" onclick="'. $onclick .'" '. $overlib .' style="font-weight: normal;">
				'. $image .'
				<strong>'. $_LANG->_( 'E_EDIT_SECTION' ) .'</strong>
			</a>'
			;

			return $output;
		}
	}

	/**
	* Writes Edit icon that links to Catgeory edit page
	*/
	function EditIconCategory( $row, $params, $access, $text=1, $output=1 ) {
		global $mosConfig_live_site, $mosConfig_edit_popup;
		global $Itemid, $my, $_LANG;

		if ( $params->get( 'popup' ) ) {
			return;
		}
		if ( $row->published < 0 ) {
			return;
		}
		if ( !$access->canEdit ) {
			return;
		}
		$link 		= 'index.php?option=com_content&amp;task=edit_category&amp;id='. $row->id .'&amp;Itemid='. $Itemid .'&amp;Returnid='. $Itemid;
		$link 		= sefRelToAbs( $link );
		$image 		= mosAdminMenus::ImageCheck( 'edit.png', '/images/M_images/', NULL, NULL );

		$state		= ( $row->published == 1 ? $_LANG->_( 'CMN_PUBLISHED' ) : $_LANG->_( 'CMN_UNPUBLISHED' ) );
		$info		= '<table>';
		$info		.= '<tr><td>'. $_LANG->_( 'Access Level' ) .':</td><td>'. $row->groups .'</td></tr>';
		$info		.= '<tr><td>'. $_LANG->_( 'State' ) .':</td><td>'. $state .'</td></tr>';
		$info 		.= '<tr><td><br/>* '. $_LANG->_( 'Click to Edit' ) .' *</td></tr>';
		$info		.= '</table>';
		$overlib	= 'onMouseOver="return overlib(\''. $info .'\', ABOVE, RIGHT);" onMouseOut="return nd();"';

		// popup handling
		$onclick = '';
		if ( $mosConfig_edit_popup ) {
			$link 		= '#';
			$linkpop	= 'index2.php?option=com_content&task=edit_categorypop&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid;
			$onclick	= "javascript:window.open( '$linkpop', 'win1', 'status=yes,toolbar=no,scrollbars=yes,titlebar=yes,menubar=no,resizable=yes,width=800,height=600,directories=no,location=no' );";
		}

		if ( $output ) {
			?>
			<a href="<?php echo $link; ?>" onclick="<?php echo $onclick; ?>" <?php echo $overlib; ?> style="font-weight: normal;">
				<?php
				echo $image;

				if ( $text ) {
					?>
					<strong>
					<?php echo $_LANG->_( 'E_EDIT_CATEGORY' ); ?>
					</strong>
					<?php
				}
				?>
			</a>
			<?php
		} else {
			$output = '
			<a href="'. $link .'" onclick="'. $onclick .'" '. $overlib .' style="font-weight: normal;">'. $image;

			if ( $text ) {
				$output .= ' <strong>'. $_LANG->_( 'E_EDIT_CATEGORY' ) .'</strong>';
			}
			$output .= '</a>';

			return $output;
		}
	}

	/**
	* Writes Edit icon that links to edit page
	*/
	function EditIcon( $row, $params, $access, $output=1 ) {
		global $mosConfig_live_site, $mosConfig_edit_popup;
		global $Itemid, $my;
		global $_LANG;

		if ( $params->get( 'popup' ) ) {
			return;
		}
		if ( $row->state < 0 ) {
			return;
		}
		if ( !$access->canEdit && !( $access->canEditOwn && $row->created_by == $my->id ) ) {
			return;
		}
		$link 		= 'index.php?option=com_content&amp;task=edit&amp;id='. $row->id .'&amp;Itemid='. $Itemid .'&amp;Returnid='. $Itemid;
		$link		= sefRelToAbs( $link );
		$image 		= mosAdminMenus::ImageCheck( 'edit.png', '/images/M_images/', NULL, NULL );

		$state		= ( $row->state == 1 ? $_LANG->_( 'CMN_PUBLISHED' ) : $_LANG->_( 'CMN_UNPUBLISHED' ) );
		$info		= '<table>';
		$info		.= '<tr><td>'. $_LANG->_( 'Access Level' ) .':</td><td>'. $row->groups .'</td></tr>';
		$info		.= '<tr><td>'. $_LANG->_( 'State' ) .':</td><td>'. $state .'</td></tr>';
		$info 		.= '<tr><td><br/>* '. $_LANG->_( 'Click to Edit' ) .' *</td></tr>';
		$info		.= '</table>';
		$overlib	= 'onMouseOver="return overlib(\''. $info .'\', ABOVE, RIGHT);" onMouseOut="return nd();"';

		// load overlib
		mosCommonHTML::loadOverlib();

		// popup handling
		$onclick = '';
		if ( $mosConfig_edit_popup ) {
			$link 		= '#';
			$linkpop	= 'index2.php?option=com_content&task=editpop&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid;
			$onclick	= "javascript:window.open( '$linkpop', 'win1', 'status=yes,toolbar=no,scrollbars=yes,titlebar=yes,menubar=no,resizable=yes,width=800,height=600,directories=no,location=no' );";
		}

		if ( $output ) {
			?>
			<a href="<?php echo $link; ?>" onclick="<?php echo $onclick; ?>" <?php echo $overlib; ?> style="font-weight: normal;">
			<?php echo $image; ?>
			</a>
			<?php
		} else {
			$output = '
			<a href="'. $link .'" onclick="'. $onclick .'" '. $overlib .' style="font-weight: normal;">
			'. $image .'
			</a>'
			;

			return $output;
		}
	}

	/**
	* ## This function has been depreciated in the conversion to pT,  it is no longer used by the core but is being kept for backward compatability of 3PD ##
	* Writes PDF icon
	*/
	function PdfIcon( $row, $params, $link_on, $hide_js, $output=1 ) {
		global $mosConfig_live_site, $_LANG;

		if ( $params->get( 'pdf' ) && !$params->get( 'popup' ) ) {
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			$link = $mosConfig_live_site. '/index2.php?option=com_content&amp;do_pdf=1&amp;id='. $row->id;
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'pdf_button.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'PDF' ) );
			} else {
				$image = $_LANG->_( 'PDF' ) .'&nbsp;';
			}

			$onclick	= "javascript:window.open( '$link', 'win1', '$status' );";
			if ( $output ) {
				?>
				<td align="right" width="100%" class="buttonheading">
					<a href="#" onclick="<?php echo $onclick;?>" style="font-weight: normal;" title="<?php echo $_LANG->_( 'PDF' ); ?>">
						<?php echo $image; ?>
					</a>
				</td>
				<?php
			} else {
				$output = '
				<td align="right" width="100%" class="buttonheading">
					<a href="#" onclick="'. $onclick .'" style="font-weight: normal;" title="'. $_LANG->_( 'PDF' ) .'">
						'. $image .'
					</a>
				</td>'
				;

				return $output;
			}
		}
	}

	/**
	* ## This function has been depreciated in the conversion to pT,  it is no longer used by the core but is being kept for backward compatability of 3PD ##
	* Writes Email icon
	*/
	function EmailIcon( $row, $params, $hide_js, $output=1 ) {
		global $mosConfig_live_site, $_LANG;
		if ( $params->get( 'email' ) && !$params->get( 'popup' ) ) {
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=500,height=320,directories=no,location=no';
			$link 	= $mosConfig_live_site .'/index2.php?option=com_content&amp;task=emailform&amp;id='. $row->id;
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'emailButton.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'EMail' ) );
			} else {
				$image = '&nbsp;'. $_LANG->_( 'EMail' );
			}

			$onclick	= "javascript:window.open( '$link', 'win1', '$status' );";
			if ( $output ) {
				?>
				<td align="right" width="100%" class="buttonheading">
					<a href="#" onclick="<?php echo $onclick;?>" style="font-weight: normal;" title="<?php echo $_LANG->_( 'Email' ); ?>">
						<?php echo $image;?>
					</a>
				</td>
				<?php
			} else {
				$output = '
				<td align="right" width="100%" class="buttonheading" title="'. $_LANG->_( 'Email' ) .'">
					<a href="#" onclick="'. $onclick .'" style="font-weight: normal;">
						'. $image .'
					</a>
				</td>'
				;

				return $output;
			}
		}
	}
}

/**
 * @package Mambo
 * @subpackage Content
 */
class contentScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml, $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	/**
	* Writes Email form for filling in the send destination
	*/
	function email( $id ) {
		global $mainframe;

		$tmpl =& contentScreens_front::createTemplate( 'email.html' );

		$params = new mosParameters( '' );
		$params->set( 'popup', 1 );
		$close 		= mosHTML::CloseButton ( $params, 0, 0 );

		$tmpl->addVar( 'body', 'close_button', 	$close );
		$tmpl->addVar( 'body', 'id', 			$id );

		$tmpl->displayParsedTemplate( 'body' );
	}

	/**
	* Writes Email sent popup
	* @param string Who it was sent to
	*/
	function emailSent( $to ) {
		$tmpl =& contentScreens_front::createTemplate( 'email_sent.html' );

		$params = new mosParameters( '' );
		$params->set( 'popup', 1 );
		$close 		= mosHTML::CloseButton ( $params, 0, 0 );

		$tmpl->addVar( 'body', 'close_button', 	$close );
		$tmpl->addVar( 'body', 'to', 			$to );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function item( $row, $params, $access, $page=0, $option, $ItemidCount=NULL ) {
		global $mosConfig_live_site;
		global $Itemid, $task;
		global $_MAMBOTS;
		global $_LANG;

		// process the new bots
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$row, &$params, $page ), true );

		$edit		= HTML_content::EditIcon( $row, $params, $access, 0 );
		// Print Icon
		if ( $params->get( 'icons' ) ) {
			$print_image = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'Print' ) );
		} else {
			$print_image = $_LANG->_( 'ICON_SEP' ) .'&nbsp;'. $_LANG->_( 'Print' ). '&nbsp;'. $_LANG->_( 'ICON_SEP' );
		}
		$link 			= $mosConfig_live_site. '/index2.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $Itemid .'&amp;pop=1&amp;page='. @$page;
		$status 		= 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
		$print_onclick 	= "javascript:window.open( '$link', 'win1', '$status' );";
		// PDF Icon
		if ( $params->get( 'icons' ) ) {
			$pdf_image = mosAdminMenus::ImageCheck( 'pdf_button.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'PDF' ) );
		} else {
			$pdf_image = $_LANG->_( 'PDF' ) .'&nbsp;';
		}
		$status 		= 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
		$link 			= $mosConfig_live_site. '/index2.php?option=com_content&amp;do_pdf=1&amp;id='. $row->id;
		$pdf_onlclick 	= "javascript:window.open( '$link', 'win1', '$status' );";
		// Email Icon
		if ( $params->get( 'icons' ) ) {
			$email_image 	= mosAdminMenus::ImageCheck( 'emailButton.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'EMail' ) );
		} else {
			$email_image 	= '&nbsp;'. $_LANG->_( 'EMail' );
		}
		$status 			= 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=500,height=320,directories=no,location=no';
		$link 				= $mosConfig_live_site .'/index2.php?option=com_content&amp;task=emailform&amp;id='. $row->id;
		$email_onlclick		= "javascript:window.open( '$link', 'win1', '$status' );";

		$tmpl =& contentScreens_front::createTemplate( 'item.html' );

		$tmpl->addVar( 'body', 'edit_icon',			$edit );
		$tmpl->addVar( 'body', 'print_image',		$print_image );
		$tmpl->addVar( 'body', 'print_onclick',		$print_onclick );
		$tmpl->addVar( 'body', 'pdf_image',			$pdf_image );
		$tmpl->addVar( 'body', 'pdf_onclick',		$pdf_onlclick );
		$tmpl->addVar( 'body', 'email_image',		$email_image );
		$tmpl->addVar( 'body', 'email_onclick',		$email_onlclick );

		$tmpl->addVar( 'body', 'show_title_area',	( ( $params->get( 'item_title' ) || $params->get( 'pdf' )  || $params->get( 'print' ) || $params->get( 'email' ) ) ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'linked_title',		( ( $params->get( 'link_titles' ) && $row->link_on != '' ) ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'show_sec_cat',		( ( ( $params->get( 'section' ) && $row->section ) || ( $params->get( 'category' ) && $row->category ) ) ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'show_page_nav',		( ( $params->get( 'item_navigation' ) && ( $task == 'view' ) && !$params->get( 'popup' ) && ( $row->prev || $row->next ) ) ? 1 : 0 ) );

		// needed to stop {mospagebreak} repeating text when multiple {mosimage}
		$row->images = NULL;

		$tmpl->addObject( 'rows', $row, 'row_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$results = $_MAMBOTS->trigger( 'onAfterDisplayContent', array( &$row, &$params, $page ), true );
		$tmpl->addVar( 'body', 'onAfterDisplayContent',	trim( implode( "\n", $results ) ) );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function list_section( &$params, &$section, &$categories, &$access ) {
		global $_MAMBOTS;
		global $_LANG;

		// process the new bots
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$section, &$params ), true );

		$edit = HTML_content::EditIconSection( $section, $params, $access, 0 );

		$tmpl =& contentScreens_front::createTemplate( 'list-section.html' );

		$tmpl->addVar( 'body', 'edit_icon',				$edit );

		$tmpl->addVar( 'body', 'show_desc_img',			( ( $section->image && $params->get( 'description_image' ) ) ? 1 : 0 ) );

		$tmpl->addObject( 'current', $section, 'cur_' );

		// categories list variables
		$tmpl->addObject( 'categories', $categories, 'cats_' );
		$tmpl->addVar( 'body', 'show_cat',				( ( ( count( $categories ) > 0 ) && $params->get( 'other_cat_section' ) ) ? 1 : 0 ) );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function table_category( &$params, &$category, &$categories, &$access, &$items, &$lists ) {
		global $_MAMBOTS, $Itemid;
		global $_LANG;

		$new_icon_link = 'index.php?option=com_content&amp;task=new&amp;sectionid='. $params->get( 'sectionid' ) .'&amp;Itemid='. $Itemid;
		$new_icon_link = sefRelToAbs( $new_icon_link );

		// process the new bots
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$category, &$params ), true );

		$edit 		= HTML_content::EditIconCategory( $category, $params, $access, 1, 0 );

		$tmpl =& contentScreens_front::createTemplate( 'table-category.html' );

		$tmpl->addVar( 'body', 'show_img',				( ( $category->image && $params->get( 'description_image' ) ) ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'show_desc_img',			( $params->get( 'description' ) && ( $category->image && $params->get( 'description_image' ) ) ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'edit_icon',				$edit );

		$tmpl->addObject( 'current', $category, 'cur_' );

		// categories list variables
		$tmpl->addObject( 'categories', $categories, 'cats_' );
		$tmpl->addVar( 'body', 'show_cat',				( ( ( count( $categories ) > 0 ) && $params->get( 'other_cat' ) ) ? 1 : 0 ) );

		// items table variables
		$tmpl->addObject( 'items', $items, 'item_' );
		$tmpl->addVar( 'body', 'form_url',				'index.php?option=com_content&amp;task=category&amp;sectionid='. $params->get( 'sectionid' ) .'&amp;id='. $params->get( 'catid' ) .'&amp;Itemid='. $Itemid );

		$tmpl->addVar( 'body', 'filter',				$lists['filter'] );
		$tmpl->addVar( 'body', 'order_select',			$lists['order_drop'] );
		$tmpl->addVar( 'body', 'order',					$lists['tOrder'] );


		$tmpl->addVar( 'body', 'order_date',			mosCommonHTML::tOrder( $lists, $_LANG->_( 'Date' ), 'a.created' ) );
		$tmpl->addVar( 'body', 'order_title',			mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'a.title' ) );
		$tmpl->addVar( 'body', 'order_author',			mosCommonHTML::tOrder( $lists, $_LANG->_( 'Author' ), 'created' ) );
		$tmpl->addVar( 'body', 'order_hits',			mosCommonHTML::tOrder( $lists, $_LANG->_( 'Hits' ), 'a.hits' ) );

		$tmpl->addVar( 'body', 'show_new_icon',			( ( $access->canEdit || $access->canEditOwn ) ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'new_icon_link',			$new_icon_link );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function blog( &$params, &$lists, &$description, &$items_leading, &$items_column, &$items_links ) {
		global $_MAMBOTS, $my;

		$tmpl =& contentScreens_front::createTemplate( 'blog.html' );

		$tmpl->addVar( 'body', 'page_links',		$lists['page_links'] );
		$tmpl->addVar( 'body', 'page_counter',		$lists['page_counter'] );

		$tmpl->addObject( 'description', $description, 'des_' );

		$tmpl->addObject( 'leading', $items_leading, 'leading_' );
		$tmpl->addObject( 'column', $items_column, 'column_' );
		$tmpl->addObject( 'link', $items_links, 'link_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>