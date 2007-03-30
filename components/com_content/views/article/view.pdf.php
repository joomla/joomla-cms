<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewArticle extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$dispatcher	=& JEventDispatcher::getInstance();

		// Initialize some variables
		$article	= & $this->get( 'Article' );
		$params 	= & $article->parameters;
		$params->def('introtext', 1);
		$params->set('intro_only', 0);

		// show/hides the intro text
		if ($params->get('introtext')) {
			$article->text = $article->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$article->fulltext);
		} else {
			$article->text = $article->fulltext;
		}

		// process the new plugins
		JPluginHelper::importPlugin('content', 'image', false);
		$dispatcher->trigger('onPrepareContent', array (& $article, & $params, 0));

		$document = &JFactory::getDocument();

		// set document information
		$document->setTitle($article->title);
		$document->setName($article->title_alias);
		$document->setDescription($article->metadesc);
		$document->setMetaData('keywords', $article->metakey);

		// prepare header lines
		$document->setHeader($this->_getHeaderText($article, $params));

		$document->setData($article->text);
	}

	function _getHeaderText(& $article, & $params)
	{
		// Initialize some variables
		$text = '';

		if ($params->get('showAuthor')) {
			// Display Author name
			if ($article->usertype == 'administrator' || $article->usertype == 'superadministrator') {
				$text .= "\n";
				$text .= JText::_('Written by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			} else {
				$text .= "\n";
				$text .= JText::_('Contributed by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			}
		}

		if ($params->get('createdate') && $params->get('showAuthor')) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('createdate')) {
			// Display Created Date
			if (intval($article->created)) {
				$create_date = JHTML::Date($article->created);
				$text .= $create_date;
			}
		}

		if ($params->get('modifydate') && ($params->get('showAuthor') || $params->get('createdate'))) {
			// Display Separator
			$text .= " - ";
		}

		if ($params->get('modifydate')) {
			// Display Modified Date
			if (intval($article->modified)) {
				$mod_date = JHTML::Date($article->modified);
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		return $text;
	}
}
?>