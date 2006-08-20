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

// no direct access
defined('_JEXEC') or die('Restricted access');

		// Process the content plugins
		JPluginHelper::importPlugin('content');
		$results = $mainframe->triggerEvent('onPrepareContent', array (& $article, & $params, $page));

		// Build the link and text of the readmore button
		if ($params->get('readmore') || $params->get('link_titles')) {
			if ($params->get('intro_only')) {
				// Check to see if the user has access to view the full article
				if ($article->access <= $user->get('gid')) {
					$Itemid = JContentHelper::getItemid($article->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$article->id."&amp;Itemid=".$Itemid);

					if (@$article->readmore) {
					// text for the readmore link
						$linkText = JText::_('Read more...');
					}
				} else {
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");

					if (@$article->readmore) {
					// text for the readmore link if accessible only if registered
						$linkText = JText::_('Register to read more...');
					}
				}
			}
		}

		// Popup pages get special treatment for page titles
		if ($params->get('popup') && $type =! 'html') {
			$doc->setTitle($mainframe->getCfg('sitename').' - '.$article->title);
		}

		// If the user can edit the article, display the edit icon
		if ($access->canEdit) {
			?>
			<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">
				<?php JContentHTMLHelper::editIcon($article, $params, $access); ?>
			</div>
			<?php
		}

		// Time to build the title bar... this may also include the pdf/print/email buttons if enabled
		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email')) {
			// Build the link for the print button
			$printLink = $mainframe->getBaseURL().'index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $page;
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
			$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (& $article, & $params, $page));
			echo trim(implode("\n", $results));
		}

		// Display the output from the onBeforeDisplayContent event
		$onBeforeDisplayContent = $mainframe->triggerEvent('onBeforeDisplayContent', array (& $article, & $params, $page));
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
		$onAfterDisplayContent = $mainframe->triggerEvent('onAfterDisplayContent', array (& $article, & $params, $page));
		echo trim(implode("\n", $onAfterDisplayContent));

		// displays close button in pop-up window
		mosHTML::CloseButton($params, $noJS);

?>