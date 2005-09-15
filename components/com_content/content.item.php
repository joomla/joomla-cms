<?php
/**
* @version $Id: content.item.php 137 2005-09-12 10:21:17Z eddieajau $
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

class comContentItem {

	function show( $id, $gid, &$access, $pop, $option, $Itemid, $task )
	{
		$this->_display( $id, $gid, $access, $pop, $option, $Itemid, $task );
	}

	function _display( $id, $gid, &$access, $pop, $option, $Itemid, $task )
	{
		switch ( strtolower( $task ) )
		{
			case 'applypop':
			case 'apply':
			case 'savepop':
			case 'save':
				mosCache::cleanCache( 'com_content' );
				$this->saveContent( $access, $task );
				break;

			case 'cancelpop':
			case 'cancel':
				$this->cancelContent( $access, $task );
				break;

			case 'emailsend':
				mosFS::load( 'components/com_content/content.html.php' );
				$this->emailContentSend( $id );
				break;

			case 'vote':
				$this->recordVote();
				break;

			case 'findkey':
				$this->findKeyItem( $gid, $access, $pop, $option );
				break;

			case 'edit':
			case 'editpop':
				mosFS::load( 'components/com_content/content.edit.html.php' );
				$this->editItem( $id, $gid, $access, 0, $task, $Itemid );
				break;

			case 'new':
				mosFS::load( 'components/com_content/content.edit.html.php' );
				$this->editItem( 0, $gid, $access, $sectionid, $task, $Itemid );
				break;

			case 'emailform':
				mosFS::load( 'components/com_content/content.html.php' );
				$this->emailContentForm( $id );
				break;

			case 'view':
			default:
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showItemMain( $id, $gid, $access, $pop, $option );
				break;
		}
	}

	function BlogOutput ( &$rows, &$params, $gid, &$access, $pop, &$menu, $archive=NULL )
	{
		global $mainframe, $Itemid, $task, $id, $option, $database, $mosConfig_live_site, $_LANG;

	/////////////////////////
	$test = 0 ;
	////////////////////////
		// parameters
		if ( $params->get( 'page_title', 1 ) && $menu) {
			$header = $params->def( 'header', $menu->name );
		} else {
			$header = '';
		}
		$columns = $params->def( 'columns', 2 );
		if ( $columns == 0 ) {
			$columns = 1;
		}
		$intro 				= $params->def( 'intro',				4 );
		$leading 			= $params->def( 'leading', 				1 );
		$links 				= $params->def( 'link', 				4 );
		$pagination 		= $params->def( 'pagination', 			2 );
		$pagination_results = $params->def( 'pagination_results', 	1 );
		$descrip		 	= $params->def( 'description', 			1 );
		$descrip_image	 	= $params->def( 'description_image', 	1 );
		$back 				= $params->set( 'back_button', 			$mainframe->getCfg( 'back_button' ) );

		$params->def( 'readmore', 1 );

		// needed to disable back button for item
		$params->set( 'back_button', 	0 );
		$params->def( 'pageclass_sfx', 	'' );
		$params->set( 'intro_only', 	1 );

		$total = count( $rows );

		// pagination support
		$limitstart = intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );
		$limit 		= $intro + $leading + $links;
		if ( $total <= $limit ) {
			$limitstart = 0;
		}
		$i = $limitstart;

		// needed to reduce queries used by getItemid
		$ItemidCount['bs'] 	= $mainframe->getBlogSectionCount();
		$ItemidCount['bc'] 	= $mainframe->getBlogCategoryCount();
		$ItemidCount['gbs']	= $mainframe->getGlobalBlogSectionCount();

		// used to display section/catagory description text and images
		// currently not supported in Archives
		$description = NULL;
		if ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ) {
			switch ( $menu->type ) {
				case 'content_blog_section':
					$description = new mosSection( $database );
					$description->load( $menu->componentid );
					break;

				case 'content_blog_category':
					$description = new mosCategory( $database );
					$description->load( $menu->componentid );
					break;

				default:
					$menu->componentid = 0;
					break;
			}
		}

		$lists['page_links']	= NULL;
		$lists['page_counter']	= NULL;

	////////////////////////////////////////////////////////////////////////////////
		// Page Output

		if ( !$test ) {
			if ( !$total ) {
				// Generic blog empty display
				echo $_LANG->_( 'EMPTY_BLOG' );
				return;
			}

			// page header
			if ( $header ) {
				echo '<div class="componentheading'. $params->get( 'pageclass_sfx' ) .'">'. $header .'</div>';
			}

			// checks to see if there are there any items to display
			if ( $total ) {
				$col_with = 100 / $columns;			// width of each column
				$width = 'width="'. $col_with .'%"';

				echo '<table class="blog' . $params->get( 'pageclass_sfx' ) . '" cellpadding="0" cellspacing="0">';

				// Secrion/Category Description & Image
				if ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ) {
					$link = $mosConfig_live_site .'/images/stories/'. $description->image;
					echo '<tr>';
					echo '<td valign="top">';
					if ( $descrip_image && $description->image ) {
						echo '<img src="'. $link .'" align="'. $description->image_position .'" hspace="6" alt="" />';
					}
					if ( $descrip && $description->description ) {
						echo $description->description;
					}
					echo '<br/><br/>';
					echo '</td>';
					echo '</tr>';
				}

				// Leading story output
				if ( $leading ) {
					echo '<tr>';
					echo '<td valign="top">';
					for ( $z = 0; $z < $leading; $z++ ) {
						if ( $i >= $total ) {
							// stops loop if total number of items is less than the number set to display as leading
							break;
						}
						echo '<div>';
						$this->showItem( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
						echo '</div>';
						$i++;
					}
					echo '</td>';
					echo '</tr>';
				}

				if ( $intro && ( $i < $total ) ) {
					echo '<tr>';
					echo '<td valign="top">';
					echo '<table width="100%"  cellpadding="0" cellspacing="0">';
					// intro story output
					for ( $z = 0; $z < $intro; $z++ ) {
						if ( $i >= $total ) {
							// stops loop if total number of items is less than the number set to display as intro + leading
							break;
						}

						if ( !( $z % $columns ) || $columns == 1 ) {
							echo '<tr>';
							$padclass = '';
						} else {
				  			$padclass = ' class="columnpad"';
						}

						echo '<td valign="top" '. $width . $padclass . '>';

						// outputs either intro or only a link
						if ( $z < $intro ) {
							$this->showItem( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
						} else {
							echo '</td>';
							echo '</tr>';
							break;
						}

						echo '</td>';

						if ( !( ( $z + 1 ) % $columns ) || $columns == 1 ) {
							echo '</tr>';
						}

						$i++;
					}

					// this is required to output a final closing </tr> tag when the number of items does not fully
					// fill the last row of output - a blank column is left
					$intro_items = $i - $leading;
					if ( $intro % $columns ) {
						echo '</tr>';
					} else if ( $intro_items < $columns ) {
					// when number of intro items is less than number of columns
						echo '</tr>';
					}

					echo '</table>';
					echo '</td>';
					echo '</tr>';
				}

				// Links output
				if ( $links && ( $i < $total )  ) {
					echo '<tr>';
					echo '<td valign="top">';
					echo '<div class="blog_more'. $params->get( 'pageclass_sfx' ) .'">';
					HTML_content::showLinks( $rows, $links, $total, $i, 1, $ItemidCount, $params, $access );
					echo '</div>';
					echo '</td>';
					echo '</tr>';
				}

				// Pagination output
				if ( $pagination ) {
					if ( ( $pagination == 2 ) && ( $total <= $limit ) ) {
						// not visible when they is no 'other' pages to display
					} else {
						// get the total number of records
						$limitstart = $limitstart ? $limitstart : 0;
						mosFS::load( '@pageNavigation' );
						$pageNav = new mosPageNav( $total, $limitstart, $limit );

						if ( $option == 'com_frontpage' ) {
							$link = 'index.php?option=com_frontpage&amp;Itemid='. $Itemid;
						} else {
							$link = 'index.php?option=com_content&amp;task='. $task .'&amp;id='. $id .'&amp;Itemid='. $Itemid;
						}
						$lists['page_links']	= $pageNav->writePagesLinks( $link, 0 );
						$lists['page_counter']	= $pageNav->writePagesCounter();


						echo '<tr>';
						echo '<td valign="top" align="center">';
						echo $pageNav->writePagesLinks( $link );
						echo '<br /><br />';
						echo '</td>';
						echo '</tr>';
						if ( $pagination_results ) {
							echo '<tr>';
							echo '<td valign="top" align="center">';
							echo $pageNav->writePagesCounter();
							echo '</td>';
							echo '</tr>';
						}
					}
				}

				echo '</table>';
			}

			// Back Button
			$params->set( 'back_button', $back );

			mosHTML::BackButton ( $params );
		}

	////////////////////////////////////////////////////////////////////////////////
		$params->set( 'show_pag', 		( ( $pagination && !( ( $pagination == 2 ) && ( $total <= $limit ) ) ) ? 1 : 0 ) );
		$params->set( 'show_column', 	( ( $params->get( 'intro' ) && ( $i < $total ) ? 1 : 0 ) ) );
		$params->set( 'show_links', 	( $links && ( $i < $total ) ? 1 : 0 ) );
		$params->set( 'show_sc_desimg', ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ? 1 : 0 ) );
		$params->set( 'show_sc_img', 	( $descrip_image && @$description->image ? 1 : 0 ) );
		$params->set( 'show_sc_des', 	( $descrip && @$description->description ? 1 : 0 ) );
		$params->set( 'show_header', 	( $params->get( 'page_title' ) && $menu ? 1 : 0 ) );


		$items_leading 	= array();
		$items_column 	= array();
		$items_links 	= array();

		for( $i=0; $i < $leading && $i < $total ; $i++ ) {
			$items_leading[$i] = $rows[$i];
		}

		if ( $i < $total ) {
			for( $n=0; $n < $intro && $i < $total ; $n++ ) {
				$items_column[$i] = $rows[$i];
				$i++;
			}
		}

		if ( $i < $total ) {
			for( $n=0; $n < $links && $i < $total ; $n++ ) {
				$_Itemid 	= $mainframe->getItemid( $rows[$i]->id, 0, 0, $ItemidCount['bs'], $ItemidCount['bc'], $ItemidCount['gbs']  );
				$link 		= 'index.php?option=com_content&amp;task=view&amp;id='. $rows[$i]->id .'&amp;Itemid='. $_Itemid;
				$rows[$i]->link	= sefRelToAbs( $link );

				$items_links[$i] = $rows[$i];
				$i++;
			}
		}

		if ( $test ) {
			contentScreens_front::blog( $params, $lists, $description, $items_leading, $items_column, $items_links );
		}
	}

	function BlogOutput_Archive ( &$rows, &$params, $gid, &$access, $pop, &$menu, $archive=NULL )
	{
		global $mainframe, $Itemid, $task, $id, $option, $database, $mosConfig_live_site, $_LANG;

		// parameters
		if ( $params->get( 'page_title', 1 ) && $menu) {
			$header = $params->def( 'header', $menu->name );
		} else {
			$header = '';
		}
		$columns = $params->def( 'columns', 2 );
		if ( $columns == 0 ) {
			$columns = 1;
		}
		$intro 				= $params->def( 'intro',				4 );
		$leading 			= $params->def( 'leading', 				1 );
		$links 				= $params->def( 'link', 				4 );
		$pagination 		= $params->def( 'pagination', 			2 );
		$pagination_results = $params->def( 'pagination_results', 	1 );
		$descrip		 	= $params->def( 'description', 			1 );
		$descrip_image	 	= $params->def( 'description_image', 	1 );
		// needed for back button for page
		$back 				= $params->get( 'back_button', 			$mainframe->getCfg( 'back_button' ) );
		// needed to disable back button for item
		$params->set( 'back_button', 	0 );
		$params->def( 'pageclass_sfx', 	'' );
		$params->set( 'intro_only', 	1 );

		$total = count( $rows );

		// pagination support
		$limitstart = intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );
		$limit 		= $intro + $leading + $links;
		if ( $total <= $limit ) {
			$limitstart = 0;
		}
		$i = $limitstart;

		// needed to reduce queries used by getItemid
		$ItemidCount['bs'] 	= $mainframe->getBlogSectionCount();
		$ItemidCount['bc'] 	= $mainframe->getBlogCategoryCount();
		$ItemidCount['gbs']	= $mainframe->getGlobalBlogSectionCount();

		// used to display section/catagory description text and images
		// currently not supported in Archives
		if ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ) {
			switch ( $menu->type ) {
				case 'content_blog_section':
					$description = new mosSection( $database );
					$description->load( $menu->componentid );
					break;

				case 'content_blog_category':
					$description = new mosCategory( $database );
					$description->load( $menu->componentid );
					break;

				default:
					$menu->componentid = 0;
					break;
			}
		}

		// Page Output
		// page header
		if ( $header ) {
			echo '<div class="componentheading'. $params->get( 'pageclass_sfx' ) .'">'. $header .'</div>';
		}

		if ( $archive ) {
			echo '<br />';
			echo mosHTML::monthSelectList( 'month', 'size="1" class="inputbox"', $params->get( 'month' ) );
			echo mosHTML::integerSelectList( 2000, 2010, 1, 'year', 'size="1" class="inputbox"', $params->get( 'year' ), "%04d" );
			echo '<input type="submit" class="button" />';
		}

		// checks to see if there are there any items to display
		if ( $total ) {
			$col_with = 100 / $columns;			// width of each column
			$width = 'width="'. $col_with .'%"';

			if ( $archive ) {
				// Search Success message
				$msg = $_LANG->sprintf( '_ARCHIVE_SEARCH_SUCCESS', $params->get( 'month' ), $params->get( 'year' ) );
				echo "<br /><br /><div align='center'>". $msg ."</div><br /><br />";
			}
			echo '<table class="blog' . $params->get( 'pageclass_sfx' ) . '" cellpadding="0" cellspacing="0">';

			// Secrion/Category Description & Image
			if ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ) {
				$link = $mosConfig_live_site .'/images/stories/'. $description->image;
				echo '<tr>';
				echo '<td valign="top">';
				if ( $descrip_image && $description->image ) {
					echo '<img src="'. $link .'" align="'. $description->image_position .'" hspace="6" alt="" />';
				}
				if ( $descrip && $description->description ) {
					echo $description->description;
				}
				echo '<br/><br/>';
				echo '</td>';
				echo '</tr>';
			}

			// Leading story output
			if ( $leading ) {
				echo '<tr>';
				echo '<td valign="top">';
				for ( $z = 0; $z < $leading; $z++ ) {
					if ( $i >= $total ) {
						// stops loop if total number of items is less than the number set to display as leading
						break;
					}
					echo '<div>';
					$this->showItem( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
					echo '</div>';
					$i++;
				}
				echo '</td>';
				echo '</tr>';
			}

			if ( $intro && ( $i < $total ) ) {
				echo '<tr>';
				echo '<td valign="top">';
				echo '<table width="100%"  cellpadding="0" cellspacing="0">';
				// intro story output
				for ( $z = 0; $z < $intro; $z++ ) {
					if ( $i >= $total ) {
						// stops loop if total number of items is less than the number set to display as intro + leading
						break;
					}

					if ( !( $z % $columns ) || $columns == 1 ) {
						echo '<tr>';
						$padclass = '';
					} else {
			  			$padclass = ' class="columnpad"';
					}

					echo '<td valign="top" '. $width . $padclass . '>';

					// outputs either intro or only a link
					if ( $z < $intro ) {
						$this->showItem( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
					} else {
						echo '</td>';
						echo '</tr>';
						break;
					}

					echo '</td>';

					if ( !( ( $z + 1 ) % $columns ) || $columns == 1 ) {
						echo '</tr>';
					}

					$i++;
				}

				// this is required to output a final closing </tr> tag when the number of items does not fully
				// fill the last row of output - a blank column is left
				$intro_items = $i - $leading;
				if ( $intro % $columns ) {
					echo '</tr>';
				} else if ( $intro_items < $columns ) {
				// when number of intro items is less than number of columns
					echo '</tr>';
				}

				echo '</table>';
				echo '</td>';
				echo '</tr>';
			}

			// Links output
			if ( $links && ( $i < $total )  ) {
				echo '<tr>';
				echo '<td valign="top">';
				echo '<div class="blog_more'. $params->get( 'pageclass_sfx' ) .'">';
				HTML_content::showLinks( $rows, $links, $total, $i, 1, $ItemidCount, $params, $access );
				echo '</div>';
				echo '</td>';
				echo '</tr>';
			}

			// Pagination output
			if ( $pagination ) {
				if ( ( $pagination == 2 ) && ( $total <= $limit ) ) {
					// not visible when they is no 'other' pages to display
				} else {
					// get the total number of records
					$limitstart = $limitstart ? $limitstart : 0;
					mosFS::load( '@pageNavigation' );
					$pageNav = new mosPageNav( $total, $limitstart, $limit );
					if ( $option == 'com_frontpage' ) {
						$link = 'index.php?option=com_frontpage&amp;Itemid='. $Itemid;
					} else if ( $archive ) {
						$year = $params->get( 'year' );
						$month = $params->get( 'month' );
						$link = 'index.php?option=com_content&amp;task='. $task .'&amp;id='. $id .'&amp;Itemid='. $Itemid.'&amp;year='. $year .'&amp;month='. $month;
					} else {
						$link = 'index.php?option=com_content&amp;task='. $task .'&amp;id='. $id .'&amp;Itemid='. $Itemid;
					}
					echo '<tr>';
					echo '<td valign="top" align="center">';
					echo $pageNav->writePagesLinks( $link );
					echo '<br /><br />';
					echo '</td>';
					echo '</tr>';
					if ( $pagination_results ) {
						echo '<tr>';
						echo '<td valign="top" align="center">';
						echo $pageNav->writePagesCounter();
						echo '</td>';
						echo '</tr>';
					}
				}
			}

			echo '</table>';

		} else if ( $archive && !$total ) {
			// Search Failure message for Archives
			$msg = $_LANG->sprintf( '_ARCHIVE_SEARCH_FAILURE', $params->get( 'month' ), $params->get( 'year' ) );
			echo '<br /><br /><div align="center">'. $msg .'</div><br />';
		} else {
			// Generic blog empty display
			echo $_LANG->_( 'EMPTY_BLOG' );
		}

		// Back Button
		$params->set( 'back_button', $back );

		mosHTML::BackButton ( $params );
	}

	function showItemMain( $uid, $gid, &$access, $pop, $option )
	{
		global $database, $mainframe;
		global $mosConfig_live_site, $mosConfig_MetaTitle, $mosConfig_MetaAuthor, $mosConfig_zero_date;

		$now = $mainframe->getDateTime();
		if ( $access->canEdit ) {
			$xwhere='';
		} else {
			$xwhere = "AND ( a.state = '1' OR a.state = '-1' )"
			. "\n	AND (publish_up = '$mosConfig_zero_date' OR publish_up <= '$now')"
			. "\n	AND (publish_down = '$mosConfig_zero_date' OR publish_down >= '$now')"
			;
		}

		$query = "SELECT a.*, v.rating_sum, v.rating_count, u.name AS author, u.usertype, cc.name AS category, s.name AS section, g.name AS groups"
		. "\n FROM #__content AS a"
		. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. "\n WHERE a.id = '$uid'"
		. $xwhere
		. "\n AND a.access <= $gid"
		;
		$database->setQuery( $query );
		$row = NULL;

		if ( $database->loadObject( $row ) ) {
			$params = new mosParameters( $row->attribs );
			$params->set( 'intro_only', 0 );
			$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
			if ( $row->sectionid == 0) {
				$params->set( 'item_navigation', 0 );
			} else {
				$params->set( 'item_navigation', $mainframe->getCfg( 'item_navigation' ) );
			}
			// loads the links for Next & Previous Button
			if ( $params->get( 'item_navigation' ) ) {
				$query = "SELECT a.id"
				. "\n FROM #__content AS a"
				. "\n WHERE a.catid = $row->catid"
				. "\n AND a.state = $row->state AND ordering < $row->ordering"
				. ( $access->canEdit ? '' : "\n AND a.access <= '$gid'" )
				. "\n ORDER BY a.ordering DESC"
				;
				$database->setQuery( $query, 0, 1 );
				$row->prev = $database->loadResult();

				$query = "SELECT a.id"
				. "\n FROM #__content AS a"
				. "\n WHERE a.catid = $row->catid"
				. "\n AND a.state = $row->state AND ordering > $row->ordering"
				. ( $access->canEdit ? '' : "\n AND a.access <= '$gid'" )
				. "\n ORDER BY a.ordering"
				;
				$database->setQuery( $query, 0, 1 );
				$row->next = $database->loadResult();
			}
			// page title
			$mainframe->setPageTitle( $row->title );
			if ( $mosConfig_MetaTitle == 1 ) {
				$mainframe->addMetaTag( 'title' , $row->title );
			}
			if ( $mosConfig_MetaAuthor == 1 ) {
				$mainframe->addMetaTag( 'author' , $row->author );
			}

			$this->showItem( $row, $params, $gid, $access, $pop, $option );
		} else {
			mosNotAuth();
			return;
		}
	}

	function showItem( $row, $params, $gid, &$access, $pop, $option, $ItemidCount=NULL )
	{
		//echo "CACHE_ID INTRO=".$params->get( 'intro_only' )." ROW=".$row->id;
		$cache  = mosFactory::getCache( "com_content" );
		$cache->callId(
			"comContentItem::_showItem",
			array( $row, $params, $gid, $access, $pop, $option, $ItemidCount ),
			$params->get( 'intro_only' ).$row->id
			);
	}

	function _showItem( $row, $params, $gid, &$access, $pop, $option, $ItemidCount=NULL )
	{
		//echo "intro"; return;
		global $database, $mainframe, $Itemid;
		global $mosConfig_live_site, $mosConfig_absolute_path, $mosConfig_sitename;
		global $options, $cache;
		global $task, $_LANG;

		$noauth = !$mainframe->getCfg( 'shownoauth' );

		if ( $access->canEdit ) {
			if ( $row->id === null || $row->access > $gid ) {
				mosNotAuth();
				return;
			}
		} else {
			if ( $row->id === null || $row->state == 0 ) {
				mosNotAuth();
				return;
			}
			if ( $row->access > $gid ) {
				if ( $noauth ) {
					mosNotAuth();
					return;
				} else {
					if ( !( $params->get( 'intro_only' ) ) ) {
						mosNotAuth();
						return;
					}
				}
			}
		}

		// GC Parameters
		$params->def( 'link_titles', 	$mainframe->getCfg( 'link_titles' ) );
		$params->def( 'author', 		!$mainframe->getCfg( 'hideAuthor' ) );
		$params->def( 'createdate', 	!$mainframe->getCfg( 'hideCreateDate' ) );
		$params->def( 'modifydate', 	!$mainframe->getCfg( 'hideModifyDate' ) );
		$params->def( 'print', 			!$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'pdf', 			!$mainframe->getCfg( 'hidePdf' ) );
		$params->def( 'email', 			!$mainframe->getCfg( 'hideEmail' ) );
		$params->def( 'rating', 		$mainframe->getCfg( 'vote' ) );
		$params->def( 'icons', 			$mainframe->getCfg( 'icons' ) );
		$params->def( 'readmore', 		$mainframe->getCfg( 'readmore' ) );
		// Other Params
		$params->def( 'image', 			1 );
		$params->def( 'section', 		0 );
		$params->def( 'section_link', 	0 );
		$params->def( 'category', 		0 );
		$params->def( 'category_link', 	0 );
		$params->def( 'introtext', 		1 );
		$params->def( 'pageclass_sfx', 	'' );
		$params->def( 'item_title', 	1 );
		$params->def( 'url', 			1 );

		// loads the link for Section name
		if ( $params->get( 'section_link' ) && $row->section ) {
			$query = 	"SELECT a.id"
			. "\n FROM #__menu AS a"
			. "\n WHERE a.componentid = ". $row->sectionid.""
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
			$link = sefRelToAbs( 'index.php?option=com_content&amp;task=section&amp;id='. $row->sectionid .'&amp;Itemid='.$_Itemid );
			$row->section = '<a href="'. $link .'">'. $row->section .'</a>';
		}

		// loads the link for Category name
		if ( $params->get( 'category_link' ) && $row->category ) {
			$query = 	"SELECT a.id"
			. "\n FROM #__menu AS a"
			. "\n WHERE a.componentid = ". $row->catid.""
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
			$link = sefRelToAbs( 'index.php?option=com_content&amp;task=category&amp;sectionid='. $row->sectionid .'&amp;id='. $row->catid .'&amp;Itemid='.$_Itemid );
			$row->category = '<a href="'. $link .'">'. $row->category .'</a>';
		}

		// loads current template for the pop-up window
		$template = '';
		if ( $pop ) {
			$params->set( 'popup', 1 );
			$template = $mainframe->getTemplate();
		} else {
			$params->set( 'popup', 0 );
		}

		// show/hides the intro text
		if ( $params->get( 'introtext'  ) ) {
			$row->text = $row->introtext. ( $params->get( 'intro_only' ) ? '' : chr(13) . chr(13) . $row->fulltext);
		} else {
			$row->text = $row->fulltext;
		}

		// deal with the {mospagebreak} mambots
		// only permitted in the full text area
		$page = intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );

		// record the hit
		if ( !$params->get( 'intro_only' ) ) {
			$obj = new mosContent( $database );
			$obj->hit( $row->id );
		}

		// & xhtml compliance conversion
		$row->title = ampReplace( $row->title );

		if ( $task == 'view' || $params->get( 'content_meta', 0 ) ) {
			// meta tag for content item
			if ( $row->metadesc ) {
				$mainframe->appendMetaTag( 'description', $row->metadesc );
			}
			if ( $row->metakey ) {
				$mainframe->appendMetaTag( 'keywords', $row->metakey );
			}
		}

		$row->link_text = NULL;
		$row->link_on 	= NULL;
		// determines the link and link text of the readmore button
		if ( $params->get( 'intro_only' ) ) {
			// checks if the item is a public or registered/special item
			if ( $row->access <= $gid ) {
				if ( $task != 'view' ) {
					$_Itemid = $mainframe->getItemid( $row->id, 0, 0, $ItemidCount['bs'], $ItemidCount['bc'], $ItemidCount['gbs'] );
				}
				$row->link_on = sefRelToAbs( 'index.php?option=com_content&amp;task=view&amp;id='. $row->id .'&amp;Itemid='. $_Itemid );
				if ( strlen( trim( $row->fulltext ) ) ) {
					$row->link_text = $_LANG->_( 'Read more' );
				}
			} else {
				$row->link_on = sefRelToAbs( 'index.php?option=com_registration&amp;task=register' );
				if ( strlen( trim( $row->fulltext ) ) ) {
					$row->link_text = $_LANG->_( 'Read more register' );
				}
			}
		}

		$row->create_date = NULL;
		if ( intval( $row->created ) != 0 ) {
			$row->create_date = mosFormatDate( $row->created );
		}

		$row->mod_date 	= null;
		if ( intval( $row->modified ) != 0) {
			$row->mod_date = mosFormatDate( $row->modified );
		}

		if ( $row->created_by_alias ) {
			$row->author = $row->created_by_alias;
		} else {
			$row->author = ( isset( $row->author ) ? $row->author : NULl );
		}

		// determines links to next and prev content items within category
		$row->prev = NULL;
		$row->next = NULL;
		if ( $params->get( 'item_navigation' ) ) {
			if ( $row->prev ) {
				$row->prev = sefRelToAbs( 'index.php?option=com_content&amp;task=view&amp;id='. $row->prev .'&amp;Itemid='. $_Itemid );
			} else {
				$row->prev = 0;
			}
			if ( $row->next ) {
				$row->next = sefRelToAbs( 'index.php?option=com_content&amp;task=view&amp;id='. $row->next .'&amp;Itemid='. $_Itemid );
			} else {
				$row->next = 0;
			}
		}

		// for pop-up page
		$no_html = mosGetParam( $_REQUEST, 'no_html', null);
		if ( $params->get( 'popup' ) && $no_html == 0 ) {
			$mainframe->SetPageTitle( $mosConfig_sitename .' :: '. $row->title );
		}

		//$cache->call( 'HTML_content::show', $row, $params, $access, $page, $option, $ItemidCount );
		contentScreens_front::item( $row, $params, $access, $page, $option, $ItemidCount );
	}


	function editItem( $uid, $gid, &$access, $sectionid=0, $task, $Itemid )
	{
		global $database, $mainframe, $my, $_LANG;
		global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_zero_date;

		mosFS::load( '@class', 'com_components' );

		$row = new mosContent( $database );
		// load the row from the db table
		$row->load( $uid );

		// fail if checked out not by 'me'
		if ( $row->isCheckedOut() ) {
			$text = $_LANG->_( 'The Content Item is currently being edited by another person.' );
			mosErrorAlert( $text );
		}

		if ( $uid ) {
			// existing record
			if ( !( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) ) {
				mosNotAuth();
				return;
			}
		} else {
			// new record
			if (!($access->canEdit || $access->canEditOwn)) {
				mosNotAuth();
				return;
			}
		}

		if ( $uid ) {
			$sectionid = $row->sectionid;
		}

		$lists = array();

		// get the type name - which is a special category
		$query = "SELECT name"
		. "\n FROM #__sections"
		. "\n WHERE id = '$sectionid'"
		;
		$database->setQuery( $query );
		$section = $database->loadResult();

		if ( $uid == 0 ) {
			$row->catid = 0;
		}

		if ( $uid ) {
			// checkout item
			$row->checkout( $my->id );

			if (trim( $row->publish_down ) == $mosConfig_zero_date) {
				$row->publish_down = 'Never';
			}

			if (trim( $row->images )) {
				$row->images = htmlentities( $row->images );
				$row->images = explode( "\n", $row->images );
			} else {
				$row->images = array();
			}

			$query = "SELECT name"
			."\n FROM #__users"
			. "\n WHERE id = $row->created_by"
			;
			$database->setQuery( $query	);
			$row->creator = $database->loadResult();

			$query = "SELECT name"
			."\n FROM #__users"
			. "\n WHERE id = $row->modified_by"
			;
			$database->setQuery( $query );
			$row->modifier = $database->loadResult();

			$query = "SELECT content_id"
			."\n FROM #__content_frontpage"
			."\n WHERE content_id = $row->id"
			;
			$database->setQuery( $query );
			$row->frontpage = $database->loadResult();

			$row->metadesc 	= ampReplace( $row->metadesc );
			$row->metakey 	= ampReplace( $row->metakey );
		} else {
			$row->sectionid 	= $sectionid;
			$row->version 		= 0;
			$row->state 		= 0;
			$row->ordering 		= 0;
			$row->images 		= array();
			$row->publish_up 	= date( 'Y-m-d', time() );
			$row->publish_down 	= 'Never';
			$row->creator 		= 0;
			$row->modifier 		= 0;
			$row->frontpage 	= 0;
		}

		$Returnid 		= intval( mosGetParam( $_REQUEST, 'Returnid', $Itemid ) );
		$row->return 	= $Returnid;

		// calls function to read image from directory
		$pathA 		= $mosConfig_absolute_path .'/images/stories';
		$pathL 		= $mosConfig_live_site .'/images/stories';
		$images 	= array();
		$folders 	= array();
		$folders[] 	= mosHTML::makeOption( '/' );
		mosAdminMenus::ReadImages( $pathA, '/', $folders, $images );
		// list of folders in images/stories/
		$lists['folders'] 		= mosAdminMenus::GetImageFolders( $folders, $pathL );
		// list of images in specfic folder in images/stories/
		$lists['imagefiles']	= mosAdminMenus::GetImages( $images, $pathL );
		// list of saved images
		$lists['imagelist'] 	= mosAdminMenus::GetSavedImages( $row, $pathL );

		// build list of categories
		$catid = intval( mosGetParam( $_REQUEST, 'cid', '0' ) );
		if ( $catid ) {
			$row->catid = $catid;
		}
		$lists['catid'] 		= mosComponentFactory::buildCategoryList( 'catid', $sectionid, intval( $row->catid ) );

		// build the html select list for the group access
		$lists['access'] 		= mosAdminMenus::Access( $row );

		// make the select list for the states
		$lists['state'] 		= mosHTML::yesnoRadioList( 'state', 'class="inputbox" size="1"', intval( $row->state ) );

		// build the html select list for ordering
		$query = "SELECT ordering AS value, title AS text"
		. "\n FROM #__content"
		. "\n WHERE catid = '$row->catid'"
		. "\n ORDER BY ordering"
		;
		$lists['ordering'] 		= mosAdminMenus::SpecificOrdering( $row, $uid, $query, 1 );

		// build the select list for the image positions
		$lists['_align'] 		= mosAdminMenus::Positions( '_align' );
		// build the select list for the image caption alignment
		$lists['_caption_align'] 	= mosAdminMenus::Positions( '_caption_align' );

		// build the select list for the image caption position
		$pos[] = mosHTML::makeOption( 'bottom', $_LANG->_( 'Bottom' ) );
		$pos[] = mosHTML::makeOption( 'top', $_LANG->_( 'Top' ) );
		$lists['_caption_position'] = mosHTML::selectList( $pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text' );

		// build the select list for the link target
		$target[] = mosHTML::makeOption( '_blank', $_LANG->_( 'New Window' ) );
		$target[] = mosHTML::makeOption( '_self', $_LANG->_( 'Parent Window' ) );
		$lists['_link_target'] = mosHTML::selectList( $target, '_link_target', 'class="inputbox" size="1"', 'value', 'text' );

		$lists['section'] 	= $section;
		$lists['sectionid'] = $sectionid;

		//toolbar css file
		$css = $mosConfig_live_site .'/includes/HTML_toolbar.css';
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="'. $css .'" type="text/css" />' );

		mosFS::load( '@toolbar_front' );

		contentEditScreens_front::editContent( $row, $task, $lists, $access, $images  );
	}

	/**
	 * Saves the content item an edit form submit
	 */
	function saveContent( &$access, $task )
	{
		global $database, $mainframe, $my, $_LANG;
		global $mosConfig_absolute_path, $mosConfig_zero_date;

		$row = new mosContent( $database );

		if (!$row->bind( $_POST )) {
			mosErrorAlert( $row->getError() );
		}

		$isNew = $row->id < 1;

		if ( $isNew ) {
			// new record
			if (!( $access->canEdit || $access->canEditOwn )) {
				mosNotAuth();
				return;
			}
			$row->created = date( 'Y-m-d H:i:s' );
			$row->created_by = $my->id;
		} else {
			// existing record
			if (!( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) )) {
				mosNotAuth();
				return;
			}
			$row->modified = date( 'Y-m-d H:i:s' );
			$row->modified_by = $my->id;
		}
		if ( trim( $row->publish_down ) == 'Never' ) {
			$row->publish_down = $mosConfig_zero_date;
		}

		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}
		$row->version++;
		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}

		// manage frontpage items
		require_once( $mainframe->getPath( 'class', 'com_frontpage' ) );
		$fp = new mosFrontPage( $database );

		if (mosGetParam( $_REQUEST, 'frontpage', 0 )) {

			// toggles go to first place
			if ( !$fp->load( $row->id ) ) {
				// new entry
				$query = "INSERT INTO #__content_frontpage VALUES ('$row->id','1')";
				$database->setQuery( $query );
				if ( !$database->query() ) {
					mosErrorAlert( $database->stderr() );
				}
				$fp->ordering = 1;
			}
		} else {
			// no frontpage mask
			if (!$fp->delete( $row->id )) {
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->updateOrder();

		$row->checkin();
		$row->updateOrder( "catid = '$row->catid'" );

		// gets section name of item
		$query = "SELECT s.title"
		. "\n FROM #__sections AS s"
		. "\n WHERE s.scope = 'content'"
		. "\n AND s.id = '$row->sectionid'"
		;
		$database->setQuery( $query );
		// gets category name of item
		$section = $database->loadResult();
		$query = "SELECT c.title"
		. "\n FROM #__categories AS c"
		. "\n WHERE c.id = '$row->catid'"
		;
		$database->setQuery( $query );
		$category = $database->loadResult();

		if ($isNew) {
			// messaging for new items
			mosFS::load( 'components/com_messages/messages.class.php' );

			$query = "SELECT id"
			. "\n FROM #__users"
			. "\n WHERE sendEmail = '1'";
			$database->setQuery( $query );
			$users = $database->loadResultArray();
			foreach ( $users as $user_id ) {
				$msg = new mosMessage( $database );
				$msg->send( $my->id, $user_id, 'New Item', $_LANG->sprintf( '_ON_NEW_CONTENT', $my->username, $row->title, $section, $category ) );
			}
		}

	 	$msg 	= $isNew ? $_LANG->_( 'THANK_SUB' ) : $_LANG->_( 'E_ITEM_SAVED' );
		$Itemid = mosGetParam( $_POST, 'Returnid', '0' );
		switch ( $task ) {
			case 'savepop':
				?>
				<script language="javascript" type="text/javascript">
				<!--
				onLoad = window.close( 'win1' )
				// reload main window
				opener.location.reload();
				//-->
				</script>
				<?php
				exit();
				break;

			case 'applypop':
				$link = sefRelToAbs( 'index2.php?option=com_content&task=editpop&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid );
				mosRedirect( $link, $msg );

			case 'apply':
				$link = sefRelToAbs( 'index.php?option=com_content&task=edit&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid );
				mosRedirect( $link, $msg );

			case 'save':
			default:
				$referer	= mosGetParam( $_POST, 'referer', '' );
				if ( $referer ) {
					mosRedirect( $referer, $msg );
				} else {
					$link = sefRelToAbs( 'index.php?option=com_content&task=view&id='. $row->id .'&Itemid='. $Itemid );
					mosRedirect( $link, $msg );
				}
				break;
		}
	}

	/**
	* Cancels an edit operation
	* @param database A database connector object
	*/
	function cancelContent( &$access, $task )
	{
		global $database, $mainframe, $my;

		if ( $my->gid < 1 ) {
			mosNotAuth();
			return;
		}

		$row = new mosContent( $database );
		$row->id = intval( mosGetParam( $_POST, 'id', 0 ) );

		if ( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) {
			$row->checkin();
		}

		if ( $task == 'cancelpop' ) {
			?>
			<script language="javascript" type="text/javascript">
			<!--
			onLoad = window.close( 'win1' )
			// reload main window
			//opener.location.reload();
			//-->
			</script>
			<?php
			exit();
		} else {
			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer && !strstr( $referer, 'task=edit' ) && !strstr( $referer, 'task=new' ) ) {
				mosRedirect( $referer, $msg );
			} else {
				$Itemid 	= mosGetParam( $_POST, 'Returnid', '0' );
				if ( $Itemid ) {
					$link = sefRelToAbs( 'index.php?option=com_content&task=view&id='. $row->id .'&Itemid='. $Itemid );
					mosRedirect( $link );
				} else {
					$link = sefRelToAbs( 'index.php' );
					mosRedirect( $link );
				}
			}
		}
	}

	/**
	* Shows the email form for a given content item.
	*/
	function emailContentForm( $id )
	{
		global $database, $mainframe, $my, $mosConfig_sitename;

		$row = new mosContent( $database );
		$row->load( $id );

		if ( $row->id === null || $row->access > $my->gid ) {
			mosNotAuth();
			return;
		} else {
			$template='';
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = '0'"
			. "\n AND menuid = '0'"
			;
			$database->setQuery( $query );
			$template = $database->loadResult();

			$mainframe->SetPageTitle( $mosConfig_sitename .' :: '. $row->title );
			$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="templates/'. $template .'/css/template_css.css" type="text/css" />' );

			contentScreens_front::email( $row->id );
		}

	}

	/**
	* Shows the email form for a given content item.
	*/
	function emailContentSend( $uid )
	{
		global $database, $mainframe, $_LANG;
		global $mosConfig_live_site, $mosConfig_sitename;
		global $mosConfig_mailfrom, $mosConfig_fromname;

		$_Itemid = $mainframe->getItemid( $uid, 0, 0  );

		$email 				= trim( mosGetParam( $_POST, 'email', '' ) );
		$yourname 			= trim( mosGetParam( $_POST, 'yourname', '' ) );
		$youremail 			= trim( mosGetParam( $_POST, 'youremail', '' ) );
		$subject_default 	= $_LANG->_( 'EMAIL_INFO' ) ." $yourname";
		$subject 			= trim( mosGetParam( $_POST, 'subject', $subject_default ) );

		if ( !$email || !$youremail || ( is_email( $email ) == false ) || ( is_email( $youremail ) == false ) ) {
			mosErrorAlert( $_LANG->_( 'EMAIL_ERR_NOINFO' ) );
		}

		// link sent in email
		$link = sefRelToAbs( $mosConfig_live_site .'/index.php?option=com_content&task=view&id='. $uid .'&Itemid='. $_Itemid );

		// message text
		$msg = $_LANG->sprintf( '_EMAIL_MSG', $mosConfig_sitename, $yourname, $youremail, $link );

		// mail function
		mosMail( $youremail, $yourname, $email, $subject, $msg );

		// set page <head> info
		$mainframe->SetPageTitle( $mosConfig_sitename .' :: '. $_LANG->_( 'Email Sent' ) );
		$template = $mainframe->getTemplate();
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="templates/'. $template .'/css/template_css.css" type="text/css" />' );

		contentScreens_front::emailSent( $email );
	}

	function recordVote()
	{
		global $database, $_LANG;

		$cid = mosGetParam( $_POST, 'cid', 0 );
		$url = mosGetParam( $_POST, 'url', '' );
		$user_rating = mosGetParam( $_POST, 'user_rating', 0 );

		$cid = intval( $cid );
		$url = InputFilter::decode( $url );
		if (InputFilter::badAttributeValue( $url )) {
			mosRedirect( $url, $_LANG->_( 'THANKS' ) ); // hack attempt
		}
		$user_rating = intval( $user_rating );

		if ( ( $user_rating >= 1 ) and ( $user_rating <= 5 ) ) {
			$currip = getenv( 'REMOTE_ADDR' );

			$query = "SELECT * FROM #__content_rating WHERE content_id = $cid";
			$database->setQuery( $query );
			$votesdb = NULL;

			if ( !( $database->loadObject( $votesdb ) ) ) {
				$query = "INSERT INTO #__content_rating ( content_id, lastip, rating_sum, rating_count )"
				. "\n VALUES ( '$cid', '$currip', '$user_rating', '1' )";
				$database->setQuery( $query );
				$database->query() or die( $database->stderr() );
			} else {
				if ($currip <> ($votesdb->lastip)) {
					$query = "UPDATE #__content_rating"
					. "\n SET rating_count = rating_count + 1,"
					. "\n rating_sum = rating_sum + $user_rating,"
					. "\n lastip = '$currip'"
					. "\n WHERE content_id = ". $cid
					;
					$database->setQuery( $query );
					$database->query() or die( $database->stderr() );
				} else {
					mosRedirect( $url, $_LANG->_( 'ALREADY_VOTE' ) );
				}
			}

			mosRedirect ( $url, $_LANG->_( 'THANKS' ) );
		}
	}

	/**
	 * Searches for an item by a key parameter
	 * @param int The user access level
	 * @param object Actions this user can perform
	 * @param int
	 * @param string The url option
	 * @param string A timestamp
	 */
	function findKeyItem( $gid, $access, $pop, $option )
	{
		global $database;
		$keyref = mosGetParam( $_REQUEST, 'keyref', '' );
		$keyref = $database->getEscaped( $keyref );

		mosFS::load( 'components/com_content/content.item.php' );
		$ccItem = new comContentItem();

		$query = 'SELECT id
			FROM #__content
			WHERE attribs LIKE \'%keyref=' . $keyref . '%\'
			';
		$database->setQuery( $query );
		$id = $database->loadResult();
		if ($id > 0) {
			$this->showItemMain( $id, $gid, $access, $pop, $option );
		} else {
			echo 'Key not found';
		}
	}
}
?>
