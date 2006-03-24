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
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JViewHTMLArchive extends JView
{
	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Archive';

	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		// Initialize some variables
		$menu		= & $this->get( 'Menu' );
		$Itemid	= $menu->id;
		$task		= JRequest::getVar('task');
		$id			= JRequest::getVar('id', 0, '', 'int');

		// initiate form
		$link = 'index.php?option=com_content&task='.$task.'&id='.$id.'&Itemid='.$Itemid;
		echo '<form action="'.sefRelToAbs($link).'" method="post">';

		$this->showArchive();

		echo '<input type="hidden" name="id" value="'.$id.'" />';
		echo '<input type="hidden" name="Itemid" value="'.$Itemid.'" />';
		echo '<input type="hidden" name="task" value="'.$task.'" />';
		echo '<input type="hidden" name="option" value="com_content" />';
		echo '</form>';
	}

	function showArchive()
	{
		// Initialize some variables
		$app		= & $this->get( 'Application' );
		$user		= & $app->getUser();
		$menu		= & $this->get( 'Menu' );
		$params	= & $menu->parameters;
		$Itemid	= $menu->id;
		$gid			= $user->get('gid');

		// At some point this will come from a request object
		$task		= JRequest::getVar('task');
		$id			= JRequest::getVar('id');
		$option	= JRequest::getVar('option');
		$showAll	= !$id;

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Append Archives to BreadCrumbs
		$breadcrumbs = & $app->getPathWay();
		$breadcrumbs->addItem('Archives', '');

		// Page Title
		$app->SetPageTitle($menu->name);

		/*
		 * Menu item parameters
		 */
		if ($params->get('page_title', 1) && $menu)
		{
			$header = $params->def('header', $menu->name);
		}
		else
		{
			$header = '';
		}
		$columns = $params->def('columns', 2);
		if ($columns == 0)
		{
			$columns = 1;
		}
		$intro								= $params->def('intro', 4);
		$leading							= $params->def('leading', 1);
		$links								= $params->def('link', 4);
		$usePagination				= $params->def('pagination', 2);
		$showPaginationResults	= $params->def('pagination_results', 1);
		$descrip							= $params->def('description', 1);
		$descrip_image				= $params->def('description_image', 1);

		$params->def('pageclass_sfx', '');
		$params->set('intro_only', 1);

		/*
		 * Lets get the content item data from the model
		 */
		$rows = & $this->get( 'Archives' );

		/*
		 * Pagination support
		 */
		$total			= count($rows);
		$limitstart		= JRequest::getVar('limitstart', 0, '', 'int');
		$limit			= $intro + $leading + $links;
		if ($total <= $limit)
		{
			$limitstart = 0;
		}
		$i = $limitstart;

		/*
		 * Header Output
		 */
		if ($header)
		{
			echo '<div class="componentheading'.$params->get('pageclass_sfx').'">'.$header.'</div>';
		}

		if (!$showAll)
		{
			echo '<br />';
			echo mosHTML::monthSelectList('month', 'size="1" class="inputbox"', $params->get('month'));
			echo mosHTML::integerSelectList(2000, 2010, 1, 'year', 'size="1" class="inputbox"', $params->get('year'), "%04d");
			echo '<input type="submit" class="button" />';
		}

		/*
		 * Do we have any items to display?
		 */
		if ($total)
		{
			$col_width = 100 / $columns; // width of each column
			$width = 'width="'.intval($col_width).'%"';

			if (!$showAll)
			{
				// Search Success message
				$msg = sprintf(JText::_('ARCHIVE_SEARCH_SUCCESS'), $params->get('month'), $params->get('year'));
				echo "<br /><br /><div align='center'>".$msg."</div><br /><br />";
			}
			echo '<table class="blog'.$params->get('pageclass_sfx').'" cellpadding="0" cellspacing="0">';

			/*
			 * Leading story output
			 */
			if ($leading)
			{
				echo '<tr>';
				echo '<td valign="top">';
				for ($i = 0; $i < $leading; $i ++)
				{
					if ($i >= $total)
					{
						// stops loop if total number of items is less than the number set to display as leading
						break;
					}
					echo '<div>';
					$this->showItem($rows[$i], $params, $access, true);
					echo '</div>';
					$i ++;
				}
				echo '</td>';
				echo '</tr>';
			}

			/*
			 * Newspaper style vertical layout
			 */
			if ($intro && ($i < $total))
			{
				echo '<tr>';
				echo '<td valign="top">';
				echo '<table width="100%"  cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<td>';

				$divider = '';
				for ($z = 0; $z < $columns; $z ++)
				{
					if ($z > 0)
					{
						$divider = " column_seperator";
					}
					echo "<td valign=\"top\"".$width." class=\"article_column".$divider."\">\n";
					for ($y = 0; $y < $intro / $columns; $y ++)
					{
						if ($i <= $intro && ($i <= $total))
						{
							$this->showItem($rows[ $i], $params, $access);
							$i ++;
						}
					}
					echo "</td>\n";

				}
				echo '</table>';
			}

			/*
			 * Links output
			 */
			if ($links && ($i < $total))
			{
				echo '<tr>';
				echo '<td valign="top">';
				echo '<div class="blog_more'.$params->get('pageclass_sfx').'">';
				$this->showLinks($rows, $links, $total, $i);
				echo '</div>';
				echo '</td>';
				echo '</tr>';
			}

			/*
			 * Pagination output
			 */
			if ($usePagination)
			{
				if (($usePagination == 2) && ($total <= $limit))
				{
					// not visible when they is no 'other' pages to display
				}
				else
				{
					// get the total number of records
					$limitstart = $limitstart ? $limitstart : 0;
					jimport('joomla.presentation.pagination');
					$pagination = new JPagination($total, $limitstart, $limit);

					if ($option == 'com_frontpage')
					{
						$link = 'index.php?option=com_frontpage&amp;Itemid='.$Itemid;
					}
					else
						if (!$showAll)
						{
							$year = $params->get('year');
							$month = $params->get('month');
							$link = 'index.php?option=com_content&amp;task='.$task.'&amp;id='.$id.'&amp;Itemid='.$Itemid.'&amp;year='.$year.'&amp;month='.$month;
						}
						else
						{
							$link = 'index.php?option=com_content&amp;task='.$task.'&amp;id='.$id.'&amp;Itemid='.$Itemid;
						}
					echo '<tr>';
					echo '<td valign="top" align="center">';
					echo $pagination->getPagesLinks($link);
					echo '<br /><br />';
					echo '</td>';
					echo '</tr>';

					if ($showPaginationResults)
					{
						echo '<tr>';
						echo '<td valign="top" align="center">';
						echo $pagination->getPagesCounter();
						echo '</td>';
						echo '</tr>';
					}
				}
			}

			echo '</table>';

		}
		else
			if (!$total && !$showAll)
			{
				$msg = sprintf(JText::_('ARCHIVE_SEARCH_FAILURE'), $params->get('month'), $params->get('year'));
				echo '<br /><br /><div align="center">'.$msg.'</div><br />';
			}
			else
			{
				// Generic blog empty display
				JViewContentHTML::emptyContainer(_EMPTY_BLOG);
			}
	}

	function showItem(&$row, &$params, &$access, $showImages = false)
	{
		// Initialize some variables
		$app		= & $this->get( 'Application' );
		$user		= & $app->getUser();
		$menu		= & $this->get( 'Menu' );
		$Itemid	= $menu->id;

		// These will come from a request object at some point
		$task			= JRequest::getVar( 'task' );
		$noJS 			= JRequest::getVar( 'hide_js', 0, '', 'int' );
		$noHTML		= JRequest::getVar('no_html', 0, '', 'int');

		// TODO: clean this part up
		$SiteName	= $app->getCfg('sitename');
		$gid				= $user->get('gid');

		$linkOn		= null;
		$linkText	= null;

		/*
		 * Get some parameters from global configuration
		 */
		$params->def('link_titles',		$app->getCfg('link_titles'));
		$params->def('author',			!$app->getCfg('hideAuthor'));
		$params->def('createdate',	!$app->getCfg('hideCreateDate'));
		$params->def('modifydate',	!$app->getCfg('hideModifyDate'));
		$params->def('print',				!$app->getCfg('hidePrint'));
		$params->def('pdf',					!$app->getCfg('hidePdf'));
		$params->def('email',				!$app->getCfg('hideEmail'));
		$params->def('rating',				$app->getCfg('vote'));
		$params->def('icons',				$app->getCfg('icons'));
		$params->def('readmore',		$app->getCfg('readmore'));
		$params->def('back_button', $app->getCfg('back_button'));
		
		/*
		 * Get some item specific parameters
		 */
		$params->def('image',					1);
		$params->def('section',				0);
		$params->def('section_link',		0);
		$params->def('category',			0);
		$params->def('category_link',	0);
		$params->def('introtext',			1);
		$params->def('pageclass_sfx',	'');
		$params->def('item_title',			1);
		$params->def('url',						1);

		if (!$showImages)
		{
			$params->set('image',	0);
		}
		
		/*
		 * Process the content preparation plugins
		 */
		$row->text	= $row->introtext;
		JPluginHelper::importPlugin('content');
		$results = $app->triggerEvent('onPrepareContent', array (& $row, & $params, 0));

		// adds mospagebreak heading or title to <site> Title
		if (isset ($row->page_title))
		{
			$app->setPageTitle($row->title.' '.$row->page_title);
		}

		// determines the link and link text of the readmore button
		if (($params->get('readmore') && @ $row->readmore) || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($row->access <= $gid)
				{
					if ($task != 'view')
					{
						$cache = & JFactory::getCache('getItemid');
						$Itemid = $cache->call( 'JContentHelper::getItemid', $row->id);
					}
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$row->id."&amp;Itemid=".$Itemid);
					$linkText = JText::_('Read more...');
				}
				else
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}

		// edit icon
		if ($access->canEdit)
		{
			?>
			<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">				
				<?php JContentHTMLHelper::editIcon($row, $params, $access); ?>
			</div>
			<?php

		}

		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email'))
		{
			// link used by print button
			$print_link = $app->getCfg('live_site').'/index2.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid.'&amp;pop=1';
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php


			// displays Item Title
			JContentHTMLHelper::title($row, $params, $linkOn, $access);

			// displays PDF Icon
			JContentHTMLHelper::pdfIcon($row, $params, $linkOn, $noJS);

			// displays Print Icon
			mosHTML::PrintIcon($row, $params, $noJS, $print_link);

			// displays Email Icon
			JContentHTMLHelper::emailIcon($row, $params, $noJS);
			?>
			</tr>
			</table>
			<?php

		}

		if (!$params->get('intro_only'))
		{
			$results = $app->triggerEvent('onAfterDisplayTitle', array (& $row, & $params,0));
			echo trim(implode("\n", $results));
		}

		$onBeforeDisplayContent = $app->triggerEvent('onBeforeDisplayContent', array (& $row, & $params, 0));
		echo trim(implode("\n", $onBeforeDisplayContent));
		?>

		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php


		// displays Section & Category
		JContentHTMLHelper::sectionCategory($row, $params);

		// displays Author Name
		JContentHTMLHelper::author($row, $params);

		// displays Created Date
		JContentHTMLHelper::createDate($row, $params);

		// displays Urls
		JContentHTMLHelper::url($row, $params);
		?>
		<tr>
			<td valign="top" colspan="2">
				<?php

		// displays Table of Contents
		JContentHTMLHelper::toc($row);

		// displays Item Text
		echo ampReplace($row->text);
		?>
			</td>
		</tr>
		<?php


		// displays Modified Date
		JContentHTMLHelper::modifiedDate($row, $params);

		// displays Readmore button
		JContentHTMLHelper::readMore($params, $linkOn, $linkText);
		?>
		</table>
		<span class="article_seperator">&nbsp;</span>

		<?php

		// Fire the after display content event
		$onAfterDisplayContent = $app->triggerEvent('onAfterDisplayContent', array (& $row, & $params, 0));
		echo trim(implode("\n", $onAfterDisplayContent));

		// displays the next & previous buttons
		//JContentHTMLHelper::navigation($row, $params);
	}

	function showLinks(& $rows, $links, $total, $i = 0)
	{
		?>
			<div>
				<strong>
				<?php echo JText::_( 'Read more...' ); ?>
				</strong>
			</div>
			
			<ul>
		<?php
		for ($z = 0; $z < $links; $z ++)
		{
			if ($i >= $total)
			{
				/*
				 * Stop the loop if the total number of items is less than the
				 * number of items set to display
				 */
				break;
			}
			$cache		= & JFactory::getCache('getItemid');
			$Itemid	= $cache->call( 'JContentHelper::getItemid', $rows[$i]->id);
			$link			= sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$rows[$i]->id.'&amp;Itemid='.$Itemid)
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
}
?>