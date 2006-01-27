<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.0
 */
class JContentView
{
	/**
	 * Draws a Content List Used by Content Category & Content Section
	 * 
	 * @since 1.1
	 */
	function showSection(& $section, & $categories, & $params, & $access, $gid)
	{
		global $Itemid;

		if ($params->get('page_title'))
		{
		?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $section->name; ?>
			</div>
		<?php
		}
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
		<?php
		if ($section->image)
		{
			$link = 'images/stories/'.$section->image;
			?>
				<img src="<?php echo $link;?>" align="<?php echo $section->image_position;?>" hspace="6" alt="<?php echo $section->image;?>" />
		<?php
		}
		echo $section->description;
		?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
		<?php
		// Displays listing of Categories
		if (count($categories) > 0)
		{
			if ($params->get('other_cat_section'))
			{
				JContentView :: showCategories($params, new stdClass(), $gid, $categories, new stdClass(), $section->id, $Itemid);
			}
		}
		?>
			</td>
		</tr>
		</table>
		<?php
		// displays back button
		mosHTML :: BackButton($params);
	}

	/**
	* Draws a Content List
	* Used by Content Category & Content Section
	*/
	function showCategory(& $category, & $other_categories, & $items, & $access, $gid, & $params, & $page, & $lists, $order)
	{
		global $Itemid;

		if ($params->get('page_title'))
		{
		?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->name; ?>
			</div>
		<?php
		}
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
		<?php
		if ($category->image)
		{
			$link = '/images/stories/'.$category->image;
			?>
				<img src="<?php echo $link;?>" align="<?php echo $category->image_position;?>" hspace="6" alt="<?php echo $category->image;?>" />
		<?php
		}
		echo $category->description;
		?>
			</td>
		</tr>
		<tr>
			<td>
		<?php
		// Displays the Table of Items in Category View
		if (count($items))
		{
			JContentView :: showTable($params, $items, $gid, $category->id, $category->id, $page, $access, $category->sectionid, $lists, $order);
		} else
			if ($category->id)
			{
			?>
				<br />
				<?php echo JText::_( 'This Category is currently empty' ); ?>
				<br /><br />
			<?php
			}
			?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
		<?php
		// Displays listing of Categories
		if (count($other_categories) > 0)
		{
			if ($params->get('other_cat'))
			{
				JContentView :: showCategories($params, $items, $gid, $other_categories, $category->id, $category->id, $Itemid);
			}
		}
		?>
			</td>
		</tr>
		</table>
		<?php
		// displays back button
		mosHTML :: BackButton($params);
	}

	function showArchive(&$rows, &$params, &$menu, &$access, $id, $gid, $pop)
	{
		global $Itemid;
		
		/*
		 * Initialize variables
		 */
		$task = JRequest::getVar('task');
		
		// initiate form
		$link = 'index.php?option=com_content&task='.$task.'&id='.$id.'&Itemid='.$Itemid;
		echo '<form action="'.sefRelToAbs($link).'" method="post">';

		JContentView :: showBlog($rows, $params, $gid, $access, $pop, $menu, ($id) ? 1 : 0 );
		
		echo '<input type="hidden" name="id" value="'.$id.'" />';
		echo '<input type="hidden" name="Itemid" value="'.$Itemid.'" />';
		echo '<input type="hidden" name="task" value="'.$task.'" />';
		echo '<input type="hidden" name="option" value="com_content" />';
		echo '</form>';
	}

	function showBlog($rows, $params, $gid, $access, $pop, $menu, $archive = null)
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db 	= & $mainframe->getDBO();
		$my 	= & $mainframe->getUser();
		$task 	= JRequest::getVar( 'task' );
		$id 	= JRequest::getVar( 'id' );
		$option = JRequest::getVar( 'option' );

		// parameters
		if ($params->get('page_title', 1) && $menu)
		{
			$header = $params->def('header', $menu->name);
		} else
		{
			$header = '';
		}
		$columns = $params->def('columns', 2);
		if ($columns == 0)
		{
			$columns = 1;
		}
		$intro = $params->def('intro', 4);
		$leading = $params->def('leading', 1);
		$links = $params->def('link', 4);
		$pagination = $params->def('pagination', 2);
		$pagination_results = $params->def('pagination_results', 1);
		$pagination_results = $params->def('pagination_results', 1);
		$descrip = $params->def('description', 1);
		$descrip_image = $params->def('description_image', 1);
		// needed for back button for page
		$back = $params->get('back_button', $mainframe->getCfg('back_button'));
		// needed to disable back button for item
		$params->set('back_button', 0);
		$params->def('pageclass_sfx', '');
		$params->set('intro_only', 1);

		$total = count($rows);

		// pagination support
		$limitstart = JRequest::getVar( 'limitstart', 0, '', 'int' );
		$limit = $intro + $leading + $links;
		if ($total <= $limit)
		{
			$limitstart = 0;
		}
		$i = $limitstart;

		// needed to reduce queries used by getItemid
		$ItemidCount['bs'] = JApplicationHelper :: getBlogSectionCount();
		$ItemidCount['bc'] = JApplicationHelper :: getBlogCategoryCount();
		$ItemidCount['gbs'] = JApplicationHelper :: getGlobalBlogSectionCount();

		// used to display section/catagory description text and images
		// currently not supported in Archives
		if ($menu && $menu->componentid && ($descrip || $descrip_image))
		{
			switch ($menu->type)
			{
				case 'content_blog_section' :
					$description = & JModel :: getInstance( 'section', $db );
					$description->load($menu->componentid);
					break;

				case 'content_blog_category' :
					$description = & JModel :: getInstance( 'category', $db );
					$description->load($menu->componentid);
					break;

				default :
					$menu->componentid = 0;
					break;
			}
		}

		// Page Output
		// page header
		if ($header)
		{
			echo '<div class="componentheading'.$params->get('pageclass_sfx').'">'.$header.'</div>';
		}

		if ($archive)
		{
			echo '<br />';
			echo mosHTML :: monthSelectList('month', 'size="1" class="inputbox"', $params->get('month'));
			echo mosHTML :: integerSelectList(2000, 2010, 1, 'year', 'size="1" class="inputbox"', $params->get('year'), "%04d");
			echo '<input type="submit" class="button" />';
		}

		// checks to see if there are there any items to display
		if ($total)
		{
			$col_width = 100 / $columns; // width of each column
			$width = 'width="'.intval($col_width).'%"';

			if ($archive)
			{
				// Search Success message
				$msg = sprintf(JText :: _('ARCHIVE_SEARCH_SUCCESS'), $params->get('month'), $params->get('year'));
				echo "<br /><br /><div align='center'>".$msg."</div><br /><br />";
			}
			echo '<table class="blog'.$params->get('pageclass_sfx').'" cellpadding="0" cellspacing="0">';

			// Secrion/Category Description & Image
			if ($menu && $menu->componentid && ($descrip || $descrip_image))
			{
				$link = '/images/stories/'.$description->image;
				echo '<tr>';
				echo '<td valign="top">';
				if ($descrip_image && $description->image)
				{
					echo '<img src="'.$link.'" align="'.$description->image_position.'" hspace="6" alt="" />';
				}
				if ($descrip && $description->description)
				{
					echo $description->description;
				}
				echo '<br/><br/>';
				echo '</td>';
				echo '</tr>';
			}

			// Leading story output
			if ($leading)
			{
				echo '<tr>';
				echo '<td valign="top">';
				for ($z = 0; $z < $leading; $z ++)
				{
					if ($i >= $total)
					{
						// stops loop if total number of items is less than the number set to display as leading
						break;
					}
					echo '<div>';
					JContentController :: show($rows[$i], $params, $my->gid, $access, $pop, $option, $ItemidCount);
					echo '</div>';
					$i ++;
				}
				echo '</td>';
				echo '</tr>';
			}

			// use newspaper style vertical layout rather than horizontal table
			if ($intro && ($i < $total))
			{
				echo '<tr>';
				echo '<td valign="top">';
				echo '<table width="100%"  cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<td>';

				$indexcount = 0;
				$divider = '';
				for ($z = 0; $z < $columns; $z ++)
				{
					if ($z > 0)
						$divider = " column_seperator";
					echo "<td valign=\"top\"".$width." class=\"article_column".$divider."\">\n";
					for ($y = 0; $y < $intro / $columns; $y ++)
					{
						if ($indexcount < $intro)
							//echo $rows[$indexcount++] . "\n";
							JContentController :: show($rows[++ $indexcount], $params, $my->gid, $access, $pop, $option, $ItemidCount);
					}
					echo "</td>\n";

				}
				echo '</table>';
				echo '</td>';
				echo '</tr>';

				// TODO: remove this below

				//			echo '<tr>';
				//			echo '<td valign="top">';
				//			echo '<table width="100%"  cellpadding="0" cellspacing="0">';
				//			// intro story output
				//			for ( $z = 0; $z < $intro; $z++ ) {
				//				if ( $i >= $total ) {
				//					// stops loop if total number of items is less than the number set to display as intro + leading
				//					break;
				//				}
				//
				//				if ( !( $z % $columns ) || $columns == 1 ) {
				//					echo '<tr>';
				//				}
				//
				//				echo '<td valign="top" '. $width .' class="column_seperator">';
				//
				//				// outputs either intro or only a link
				//				if ( $z < $intro ) {
				//					show( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
				//				} else {
				//					echo '</td>';
				//					echo '</tr>';
				//					break;
				//				}
				//
				//				echo '</td>';
				//
				//				if ( !( ( $z + 1 ) % $columns ) || $columns == 1 ) {
				//					echo '</tr>';
				//				}
				//
				//				$i++;
				//			}
				//
				//			// this is required to output a final closing </tr> tag when the number of items does not fully
				//			// fill the last row of output - a blank column is left
				//			if ( $intro % $columns ) {
				//				echo '</tr>';
				//			}
				//
				//			echo '</table>';
				//			echo '</td>';
				//			echo '</tr>';

				// NOTE: End remove
			}

			// Links output
			if ($links && ($i < $total))
			{
				echo '<tr>';
				echo '<td valign="top">';
				echo '<div class="blog_more'.$params->get('pageclass_sfx').'">';
				JContentView :: showLinks($rows, $links, $total, ++ $indexcount, 1, $ItemidCount);
				echo '</div>';
				echo '</td>';
				echo '</tr>';
			}

			// Pagination output
			if ($pagination)
			{
				if (($pagination == 2) && ($total <= $limit))
				{
					// not visible when they is no 'other' pages to display
				} else
				{
					// get the total number of records
					$limitstart = $limitstart ? $limitstart : 0;
					require_once (JPATH_SITE.'/includes/pageNavigation.php');
					$pageNav = new mosPageNav($total, $limitstart, $limit);
					if ($option == 'com_frontpage')
					{
						$link = 'index.php?option=com_frontpage&amp;Itemid='.$Itemid;
					} else
						if ($archive)
						{
							$year = $params->get('year');
							$month = $params->get('month');
							$link = 'index.php?option=com_content&amp;task='.$task.'&amp;id='.$id.'&amp;Itemid='.$Itemid.'&amp;year='.$year.'&amp;month='.$month;
						} else
						{
							$link = 'index.php?option=com_content&amp;task='.$task.'&amp;id='.$id.'&amp;Itemid='.$Itemid;
						}
					echo '<tr>';
					echo '<td valign="top" align="center">';
					echo $pageNav->writePagesLinks($link);
					echo '<br /><br />';
					echo '</td>';
					echo '</tr>';
					if ($pagination_results)
					{
						echo '<tr>';
						echo '<td valign="top" align="center">';
						echo $pageNav->writePagesCounter();
						echo '</td>';
						echo '</tr>';
					}
				}
			}

			echo '</table>';

		} else
			if ($archive && !$total)
			{
				// Search Failure message for Archives
				$msg = sprintf(JText :: _('ARCHIVE_SEARCH_FAILURE'), $params->get('month'), $params->get('year'));
				echo '<br /><br /><div align="center">'.$msg.'</div><br />';
			} else
			{
				// Generic blog empty display
				echo _EMPTY_BLOG;
			}

		// Back Button
		$params->set('back_button', $back);
		mosHTML :: BackButton($params);
	}

	/**
	* Show a content item
	* @param object An object with the record data
	* @param boolean If <code>false</code>, the print button links to a popup window.  If <code>true</code> then the print button invokes the browser print method.
	*/
	function show(& $row, & $params, & $access, $page = 0, $option, $ItemidCount = NULL)
	{
		global $mainframe, $hide_js, $Itemid;

		/*
		 * Initialize some variables
		 */
		$my 		= & $mainframe->getUser();
		$document	= & $mainframe->getDocument();
		$SiteName 	= $mainframe->getCfg('sitename');
		$task 		= JRequest::getVar( 'task' );
		$gid 		= $my->gid;
		$_Itemid 	= $Itemid;
		$linkOn 	= null;
		$linkText 	= null;


		$mainframe->appendMetaTag('description', $row->metadesc);
		$mainframe->appendMetaTag('keywords', $row->metakey);

		// process the new plugins
		JPluginHelper :: importGroup('content');
		$results = $mainframe->triggerEvent('onPrepareContent', array (& $row, & $params, $page));

		// adds mospagebreak heading or title to <site> Title
		if (isset ($row->page_title))
		{
			$document->setTitle($row->title.': '.$row->page_title);
		}

		// determines the link and link text of the readmore button
		if ($params->get('intro_only'))
		{
			// checks if the item is a public or registered/special item
			if ($row->access <= $gid)
			{
				if ($task != "view")
				{
					$_Itemid = JApplicationHelper :: getItemid($row->id, 0, 0, $ItemidCount['bs'], $ItemidCount['bc'], $ItemidCount['gbs']);
				}
				$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$row->id."&amp;Itemid=".$_Itemid);
				//if ( strlen( trim( $row->fulltext ) )) {
				if (@ $row->readmore)
				{
					$linkText = JText :: _('Read more...');
				}
			} else
			{
				$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
				//if (strlen( trim( $row->fulltext ) )) {
				if (@ $row->readmore)
				{
					$linkText = JText :: _('Register to read more...');
				}
			}
		}

		$no_html = mosGetParam($_REQUEST, 'no_html', null);

		// for pop-up page
		if ($params->get('popup') && $no_html == 0)
		{
			$document->setTitle($SiteName.' - '.$row->title);
		}

		// determines links to next and prev content items within category
		if ($params->get('item_navigation'))
		{
			if ($row->prev)
			{
				$row->prev = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->prev.'&amp;Itemid='.$_Itemid);
			} else
			{
				$row->prev = 0;
			}
			if ($row->next)
			{
				$row->next = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->next.'&amp;Itemid='.$_Itemid);
			} else
			{
				$row->next = 0;
			}
		}

		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email'))
		{
			// link used by print button
			$print_link = $mainframe->getCfg('live_site').'/index2.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $page;
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php

			// displays Item Title
			JContentView :: _title($row, $params, $linkOn, $access);

			// displays PDF Icon
			JContentView :: _pdfIcon($row, $params, $linkOn, $hide_js);

			// displays Print Icon
			mosHTML :: PrintIcon($row, $params, $hide_js, $print_link);

			// displays Email Icon
			JContentView :: _emailIcon($row, $params, $hide_js);
			?>
			</tr>
			</table>
		<?php
		} else
			if ($access->canEdit)
			{
			// edit icon when item title set to hide
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
 			<tr>
 				<td>
 				<?php
				JContentView :: _editIcon($row, $params, $access);
				?>
 				</td>
 			</tr>
 			</table>
 			<?php


			}

		if (!$params->get('intro_only'))
		{
			$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (& $row, & $params, $page));
			echo trim(implode("\n", $results));
		}

		$onBeforeDisplayContent = $mainframe->triggerEvent('onBeforeDisplayContent', array (& $row, & $params, $page));
		echo trim(implode("\n", $onBeforeDisplayContent));
		?>

		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php

		// displays Section & Category
		JContentView :: _sectionCategory($row, $params);

		// displays Author Name
		JContentView :: _author($row, $params);

		// displays Created Date
		JContentView :: _createDate($row, $params);

		// displays Urls
		JContentView :: _url($row, $params);
		?>
		<tr>
			<td valign="top" colspan="2">
			<?php


		// displays Table of Contents
		JContentView :: _toc($row);

		// displays Item Text
		echo ampReplace($row->text);
		?>
			</td>
		</tr>
		<?php

		// displays Modified Date
		JContentView :: _modifiedDate($row, $params);

		// displays Readmore button
		JContentView :: _readMore($params, $linkOn, $linkText);

		?>
		</table>
		<span class="article_seperator">&nbsp;</span>

		<?php
		// Fire the after display content event
		$onAfterDisplayContent = $mainframe->triggerEvent('onAfterDisplayContent', array (& $row, & $params, $page));
		echo trim(implode("\n", $onAfterDisplayContent));

		// displays the next & previous buttons
		JContentView :: _navigation($row, $params);

		// displays close button in pop-up window
		mosHTML :: CloseButton($params, $hide_js);

		// displays back button in pop-up window
		mosHTML :: BackButton($params, $hide_js);
	}

	/**
	* Display links to categories
	*/
	function showCategories(& $params, & $items, $gid, & $categories, $catid, $id, $Itemid) {
		if (count($categories)) {
			?>
			<ul>
				<?php
				foreach ($categories as $row) {
					if ($catid != $row->id) {
						if ($row->access <= $gid) {
							$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$id.'&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
							?>
							<li>
								<a href="<?php echo $link; ?>" class="category">
									<?php echo $row->name;?></a>
								<?php
								if ($params->get('cat_items')) {
									?>
									&nbsp;<i>( <?php echo $row->numitems ." ". JText::_( 'items' );?> )</i>
									<?php
								}
								
								// Writes Category Description
								if ($params->get('cat_description') && $row->description) {
									echo '<br />';
									echo $row->description;
								}
								?>
							</li>
							<?php
						} else {
							?>
							<li>
								<?php echo $row->name; ?>
								<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=register' ); ?>">
									( <?php echo JText::_( 'Registered Users Only' ); ?> )</a>
							</li>
						<?php
						}
					}
				}
				?>
			</ul>
			<?php
		}
	}

	/**
	* Display Table of items
	*/
	function showTable(& $params, & $items, & $gid, $catid, $id, & $pageNav, & $access, & $sectionid, & $lists, $order) {
		global $Itemid;

		$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sectionid.'&amp;id='.$catid.'&amp;Itemid='.$Itemid;
		?>
		<script language="javascript" type="text/javascript">
		function tableOrdering( order, dir, task ) {
			var form = document.adminForm;
		
			form.filter_order.value 	= order;
			form.filter_order_Dir.value	= dir;
			document.adminForm.submit( task );
		}
		</script>
		
		<form action="<?php echo sefRelToAbs($link); ?>" method="post" name="adminForm">
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php
		if ($params->get('filter') || $params->get('order_select') || $params->get('display'))	{
			?>
			<tr>
				<td colspan="4">
					<table>
					<tr>
					<?php
					if ($params->get('filter'))	{
						?>
						<td align="left" width="100%" nowrap="nowrap">
							<?php
							echo JText :: _('Filter').'&nbsp;';
							?>
							<input type="text" name="filter" value="<?php echo $lists['filter'];?>" class="inputbox" onchange="document.adminForm.submit();" />
						</td>
					<?php
					}
					if ($params->get('display')) {
						?>
						<td align="right" width="100%" nowrap="nowrap">
							<?php
							echo '&nbsp;&nbsp;&nbsp;'.JText :: _('Display Num').'&nbsp;';
							$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sectionid.'&amp;id='.$catid.'&amp;Itemid='.$Itemid;
							echo $pageNav->getLimitBox($link);
							?>
						</td>
						<?php
					}
					?>
					</tr>
					</table>
				</td>
			</tr>
			<?php
		}
		if ($params->get('headings')) {
			?>
			<tr>
				<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5">
					<?php echo JText :: _('Num'); ?>
				</td>
				<?php
				if ($params->get('date')){
					?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="35%">
						<?php mosCommonHTML :: tableOrdering( 'Date', 'a.created', $lists ); ?>
					</td>
					<?php
				}
				if ($params->get('title')) {
					?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="45%">
						<?php mosCommonHTML :: tableOrdering( 'Item Title', 'a.title', $lists ); ?>
					</td>
					<?php
				}
				if ($params->get('author'))	{
					?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>"  width="25%">
						<?php mosCommonHTML :: tableOrdering( 'Author', 'author', $lists ); ?>
					</td>
					<?php
				}
				if ($params->get('hits')) {
					?>
					<td align="center" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5%" nowrap="nowrap">
						<?php mosCommonHTML :: tableOrdering( 'Hits', 'a.hits', $lists ); ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
		}

		$k = 0;
		$i = 0;
		foreach ($items as $row){
			$row->created = mosFormatDate($row->created, $params->get('date_format'));
			?>
			<tr class="sectiontableentry<?php echo ($k+1) . $params->get( 'pageclass_sfx' ); ?>" >
				<td align="center">
					<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<?php
				if ($params->get('date')) {
					?>
					<td>
						<?php echo $row->created; ?>
					</td>
					<?php
				}
				if ($params->get('title')) {
					if ($row->access <= $gid) {
						$link = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
						?>
						<td>
							<a href="<?php echo $link; ?>">
								<?php echo $row->title; ?></a>
							<?php
							JContentView :: _editIcon($row, $params, $access);
							?>
						</td>
						<?php
					} else	{
						?>
						<td>
						<?php
						echo $row->title.' : ';
						$link = sefRelToAbs('index.php?option=com_registration&amp;task=register');
						?>
						<a href="<?php echo $link; ?>">
							<?php echo JText::_( 'Register to read more...' ); ?></a>
						</td>
					<?php
					}
				}
				if ($params->get('author')) {
					?>
					<td >
						<?php echo $row->created_by_alias ? $row->created_by_alias : $row->author; ?>
					</td>
					<?php
				}
				if ($params->get('hits')) {
					?>
					<td align="center">
						<?php echo $row->hits ? $row->hits : '-'; ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
		}
		if ($params->get('navigation')) {
			?>
			<tr>
				<td colspan="4">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php
					$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sectionid.'&amp;id='.$catid.'&amp;Itemid='.$Itemid;
					echo $pageNav->writePagesLinks($link);
					?>
				</td>
			</tr>
			<tr>
				<td colspan="4" align="right">
					<?php echo $pageNav->writePagesCounter(); ?>
				</td>
			</tr>
			<?php
		}
		if ($access->canEdit || $access->canEditOwn) {
			$link = sefRelToAbs('index.php?option=com_content&amp;task=new&amp;sectionid='.$id.'&amp;cid='.$row->id.'&amp;Itemid='.$Itemid);
			?>
			<tr>
				<td colspan="4">
					<a href="<?php echo $link; ?>">
						<img src="images/M_images/new.png" width="13" height="14" align="middle" border="0" alt="<?php echo JText::_( 'New' );?>" />
						&nbsp;<?php echo JText::_( 'New' );?>...</a>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		
		<input type="hidden" name="id" value="<?php echo $catid; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sectionid; ?>" />
		<input type="hidden" name="task" value="<?php echo $lists['task']; ?>" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	* Display links to content items
	*/
	function showLinks(& $rows, $links, $total, $i = 0, $show = 1, $ItemidCount)
	{
		global $mainframe;

		if ($show)
		{
		?>
			<div>
			<strong>
			<?php echo JText::_( 'Read more...' ); ?>
			</strong>
			</div>
			<ul>
			<?php
		}
		for ($z = 0; $z < $links; $z ++)
		{
			if ($i >= $total)
			{
				// stops loop if total number of items is less than the number set to display as intro + leading
				break;
			}
			// needed to reduce queries used by getItemid
			$_Itemid = JApplicationHelper :: getItemid($rows[$i]->id, 0, 0, $ItemidCount['bs'], $ItemidCount['bc'], $ItemidCount['gbs']);
			$link = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$rows[$i]->id.'&amp;Itemid='.$_Itemid)
			?>
			<li>
			<a class="blogsection" href="<?php echo $link; ?>">
			<?php echo $rows[$i]->title; ?>
			</a>
			</li>
			<?php
			 $i ++;
		}
		?>
		</ul>
		<?php
	}

	/**
	* Writes the edit form for new and existing content item
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* 
	* @return void
	* @since 1.0
	*/
	function editContent(& $row, $section, & $lists, & $images, & $access, $myid, $sectionid, $task, $Itemid)
	{
		global $mainframe, $Itemid;

		// Require the toolbar
		require_once (JPATH_SITE.'/includes/HTML_toolbar.php');

		/*
		 * Initialize some variables
		 */
		$document = & $mainframe->getDocument();
		$Returnid = JRequest::getVar( 'Returnid', $Itemid, '', 'int' );
		$tabs = new mosTabs(0, 1);

		$document->addStyleSheet('includes/js/calendar/calendar-mos.css');
		$document->addScript('includes/js/calendar/calendar_mini.js');
		$document->addScript('includes/js/calendar/lang/calendar-en.js');
		
		mosCommonHTML::loadOverlib();

		// Ensure the row data is safe html
		mosMakeHtmlSafe($row);

		?>
	  	<script language="javascript" type="text/javascript">
		onunload = WarnUser;
		var folderimages = new Array;
		<?php
		$i = 0;
		foreach ($images as $k => $items)
		{
			foreach ($items as $v)
			{
				echo "\n	folderimages[".$i ++."] = new Array( '$k','".addslashes($v->value)."','".addslashes($v->text)."' );";
			}
		}
		?>
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// var goodexit=false;
			// assemble the images back into one field
			form.goodexit.value=1;
			var temp = new Array;
			for (var i=0, n=form.imagelist.options.length; i < n; i++) {
				temp[i] = form.imagelist.options[i].value;
			}
			form.images.value = temp.join( '\n' );
			try {
				form.onsubmit();
			}
			catch(e){}
			// do field validation
			if (form.title.value == "") {
				alert ( "<?php echo JText::_( 'Content item must have a title', true ); ?>" );
			} else if (parseInt('<?php echo $row->sectionid;?>')) {
				// for content items
				if (getSelectedValue('adminForm','catid') < 1) {
					alert ( "<?php echo JText::_( 'Please select a category', true ); ?>" );
				//} else if (form.introtext.value == "") {
				//	alert ( "<?php echo JText::_( 'Content item must have intro text', true ); ?>" );
				} else {
		<?php
		$editor = & JEditor :: getInstance();
		echo $editor->getEditorContents('editor1', 'introtext');
		echo $editor->getEditorContents('editor2', 'fulltext');
		?>
					submitform(pressbutton);
				}
			//} else if (form.introtext.value == "") {
			//	alert ( "<?php echo JText::_( 'Content item must have intro text', true ); ?>" );
			} else {
				// for static content
		<?php
		$editor = & JEditor :: getInstance();
		echo $editor->getEditorContents('editor1', 'introtext');
		?>
				submitform(pressbutton);
			}
		}

		function setgood(){
			document.adminForm.goodexit.value=1;
		}

		function WarnUser(){
			if (document.adminForm.goodexit.value==0) {
				alert('<?php echo JText::_( 'WARNUSER', true );?>');
				window.location="<?php echo sefRelToAbs("index.php?option=com_content&task=".$task."&sectionid=".$sectionid."&id=".$row->id."&Itemid=".$Itemid); ?>";
			}
		}
		</script>
		<?php
		$docinfo = "<strong>".JText :: _('Expiry Date').":</strong> ";
		$docinfo .= $row->publish_down."<br />";
		$docinfo .= "<strong>".JText :: _('Version').":</strong> ";
		$docinfo .= $row->version."<br />";
		$docinfo .= "<strong>".JText :: _('Created').":</strong> ";
		$docinfo .= $row->created."<br />";
		$docinfo .= "<strong>".JText :: _('Last Modified').":</strong> ";
		$docinfo .= $row->modified."<br />";
		$docinfo .= "<strong>".JText :: _('Hits').":</strong> ";
		$docinfo .= $row->hits."<br />";
		?>
		<form action="index.php" method="post" name="adminForm" onSubmit="javascript:setgood();">

		<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td class="contentheading" >
			<?php echo $section;?> / <?php echo $row->id ? JText::_( 'Edit' ) : JText::_( 'Add' );?>&nbsp;
			<?php echo JText::_( 'Content' );?> &nbsp;&nbsp;&nbsp;
			<?php echo mosToolTip('<table>'.$docinfo.'</table>', JText::_( 'Item Information', true ), '', '', '<strong>['.JText::_( 'Info', true ).']</strong>'); ?>
			</a>
			</td>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<td>
				<div style="float: left;">
					<?php echo JText::_( 'Title' ); ?>:
					<br />
					<input class="inputbox" type="text" name="title" size="50" maxlength="100" value="<?php echo $row->title; ?>" />
				</div>
				<div style="float: right;">
		<?php
		// Toolbar Top
		mosToolBar :: startTable();
		mosToolBar :: save();
		mosToolBar :: apply('apply_new');
		mosToolBar :: cancel();
		mosToolBar :: endtable();
		?>
				</div>
			</td>
		</tr>
		<?php
		if ($row->sectionid)
		{
			?>
			<tr>
				<td>
				<?php echo JText::_( 'Category' ); ?>:
				<br />
				<?php echo $lists['catid']; ?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
		<?php
		if (intval($row->sectionid) > 0)
		{
			?>
				<td>
				<?php echo JText::_( 'Intro Text' ) .' ('. JText::_( 'Required' ) .')'; ?>:
				</td>
				<?php
		} else
		{
			?>
				<td>
				<?php echo JText::_( 'Main Text' ) .' ('. JText::_( 'Required' ) .')'; ?>:
				</td>
			<?php
		}
		?>
		</tr>
		<tr>
			<td>
			<?php
		// parameters : areaname, content, hidden field, width, height, rows, cols
		$editor = & JEditor :: getInstance();
		echo $editor->getEditor('editor1', $row->introtext, 'introtext', '600', '400', '70', '15');
		?>
			</td>
		</tr>
		<?php
		if (intval($row->sectionid) > 0)
		{
			?>
			<tr>
				<td>
				<?php echo JText::_( 'Main Text' ) .' ('. JText::_( 'Optional' ) .')'; ?>:
				</td>
			</tr>
			<tr>
				<td>
			<?php
			// parameters : areaname, content, hidden field, width, height, rows, cols
			$editor = & JEditor :: getInstance();
			echo $editor->getEditor('editor2', $row->fulltext, 'fulltext', '600', '400', '70', '15');
			?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php

		// Toolbar Bottom
		mosToolBar :: startTable();
		mosToolBar :: save();
		mosToolBar :: apply();
		mosToolBar :: cancel();
		mosToolBar :: endtable();

		$title = JText :: _('Images');
		$tabs->startPane('content-pane');
		$tabs->startTab($title, 'images-page');
		?>
			<table class="adminform">
			<tr>
				<td colspan="4">
				<?php echo JText::_( 'Sub-folder' ); ?> - <?php echo $lists['folders'];?>
				</td>
			</tr>
			<tr>
				<td align="top">
					<?php echo JText::_( 'Gallery Images' ); ?>
				</td>
				<td width="2%">
				</td>
				<td align="top">
					<?php echo JText::_( 'Content Images' ); ?>
				</td>
				<td align="top">
					<?php echo JText::_( 'Edit Image' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $lists['imagefiles'];?>
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Insert' ); ?>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" />
				</td>
				<td width="2%">
					<input class="button" type="button" value=">>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" title="<?php echo JText::_( 'Add' ); ?>"/>
					<br/>
					<input class="button" type="button" value="<<" onclick="delSelectedFromList('adminForm','imagelist')" title="<?php echo JText::_( 'Remove' ); ?>"/>
				</td>
				<td valign="top">
					<?php echo $lists['imagelist'];?>
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Up' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,-1)" />
					<input class="button" type="button" value="<?php echo JText::_( 'Down' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,+1)" />
				</td>
				<td valign="top">
					<table>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Source' ); ?>:
						</td>
						<td>
						<input class="inputbox" type="text" name= "_source" value="" size="15" />
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">
						<?php echo JText::_( 'Align' ); ?>:
						</td>
						<td>
						<?php echo $lists['_align']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Alt Text' ); ?>:
						</td>
						<td>
						<input class="inputbox" type="text" name="_alt" value="" size="15" />
						</td>
					</tr>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Border' ); ?>:
						</td>
						<td>
						<input class="inputbox" type="text" name="_border" value="" size="3" maxlength="1" />
						</td>
					</tr>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Caption' ); ?>:
						</td>
						<td>
						<input class="text_area" type="text" name="_caption" value="" size="30" />
						</td>
					</tr>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Caption Position' ); ?>:
						</td>
						<td>
						<?php echo $lists['_caption_position']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Caption Align' ); ?>:
						</td>
						<td>
						<?php echo $lists['_caption_align']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
						<?php echo JText::_( 'Caption Width' ); ?>:
						</td>
						<td>
						<input class="text_area" type="text" name="_width" value="" size="5" maxlength="5" />
						</td>
					</tr>
					<tr>
						<td align="right"></td>
						<td>
						<input class="button" type="button" value="<?php echo JText::_( 'Apply' ); ?>" onclick="applyImageProps()" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<img name="view_imagefiles" src="images/M_images/blank.png" width="50" alt="<?php echo JText::_( 'No Image' ); ?>" />
				</td>
				<td width="2%">
				</td>
				<td>
					<img name="view_imagelist" src="images/M_images/blank.png" width="50" alt="<?php echo JText::_( 'No Image' ); ?>" />
				</td>
				<td>
				</td>
			</tr>
			</table>
		<?php
		$title = JText :: _('Publishing');
		$tabs->endTab();
		$tabs->startTab($title, 'publish-page');
		?>
			<table class="adminform">
			<?php
		if ($access->canPublish)
		{
			?>
				<tr>
					<td >
					<?php echo JText::_( 'State' ); ?>:
					</td>
					<td>
					<?php echo $lists['state']; ?>
					</td>
				</tr>
			<?php
		}
			?>
			<tr>
				<td >
				<?php echo JText::_( 'Access Level' ); ?>:
				</td>
				<td>
				<?php echo $lists['access']; ?>
				</td>
			</tr>
			<tr>
				<td >
				<?php echo JText::_( 'Author Alias' ); ?>:
				</td>
				<td>
				<input type="text" name="created_by_alias" size="50" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="inputbox" />
				</td>
			</tr>
			<tr>
				<td >
				<?php echo JText::_( 'Ordering' ); ?>:
				</td>
				<td>
				<?php echo $lists['ordering']; ?>
				</td>
			</tr>
			<tr>
				<td >
				<?php echo JText::_( 'Start Publishing' ); ?>:
				</td>
				<td>
				<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
				<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');" />
				</td>
			</tr>
			<tr>
				<td >
				<?php echo JText::_( 'Finish Publishing' ); ?>:
				</td>
				<td>
				<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
				<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');" />
				</td>
			</tr>
			<tr>
				<td >
				<?php echo JText::_( 'Show on Front Page' ); ?>:
				</td>
				<td>
				<input type="checkbox" name="frontpage" value="1" <?php echo $row->frontpage ? 'checked="checked"' : ''; ?> />
				</td>
			</tr>
			</table>
		<?php
		$title = JText :: _('Metadata');
		$tabs->endTab();
		$tabs->startTab($title, 'meta-page');
		?>
			<table class="adminform">
			<tr>
				<td  valign="top">
				<?php echo JText::_( 'Description' ); ?>:
				</td>
				<td>
				<textarea class="inputbox" cols="45" rows="3" name="metadesc"><?php echo str_replace('&','&amp;',$row->metadesc); ?></textarea>
				</td>
			</tr>
			<tr>
				<td  valign="top">
				<?php echo JText::_( 'Keywords' ); ?>:
				</td>
				<td>
				<textarea class="inputbox" cols="45" rows="3" name="metakey"><?php echo str_replace('&','&amp;',$row->metakey); ?></textarea>
				</td>
			</tr>
			</table>

		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>

		<div style="clear:both;"></div>

		<input type="hidden" name="images" value="" />
		<input type="hidden" name="goodexit" value="0" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="Returnid" value="<?php echo $Returnid; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $row->version; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $row->sectionid; ?>" />
		<input type="hidden" name="created_by" value="<?php echo $row->created_by; ?>" />
		<input type="hidden" name="referer" value="<?php echo ampReplace( $_SERVER['HTTP_REFERER'] ); ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	 * Writes Email form for sending a content item link to a friend
	 * 
	 * @param int 		$uid 		Content item id
	 * @param string 	$title 		Content item title
	 * @param string 	$template 	The current template
	 * @return void
	 * @since 1.0
	 */
	function emailForm($uid, $title, $template = '')
	{
		global $mosConfig_sitename, $mainframe;

		$mainframe->setPageTitle($mosConfig_sitename.' - '.$title);
		$mainframe->addCustomHeadTag('<link rel="stylesheet" href="templates/'.$template.'/css/template_css.css" type="text/css" />');
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton() {
			var form = document.frontendForm;
			// do field validation
			if (form.email.value == "" || form.youremail.value == "") {
				alert( "<?php echo JText::_( 'EMAIL_ERR_NOINFO', true ); ?>" );
				return false;
			}
			return true;
		}
		</script>

		<form action="index2.php?option=com_content&amp;task=emailsend" name="frontendForm" method="post" onSubmit="return submitbutton();">
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td colspan="2">
			<?php echo JText::_( 'Email this to a friend.' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td width="130">
			<?php echo JText::_( 'Your friend`s Email' ); ?>:
			</td>
			<td>
			<input type="text" name="email" class="inputbox" size="25" />
			</td>
		</tr>
		<tr>
			<td height="27">
			<?php echo JText::_( 'Your Name' ); ?>:
			</td>
			<td>
			<input type="text" name="yourname" class="inputbox" size="25" />
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JText::_( 'Your Email' ); ?>:
			</td>
			<td>
			<input type="text" name="youremail" class="inputbox" size="25" />
			</td>
		</tr>
		<tr>
			<td>
			&nbsp;<?php echo JText::_( 'Message subject' ); ?>:
			</td>
			<td>
			<input type="text" name="subject" class="inputbox" maxlength="100" size="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="submit" name="submit" class="button" value="<?php echo JText::_( 'Send email' ); ?>" />
			&nbsp;&nbsp;
			<input type="button" name="cancel" value="<?php echo JText::_( 'Cancel' ); ?>" class="button" onclick="window.close();" />
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $uid; ?>" />
		<input type="hidden" name="<?php echo mosHash( 'validate' );?>" value="1" />
		</form>
		<?php


	}

	/**
	 * Writes Email sent popup
	 * 
	 * @param string $to 		Email recipient
	 * @param string $template 	The current template
	 * @return void
	 * @since 1.0
	 */
	function emailSent($to, $template = '')
	{
		global $mainframe;

		$mainframe->setPageTitle($mainframe->getCfg('sitename'));
		$mainframe->addCustomHeadTag('<link rel="stylesheet" href="templates/'.$template.'/css/template_css.css" type="text/css" />');
		?>
		<span class="contentheading"><?php echo JText::_( 'This item has been sent to' )." $to";?></span> <br />
		<br />
		<br />
		<a href='javascript:window.close();'>
		<span class="small"><?php echo JText::_( 'Close Window' );?></span>
		</a>
		<?php


	}

	/**
	 * Method to show an empty container if there is no data to display
	 * 
	 * @static
	 * @param string $msg The message to show
	 * @return void
	 * @since 1.1
	 */
	function emptyContainer($msg)
	{
		echo '<p><div align="center">'.$msg.'</div></p>';
	}
	
	/**
	 * Writes a user input error message and if javascript is enabled goes back
	 * to the previous screen to try again.
	 * 
	 * @param string $msg The error message to display
	 * @return void
	 * @since 1.1
	 */
	function userInputError($msg)
	{
		josErrorAlert($msg);
	}

	/**
	 * Helper method to print the content item's title block if enabled.
	 * 
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @param string $linkOn 	Menu link for the content item
	 * @param object $access 	Access object for the content item
	 * @return void
	 * @since 1.0
	 */
	function _title($row, $params, $linkOn, $access)
	{
		if ($params->get('item_title'))
		{
			if ($params->get('link_titles') && $linkOn != '')
			{
				?>
				<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
				<a href="<?php echo $linkOn;?>" class="contentpagetitle<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $row->title;?>
				</a>
				<?php JContentView::_editIcon( $row, $params, $access ); ?>
				</td>
				<?php


			} 
			else
			{
				?>
				<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
				<?php echo $row->title;?>
				<?php JContentView::_editIcon( $row, $params, $access ); ?>
				</td>
				<?php
			}
		} else
		{
			?>
			<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
			<?php JContentView::_editIcon( $row, $params, $access ); ?>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print the edit icon for the content item if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @param object $access 	Access object for the content item
	 * @return void
	 * @since 1.0
	 */
	function _editIcon($row, $params, $access)
	{
		global $Itemid, $my, $mainframe;

		if ($params->get('popup'))
		{
			return;
		}
		if ($row->state < 0)
		{
			return;
		}
		if (!$access->canEdit && !($access->canEditOwn && $row->created_by == $my->id))
		{
			return;
		}

		mosCommonHTML :: loadOverlib();

		$link = 'index.php?option=com_content&amp;task=edit&amp;id='.$row->id.'&amp;Itemid='.$Itemid.'&amp;Returnid='.$Itemid;
		$image = mosAdminMenus :: ImageCheck('edit.png', '/images/M_images/', NULL, NULL, JText :: _('Edit'), JText :: _('Edit'));

		if ($row->state == 0)
		{
			$overlib = JText :: _('Unpublished');
		} else
		{
			$overlib = JText :: _('Published');
		}
		$date = mosFormatDate($row->created);
		$author = $row->created_by_alias ? $row->created_by_alias : $row->author;

		$overlib .= '<br />';
		$overlib .= $row->groups;
		$overlib .= '<br />';
		$overlib .= $date;
		$overlib .= '<br />';
		$overlib .= $author;
		?>
		<a href="<?php echo sefRelToAbs( $link ); ?>" onmouseover="return overlib('<?php echo $overlib; ?>', CAPTION, '<?php echo JText::_( 'Edit Item' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
		<?php echo $image; ?>
		</a>
		<?php
	}

	/**
	 * Helper method to print the content item's pdf icon if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object 	$row 	The content item
	 * @param object 	$params The content item's parameters object
	 * @param string 	$linkOn Menu link for the content item
	 * @param boolean 	$hideJS True to hide the javascript
	 * @return void
	 * @since 1.0
	 */
	function _pdfIcon($row, $params, $linkOn, $hideJS)
	{
		if ($params->get('pdf') && !$params->get('popup') && !$hideJS)
		{
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			$link = 'index2.php?option=com_content&amp;no_html=1&amp;task=viewpdf&amp;id='.$row->id;
			if ($params->get('icons'))
			{
				$image = mosAdminMenus :: ImageCheck('pdf_button.png', '/images/M_images/', NULL, NULL, JText :: _('PDF'), JText :: _('PDF'));
			} else
			{
				$image = JText :: _('PDF').'&nbsp;';
			}
			?>
			<td align="right" width="100%" class="buttonheading">
			<a href="javascript:void(0)" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>');" title="<?php echo JText::_( 'PDF' );?>">
			<?php echo $image; ?>
			</a>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's email icon if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object 	$row 	The content item
	 * @param object 	$params The content item's parameters object
	 * @param boolean 	$hideJS True to hide javascript code
	 * @return void
	 * @since 1.0
	 */
	function _emailIcon($row, $params, $hideJS)
	{
		if ($params->get('email') && !$params->get('popup') && !$hideJS)
		{
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=400,height=250,directories=no,location=no';
			$link = 'index2.php?option=com_content&amp;task=emailform&amp;id='.$row->id;
			if ($params->get('icons'))
			{
				$image = mosAdminMenus :: ImageCheck('emailButton.png', '/images/M_images/', NULL, NULL, JText :: _('Email'), JText :: _('Email'));
			} else
			{
				$image = '&nbsp;'.JText :: _('Email');
			}
			?>
			<td align="right" width="100%" class="buttonheading">
			<a href="javascript:void(0)" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>');" title="<?php echo JText::_( 'Email' );?>">
			<?php echo $image; ?>
			</a>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print a container for a category and section blocks
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The item to display
	 * @param object $params 	The item to display's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _sectionCategory($row, $params)
	{
		if ($params->get('section') || $params->get('category'))
		{
			?>
			<tr>
				<td>
				<?php
		}

		// displays Section Name
		JContentView :: _section($row, $params);

		// displays Section Name
		JContentView :: _category($row, $params);

		if ($params->get('section') || $params->get('category'))
		{
				?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the section block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The section item
	 * @param object $params 	The section item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _section($row, $params)
	{
		if ($params->get('section'))
		{
			?>
			<span>
				<?php
				echo $row->section;
				// writes dash between section & Category Name when both are active
				if ($params->get('category'))
				{
					echo ' - ';
				}
				?>
			</span>
			<?php
		}
	}

	/**
	 * Helper method to print the category block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The category item
	 * @param object $params 	The category's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _category($row, $params)
	{
		if ($params->get('category'))
		{
			?>
			<span>
			<?php
			echo $row->category;
			?>
			</span>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's author block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _author($row, $params)
	{
		global $acl;

		if (($params->get('author')) && ($row->author != ""))
		{
			?>
			<tr>
				<td width="70%"  valign="top" colspan="2">
				<span class="small">
				&nbsp;<?php JText::printf( 'Written by', ($row->created_by_alias ? $row->created_by_alias : $row->author) ); ?>
				</span>
				&nbsp;&nbsp;
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's URL block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _url($row, $params)
	{
		if ($params->get('url') && $row->urls)
		{
			?>
			<tr>
				<td valign="top" colspan="2">
				<a href="http://<?php echo $row->urls ; ?>" target="_blank">
				<?php echo $row->urls; ?>
				</a>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's created date block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _createDate($row, $params)
	{
		$create_date = null;
		if (intval($row->created) != 0)
		{
			$create_date = mosFormatDate($row->created);
		}
		if ($params->get('createdate'))
		{
			?>
			<tr>
				<td valign="top" colspan="2" class="createdate">
				<?php echo $create_date; ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's modified date block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _modifiedDate($row, $params)
	{
		$mod_date = null;
		if (intval($row->modified) != 0)
		{
			$mod_date = mosFormatDate($row->modified);
		}
		if (($mod_date != '') && $params->get('modifydate'))
		{
			?>
			<tr>
				<td colspan="2"  class="modifydate">
				<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $mod_date; ?> )
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's table of contents block if
	 * present.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row The content item
	 * @return void
	 * @since 1.0
	 */
	function _toc($row)
	{
		if (isset ($row->toc))
		{
			echo $row->toc;
		}
	}

	/**
	 * Helper method to print the content item's read more button if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param string $linkOn 	Button link for the read more button
	 * @param string $linkText 	Text for read more button
	 * @return void
	 * @since 1.0
	 */
	function _readMore($params, $linkOn, $linkText)
	{
		if ($params->get('readmore'))
		{
			if ($params->get('intro_only') && $linkText)
			{
				?>
				<tr>
					<td  colspan="2">
					<a href="<?php echo $linkOn;?>" class="readon<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo $linkText;?>
					</a>
					</td>
				</tr>
				<?php
			}
		}
	}

	/**
	 * Helper method to print the content item's pagination block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function _navigation($row, $params)
	{
		$task = mosGetParam($_REQUEST, 'task', '');
		if ($params->get('item_navigation') && ($task == "view") && !$params->get('popup') && ($row->prev || $row->next))
		{
			$pnSpace = "";
			if (JText :: _('&lt') || JText :: _('&gt'))
			{
				$pnSpace = " ";
			}
			?>
			<table align="center" style="margin-top: 25px;">
			<tr>
				<?php
				if ($row->prev)
				{
					?>
					<th class="pagenav_prev">
					<a href="<?php echo $row->prev; ?>">
					<?php echo JText::_( '&lt' ) . $pnSpace . JText::_( 'Prev' ); ?>
					</a>
					</th>
					<?php
				}
				if ($row->prev && $row->next)
				{
					?>
					<td width="50">
						&nbsp;
					</td>
					<?php
				}
				if ($row->next)
				{
					?>
					<th class="pagenav_next">
					<a href="<?php echo $row->next; ?>">
					<?php echo JText::_( 'Next' ) . $pnSpace . JText::_( '&gt' ); ?>
					</a>
					</th>
					<?php
				}
				?>
			</tr>
			</table>
			<?php
		}
	}
}
?>