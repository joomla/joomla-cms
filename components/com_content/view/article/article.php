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
 * HTML Article View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JViewHTMLArticle extends JView
{
	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Article';

	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		/*
		 * Initialize some variables
		 */
		$app			= & $this->get( 'Application' );
		$user			= & $app->getUser();
		$menu			= & $this->get( 'Menu' );
		$SiteName	= $app->getCfg('sitename');
		$Itemid		= $menu->id;
		$page			= JRequest::getVar('limitstart', 0, '', 'int');
		$noJS 			= JRequest::getVar( 'hide_js', 0, '', 'int' );
		$noHTML		= JRequest::getVar('no_html', 0, '', 'int');
		$linkOn			= null;
		$linkText		= null;

		// Get the article from the model
		$article		= & $this->get( 'Article' );
		$params	= $article->parameters;

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		/*
		 * Handle BreadCrumbs and Page Title
		 */
		$breadcrumbs = & $app->getPathWay();
		if (!empty ($Itemid))
		{
			// Section
			if (!empty ($article->section))
			{
				$breadcrumbs->addItem($article->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$article->sectionid.'&amp;Itemid='.$Itemid));
			}
			// Category
			if (!empty ($article->section))
			{
				$breadcrumbs->addItem($article->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$article->sectionid.'&amp;id='.$article->catid.'&amp;Itemid='.$Itemid));
			}
		}
		// Item
		$breadcrumbs->addItem($article->title, '');
		$app->setPageTitle($article->title);
		$app->appendMetaTag('description', $article->metadesc);
		$app->appendMetaTag('keywords', $article->metakey);

		// process the new plugins
		JPluginHelper::importPlugin('content');
		$results = $app->triggerEvent('onPrepareContent', array (& $article, & $params, $page));

		// adds mospagebreak heading or title to <site> Title
		if (isset ($article->page_title))
		{
			$app->setPageTitle($article->title.' '.$article->page_title);
		}

		// determines the link and link text of the readmore button
		if (($params->get('readmore') && @ $article->readmore) || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($article->access <= $user->get('gid'))
				{
					$cache = & JFactory::getCache('getItemid');
					$Itemid = $cache->call( 'JContentHelper::getItemid', $article->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$article->id."&amp;Itemid=".$Itemid);
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
		if ($params->get('popup') && $noHTML == 0)
		{
			$app->setPageTitle($SiteName.' - '.$article->title);
		}

		// edit icon
		if ($access->canEdit)
		{
			?>
			<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">				
				<?php JContentHTMLHelper::editIcon($article, $params, $access); ?>
			</div>
			<?php


		}

		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email'))
		{
			// link used by print button
			$print_link = $app->getCfg('live_site').'/index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $page;
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php


			// displays Item Title
			JContentHTMLHelper::title($article, $params, $linkOn, $access);

			// displays PDF Icon
			JContentHTMLHelper::pdfIcon($article, $params, $linkOn, $noJS);

			// displays Print Icon
			mosHTML::PrintIcon($article, $params, $noJS, $print_link);

			// displays Email Icon
			JContentHTMLHelper::emailIcon($article, $params, $noJS);
			?>
			</tr>
			</table>
			<?php


		}

		if (!$params->get('intro_only'))
		{
			$results = $app->triggerEvent('onAfterDisplayTitle', array (& $article, & $params, $page));
			echo trim(implode("\n", $results));
		}

		$onBeforeDisplayContent = $app->triggerEvent('onBeforeDisplayContent', array (& $article, & $params, $page));
		echo trim(implode("\n", $onBeforeDisplayContent));
		?>

		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php


		// displays Section & Category
		JContentHTMLHelper::sectionCategory($article, $params);

		// displays Author Name
		JContentHTMLHelper::author($article, $params);

		// displays Created Date
		JContentHTMLHelper::createDate($article, $params);

		// displays Urls
		JContentHTMLHelper::url($article, $params);
		?>
		<tr>
			<td valign="top" colspan="2">
				<?php


		// displays Table of Contents
		JContentHTMLHelper::toc($article);

		// displays Item Text
		echo ampReplace($article->text);
		?>
			</td>
		</tr>
		<?php


		// displays Modified Date
		JContentHTMLHelper::modifiedDate($article, $params);

		// displays Readmore button
		JContentHTMLHelper::readMore($params, $linkOn, $linkText);
		?>
		</table>
		<span class="article_seperator">&nbsp;</span>

		<?php


		// Fire the after display content event
		$onAfterDisplayContent = $app->triggerEvent('onAfterDisplayContent', array (& $article, & $params, $page));
		echo trim(implode("\n", $onAfterDisplayContent));

		// displays the next & previous buttons
		//JContentHTMLHelper::navigation($article, $params);

		// displays close button in pop-up window
		mosHTML::CloseButton($params, $noJS);

	}

	function _buildEditLists()
	{
		// Get the article from the model
		$article		= & $this->get( 'Article' );

		// Read the JPATH_ROOT/images/stories/ folder
		$pathA			= 'images/stories';
		$pathL			= 'images/stories';
		$images		= array ();
		$folders		= array ();
		$folders[]		= mosHTML::makeOption('/');
		mosAdminMenus::ReadImages($pathA, '/', $folders, $images);

		// Select List: Subfolders in the JPATH_ROOT/images/stories/ folder
		$lists['folders'] = mosAdminMenus::GetImageFolders($folders, $pathL);

		// Select List: Images in the JPATH_ROOT/images/stories/ folder
		$lists['imagefiles'] = mosAdminMenus::GetImages($images, $pathL);

		// Select List: Saved Images
		$lists['imagelist'] = mosAdminMenus::GetSavedImages($article, $pathL);

		// Select List: Image Positions
		$lists['_align'] = mosAdminMenus::Positions('_align');

		// Select List: Image Caption Alignment
		$lists['_caption_align'] = mosAdminMenus::Positions('_caption_align');

		// Select List: Image Caption Position
		$pos[] = mosHTML::makeOption('bottom', JText::_('Bottom'));
		$pos[] = mosHTML::makeOption('top', JText::_('Top'));
		$lists['_caption_position'] = mosHTML::selectList($pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text');

		// Select List: Categories
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', $article->sectionid, intval($article->catid));

		// Select List: Category Ordering
		$query = "SELECT ordering AS value, title AS text" .
				"\n FROM #__content" .
				"\n WHERE catid = $article->catid" .
				"\n ORDER BY ordering";
		$lists['ordering'] = mosAdminMenus::SpecificOrdering($article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = mosHTML::yesnoradioList('state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		$query = "SELECT content_id" .
				"\n FROM #__content_frontpage" .
				"\n WHERE content_id = $row->id";
		$db->setQuery($query);
		$row->frontpage = $db->loadResult();
		$lists['frontpage'] = mosHTML::yesnoradioList('frontpage', '', $article->frontpage);

		// Select List: Group Access
		$lists['access'] = mosAdminMenus::Access($article);

		return $lists;
	}
}
?>