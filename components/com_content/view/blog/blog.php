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
class JViewHTMLBlog extends JView
{
	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Blog';

	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		// Initialize some variables
		$app = & $this->get('Application');
		$user = & $app->getUser();
		$menu = & $this->get('Menu');
		$params = & $menu->parameters;
		$Itemid = $menu->id;

		$gid = $user->get('gid');
		$task = JRequest::getVar('task');
		$id = JRequest::getVar('id');
		$option = JRequest::getVar('option');

		/*
		 * Create a user access object for the user
		 */
		$access = new stdClass();
		$access->canEdit = $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn = $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish = $user->authorize('action', 'publish', 'content', 'all');

		/*
		 * Menu item parameters
		 */
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
		$usePagination = $params->def('pagination', 2);
		$showPaginationResults = $params->def('pagination_results', 1);
		$descrip = $params->def('description', 1);
		$descrip_image = $params->def('description_image', 1);

		$params->def('pageclass_sfx', '');
		$params->set('intro_only', 1);

		/*
		 * Lets get the content item data from the model
		 */
		$rows = & $this->get('Content');

		// Dynamic Page Title and BreadCrumbs
		$breadcrumbs = & $app->getPathWay();
		$document = & $app->getDocument();
		if ($menu->name)
		{
			$document->setTitle($menu->name);
			$breadcrumbs->addItem($menu->name, '');
		} else
		{
			$breadcrumbs->addItem($rows[0]->section, '');
		}

		/*
		 * Pagination support
		 */
		$total = count($rows);
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$limit = $intro + $leading + $links;
		if ($total <= $limit)
		{
			$limitstart = 0;
		}
		$i = $limitstart;

		/*
		 * Set section/category description text and images for blog sections
		 * and categories
		 */
		if ($menu && $menu->componentid && ($descrip || $descrip_image))
		{
			switch ($menu->type)
			{
				case 'content_blog_section' :
					$description = & JTable::getInstance('section', $this->get('DBO'));
					$description->load($menu->componentid);
					break;

				case 'content_blog_category' :
					$description = & JTable::getInstance('category', $this->get('DBO'));
					$description->load($menu->componentid);
					break;

				default :
					$menu->componentid = 0;
					break;
			}
		}

		/*
		 * Header output
		 */
		if ($header)
		{
			echo '<div class="componentheading'.$params->get('pageclass_sfx').'">'.$header.'</div>';
		}

		/*
		 * Do we have any items to display?
		 */
		if ($total)
		{
			$col_width = 100 / $columns; // width of each column
			$width = 'width="'.intval($col_width).'%"';

			echo '<table class="blog'.$params->get('pageclass_sfx').'" cellpadding="0" cellspacing="0">';

			// Secrion/Category Description & Image
			if ($menu && $menu->componentid && ($descrip || $descrip_image))
			{
				$link = 'images/stories/'.$description->image;
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
					$this->showItem($rows[$i], $access, true);
					echo '</div>';
				}
				echo '</td>';
				echo '</tr>';
			} else
			{
				$i = 0;
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
							$this->showItem($rows[$i], $access);
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
				} else
				{
					// get the total number of records
					$limitstart = $limitstart ? $limitstart : 0;
					jimport('joomla.presentation.pagination');
					$pagination = new JPagination($total, $limitstart, $limit);

					if ($option == 'com_frontpage')
					{
						$link = 'index.php?option=com_frontpage&amp;Itemid='.$Itemid;
					} else
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

		} else
		{
			// Generic blog empty display
			echo '<p>'.JText::_(_EMPTY_BLOG).'</p>';
		}

	}

	function showItem(& $row, & $access, $showImages = false)
	{
			// Initialize some variables
	$app = & $this->get('Application');
		$user = & $app->getUser();
		$menu = & $this->get('Menu');
		$Itemid = $menu->id;
		$params = & new JParameter($row->attribs);

		// These will come from a request object at some point
		$task = JRequest::getVar('task');
		$noJS = JRequest::getVar('hide_js', 0, '', 'int');
		$noHTML = JRequest::getVar('no_html', 0, '', 'int');

		// TODO: clean this part up
		$SiteName = $app->getCfg('sitename');
		$gid = $user->get('gid');

		// Get some global parameters
		$params->def('link_titles', $app->getCfg('link_titles'));
		$params->def('author', !$app->getCfg('hideAuthor'));
		$params->def('createdate', !$app->getCfg('hideCreateDate'));
		$params->def('modifydate', !$app->getCfg('hideModifyDate'));
		$params->def('print', !$app->getCfg('hidePrint'));
		$params->def('pdf', !$app->getCfg('hidePdf'));
		$params->def('email', !$app->getCfg('hideEmail'));
		$params->def('rating', $app->getCfg('vote'));
		$params->def('icons', $app->getCfg('icons'));
		$params->def('readmore', $app->getCfg('readmore'));
		$params->def('back_button', $app->getCfg('back_button'));
		$params->set('intro_only', 1);

		// Get some article specific parameters
		$params->def('image', 1);
		$params->def('section', 0);
		$params->def('section_link', 0);
		$params->def('category', 0);
		$params->def('category_link', 0);
		$params->def('introtext', 1);
		$params->def('pageclass_sfx', '');
		$params->def('item_title', 1);
		$params->def('url', 1);

		if (!$showImages) {
			$params->set('image', 0);
		}

		// Process the content preparation plugins
		$row->text = $row->introtext;
		JPluginHelper::importPlugin('content');
		$results = $app->triggerEvent('onPrepareContent', array (& $row, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('readmore') && @ $row->readmore) || $params->get('link_titles')) {
			if ($params->get('intro_only')) {
				// Check to see if the user has access to view the full article
				if ($row->access <= $gid) {
					$Itemid = JContentHelper::getItemid($row->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$row->id."&amp;Itemid=".$Itemid);
					$linkText = JText::_('Read more...');
				} else {
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}

		// Display the edit icon if appropriate
		if ($access->canEdit) {
			?>
			<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">				
				<?php JContentHTMLHelper::editIcon($row, $params, $access); ?>
			</div>
			<?php
		}

		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email')) {
			// link used by print button
			$printLink = $app->getBaseURL().'index2.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid.'&amp;pop=1';
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php

			// displays Item Title
			JContentHTMLHelper::title($row, $params, $linkOn, $access);

			// displays PDF Icon
			JContentHTMLHelper::pdfIcon($row, $params, $linkOn, $noJS);

			// displays Print Icon
			mosHTML::PrintIcon($row, $params, $noJS, $printLink);

			// displays Email Icon
			JContentHTMLHelper::emailIcon($row, $params, $noJS);
			?>
			</tr>
			</table>
			<?php
		}

		if (!$params->get('intro_only')) {
			$results = $app->triggerEvent('onAfterDisplayTitle', array (& $row, & $params, 0));
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

		for ($j = 0; $j < $links; $j ++)
		{
			if ($i >= $total) {
				/*
				 * Stop the loop if the total number of items is less than the
				 * number of items set to display
				 */
				break;
			}
			$Itemid = JContentHelper::getItemid($rows[$i]->id);
			$link = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$rows[$i]->id.'&amp;Itemid='.$Itemid)
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