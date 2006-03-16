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
 * @since 1.1
 */
class JContentViewHTML_item
{

	function show(& $row, & $params, & $access, $page = 0)
	{
		global $mainframe, $hide_js;

		/*
		 * Initialize some variables
		 */
		$user			= & $mainframe->getUser();
		$SiteName	= $mainframe->getCfg('sitename');
		$gid				= $user->get('gid');
		$task			= JRequest::getVar('task');
		$page			= JRequest::getVar('limitstart', 0, '', 'int');
		$no_html		= JRequest::getVar('no_html', 0, '', 'int');
		$Itemid		= JRequest::getVar('Itemid', 9999, '', 'int');
		$linkOn			= null;
		$linkText		= null;

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
						$cache = JFactory::getCache('getItemid');
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