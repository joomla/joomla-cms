<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

class JContentArticleHelper
{
	function showItem( &$parent, &$article, &$access, $showImages = false)
	{
		// Initialize some variables
		$app		= &$parent->getApplication();
		$user		= &$app->getUser();
		$linkOn		= null;
		$linkText	= null;

		// These will come from a request object at some point
		$task   = JRequest::getVar('task');
		$noJS   = JRequest::getVar('hide_js', 0, '', 'int');
		$Itemid = JRequest::getVar('Itemid');

		// Get the paramaters of the active menu item
		$menus  =& JMenu::getInstance();
		$params =& $menus->getParams($Itemid);

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

		// Process the content plugins
		$article->text = $article->introtext;
		JPluginHelper::importPlugin('content');
		$results = $app->triggerEvent('onPrepareContent', array (& $article, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('readmore') && @ $article->readmore) || $params->get('link_titles')) {
			if ($params->get('intro_only')) {
				// Check to see if the user has access to view the full article
				if ($article->access <= $gid) {
					$Itemid = JContentHelper::getItemid($article->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$article->id."&amp;Itemid=".$Itemid);
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
				<?php JContentHTMLHelper::editIcon($article, $params, $access); ?>
			</div>
			<?php
		}

		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email')) {
			// link used by print button
			$printLink = $app->getBaseURL().'index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1';
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php

			// displays Item Title
			JContentHTMLHelper::title($article, $params, $linkOn, $access);

			// displays PDF Icon
			JContentHTMLHelper::pdfIcon($article, $params, $linkOn, $noJS);

			// displays Print Icon
			mosHTML::PrintIcon($article, $params, $noJS, $printLink);

			// displays Email Icon
			JContentHTMLHelper::emailIcon($article, $params, $noJS);
			?>
			</tr>
			</table>
			<?php
		}

		// If only displaying intro, display the output from the onAfterDisplayTitle event
		if (!$params->get('intro_only')) {
			$results = $app->triggerEvent('onAfterDisplayTitle', array (& $article, & $params, 0));
			echo trim(implode("\n", $results));
		}

		// Display the output from the onBeforeDisplayContent event
		$onBeforeDisplayContent = $app->triggerEvent('onBeforeDisplayContent', array (& $article, & $params, 0));
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
		$onAfterDisplayContent = $app->triggerEvent('onAfterDisplayContent', array (& $article, & $params, 0));
		echo trim(implode("\n", $onAfterDisplayContent));
	}

	function showLinks(& $articles, $links, $total, $i = 0)
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
			$Itemid = JContentHelper::getItemid($articles[$i]->id);
			$link = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$articles[$i]->id.'&amp;Itemid='.$Itemid)
			?>
			<li>
				<a class="blogsection" href="<?php echo $link; ?>">
				<?php echo $articles[$i]->title; ?>
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