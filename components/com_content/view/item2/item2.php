<?php
/**
 * @version $Id: item.php 2857 2006-03-21 07:43:37Z webImagery $
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
 * @since 1.1
 */
class JViewContent_item2 extends JView
{
	/**
	 * Method to show a content item as the main page display
	 *
	 * @return array
	 * @since 1.0
	 */
	function &getData()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db			= &$mainframe->getDBO();
		$user		= &$mainframe->getUser();
		$MetaTitle	= $mainframe->getCfg('MetaTitle');
		$MetaAuthor	= $mainframe->getCfg('MetaAuthor');
		$voting		= $mainframe->getCfg('vote');
		$now		= $mainframe->get('requestTime');
		$noauth		= !$mainframe->getCfg('shownoauth');
		$nullDate	= $db->getNullDate();
		$gid		= $user->get('gid');
		$option		= JRequest::getVar('option');
		$uid		= JRequest::getVar('id',			0, '', 'int');
		$pop		= JRequest::getVar('pop',			0, '', 'int');
		$limit		= JRequest::getVar('limit',			0, '', 'int');
		$limitstart	= JRequest::getVar('limitstart',	0, '', 'int');
		$row		= null;

		/*
		 * Create a user access object for the user
		 */
		$access				= new stdClass();
		$access->canEdit	= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('action', 'publish', 'content', 'all');

		/*
		 * Get a content component cache object
		 */
		$cache = & JFactory::getCache('com_content');

		if ($access->canEdit)
		{
			$xwhere = '';
		}
		else
		{
			$xwhere = " AND ( a.state = 1 OR a.state = -1 )" .
					"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
					"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
		}

		$voting = JContentHelper::buildVotingQuery();

		// Main content item query
		$query = "SELECT a.*, u.name AS author, u.usertype, cc.title AS category, s.title AS section," .
				"\n g.name AS groups, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access".$voting['select'].
				"\n FROM #__content AS a" .
				"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
				"\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id".
				$voting['join'].
				"\n WHERE a.id = $uid".
				$xwhere.
				"\n AND a.access <= $gid";
		$db->setQuery($query);

		if ($db->loadObject($row))
		{
			if (!$row->cat_pub && $row->catid)
			{
				// check whether category is published
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			if (!$row->sec_pub && $row->sectionid)
			{
				// check whether section is published
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			if (($row->cat_access > $gid) && $row->catid)
			{
				// check whether category access level allows access
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
			if (($row->sec_access > $gid) && $row->sectionid)
			{
				// check whether section access level allows access
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}

			$params = new JParameter($row->attribs);
			$params->set('intro_only', 0);
			$params->def('back_button', $mainframe->getCfg('back_button'));
			if ($row->sectionid == 0)
			{
				$params->set('item_navigation', 0);
			}
			else
			{
				$params->set('item_navigation', $mainframe->getCfg('item_navigation'));
			}
			if ($MetaTitle == '1')
			{
				$mainframe->addMetaTag('title', $row->title);
			}
			if ($MetaAuthor == '1')
			{
				$mainframe->addMetaTag('author', $row->author);
			}

			/*
			 * Handle BreadCrumbs and Page Title
			 */
			$breadcrumbs = & $mainframe->getPathWay();
			if (!empty ($Itemid))
			{
				// Section
				if (!empty ($row->section))
				{
					$breadcrumbs->addItem($row->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$row->sectionid.'&amp;Itemid='.$Itemid));
				}
				// Category
				if (!empty ($row->section))
				{
					$breadcrumbs->addItem($row->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$row->sectionid.'&amp;id='.$row->catid.'&amp;Itemid='.$Itemid));
				}
			}
			// Item
			$breadcrumbs->addItem($row->title, '');
			$mainframe->setPageTitle($row->title);

			if ($access->canEdit)
			{
				if ($row->id === null || $row->access > $gid)
				{
					JError::raiseError( 404, JText::_("Resource Not Found") );
				}
			}
			else
			{
				if ($row->id === null || $row->state == 0)
				{
					JError::raiseError( 404, JText::_("Resource Not Found") );
				}
				if ($row->access > $gid)
				{
					if ($noauth)
					{
						JError::raiseError( 403, JText::_("Access Forbidden") );
					}
					else
					{
						if (!($params->get('intro_only')))
						{
							JError::raiseError( 403, JText::_("Access Forbidden") );
						}
					}
				}
			}

			/*
			 * Get some parameters from global configuration
			 */
			$params->def('link_titles',		$mainframe->getCfg('link_titles'));
			$params->def('author',			!$mainframe->getCfg('hideAuthor'));
			$params->def('createdate',	!$mainframe->getCfg('hideCreateDate'));
			$params->def('modifydate',	!$mainframe->getCfg('hideModifyDate'));
			$params->def('print',				!$mainframe->getCfg('hidePrint'));
			$params->def('pdf',					!$mainframe->getCfg('hidePdf'));
			$params->def('email',				!$mainframe->getCfg('hideEmail'));
			$params->def('rating',				$mainframe->getCfg('vote'));
			$params->def('icons',				$mainframe->getCfg('icons'));
			$params->def('readmore',		$mainframe->getCfg('readmore'));
			
			/*
			 * Get some item specific parameters
			 */
			$params->def('image',					1);
			$params->def('section',				0);
			$params->def('popup',					$pop);
			$params->def('section_link',		0);
			$params->def('category',			0);
			$params->def('category_link',	0);
			$params->def('introtext',			1);
			$params->def('pageclass_sfx',	'');
			$params->def('item_title',			1);
			$params->def('url',						1);
	
			if ($params->get('section_link') && $row->sectionid)
			{
				$row->section = JContentHelper::getSectionLink($row);
			}
	
			if ($params->get('category_link') && $row->catid)
			{
				$row->category = JContentHelper::getCategoryLink($row);
			}
	
			// show/hides the intro text
			if ($params->get('introtext'))
			{
				$row->text = $row->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$row->fulltext);
			}
			else
			{
				$row->text = $row->fulltext;
			}
	
			// record the hit
			if (!$params->get('intro_only') && ($limitstart == 0))
			{
				$obj = & JTable::getInstance('content', $db);
				$obj->hit($row->id);
			}
	
			//$cache = & JFactory::getCache('com_content');
			//$cache->call('JContentViewHTML::showItem', $row, $params, $access, $limitstart);

			$data['row'] = &$row;
			$data['params'] = &$params;
			$data['access'] = &$access;
			$data['limitstart'] = &$limitstart;

			return $data;
		}
		else
		{
			JError::raiseError( 404, JText::_("Resource Not Found") );
		}
	}

	function display()
	{
		global $mainframe, $hide_js;

		if (!$data = $this->getData()) {
			echo 'TODO: Um, I give up';
			die;
		}

		$row	= &$data['row'];
		$params	= &$data['params'];
		$access	= &$data['access'];
		$page	= &$data['page'];

		/*
		 * Initialize some variables
		 */
		$user		= & $mainframe->getUser();
		$SiteName	= $mainframe->getCfg('sitename');
		$gid		= $user->get('gid');
		$task		= JRequest::getVar('task');
		$page		= JRequest::getVar('limitstart', 0, '', 'int');
		$no_html	= JRequest::getVar('no_html', 0, '', 'int');
		$Itemid		= JRequest::getVar('Itemid', 9999, '', 'int');
		$linkOn		= null;
		$linkText	= null;

		$mainframe->appendMetaTag('description', $row->metadesc);
		$mainframe->appendMetaTag('keywords', $row->metakey);

		// process the new plugins
		JPluginHelper::importPlugin('content');
		$results = $mainframe->triggerEvent('onPrepareContent', array (& $row, & $params, $page));

		// adds mospagebreak heading or title to <site> Title
		if (isset ($row->page_title))
		{
			$mainframe->setPageTitle($row->title.' '.$row->page_title);
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

		/*
		 * Handle popup page
		 */
		if ($params->get('popup') && $no_html == 0)
		{
			$mainframe->setPageTitle($SiteName.' - '.$row->title);
		}

		// edit icon
		if ($access->canEdit)
		{
			?>
			<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">				
				<?php JContentViewHTMLHelper::editIcon($row, $params, $access); ?>
			</div>
			<?php


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
			JContentViewHTMLHelper::title($row, $params, $linkOn, $access);

			// displays PDF Icon
			JContentViewHTMLHelper::pdfIcon($row, $params, $linkOn, $hide_js);

			// displays Print Icon
			mosHTML::PrintIcon($row, $params, $hide_js, $print_link);

			// displays Email Icon
			JContentViewHTMLHelper::emailIcon($row, $params, $hide_js);
			?>
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
		JContentViewHTMLHelper::sectionCategory($row, $params);

		// displays Author Name
		JContentViewHTMLHelper::author($row, $params);

		// displays Created Date
		JContentViewHTMLHelper::createDate($row, $params);

		// displays Urls
		JContentViewHTMLHelper::url($row, $params);
		?>
		<tr>
			<td valign="top" colspan="2">
				<?php


		// displays Table of Contents
		JContentViewHTMLHelper::toc($row);

		// displays Item Text
		echo ampReplace($row->text);
		?>
			</td>
		</tr>
		<?php


		// displays Modified Date
		JContentViewHTMLHelper::modifiedDate($row, $params);

		// displays Readmore button
		JContentViewHTMLHelper::readMore($params, $linkOn, $linkText);
		?>
		</table>
		<span class="article_seperator">&nbsp;</span>

		<?php


		// Fire the after display content event
		$onAfterDisplayContent = $mainframe->triggerEvent('onAfterDisplayContent', array (& $row, & $params, $page));
		echo trim(implode("\n", $onAfterDisplayContent));

		// displays the next & previous buttons
		//JContentViewHTMLHelper::navigation($row, $params);

		// displays close button in pop-up window
		mosHTML::CloseButton($params, $hide_js);

	}
}
?>