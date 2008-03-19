<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

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

		$dispatcher	=& JDispatcher::getInstance();

		// Initialize some variables
		$article	= & $this->get( 'Article' );
		$params 	= & $article->parameters;

		// process the new plugins
		JPluginHelper::importPlugin('content', 'image');
		$dispatcher->trigger('onPrepareContent', array (& $article, & $params, 0));

		$document = &JFactory::getDocument();

		// set document information
		$document->setTitle($article->title);
		$document->setName($article->alias);
		$document->setDescription($article->metadesc);
		$document->setMetaData('keywords', $article->metakey);

		// prepare header lines
		$document->setHeader($this->_getHeaderText($article, $params));

		echo $article->text;
	}

	function _getHeaderText(& $article, & $params)
	{
		// Initialize some variables
		$text = '';

		// Display Author name
		if ($params->get('show_author')) {
			// Display Author name
			$text .= "\n";
			$text .= JText::sprintf( 'Written by', ($article->created_by_alias ? $article->created_by_alias : $article->author) );
		}

		if ($params->get('show_create_date') && $params->get('show_author')) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('show_create_date')) {
			// Display Created Date
			if (intval($article->created)) {
				$create_date = JHTML::_('date', $article->created, JText::_('DATE_FORMAT_LC2'));
				$text .= $create_date;
			}
		}

		if ($params->get('show_modify_date') && ($params->get('show_author') || $params->get('show_create_date'))) {
			// Display Separator
			$text .= " - ";
		}

		if ($params->get('show_modify_date')) {
			// Display Modified Date
			if (intval($article->modified)) {
				$mod_date = JHTML::_('date', $article->modified, JText::_('DATE_FORMAT_LC2'));
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		return $text;
	}
}
?>