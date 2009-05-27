<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewArticle extends JView
{
	function display($tpl = null)
	{
		global $mainframe;
		$user		= &JFactory::getUser();
		$groups		= $user->authorisedLevels();
		$dispatcher	= &JDispatcher::getInstance();

		// Initialize some variables
		$article	= & $this->get('Article');
		$params 	= & $article->parameters;

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorize('com_content.article.edit_article');
		$access->canEditOwn	= $user->authorize('com_content.article.edit_own');
		$access->canPublish	= $user->authorize('com_content.article.publish');

		// Check to see if the user has access to view the full article
		if (!in_array($article->access, $groups)) {
			// Redirect to login
			$uri		= JFactory::getURI();
			$return		= $uri->toString();

			$url  = 'index.php?option=com_users&view=login';
			$url .= '&return='.base64_encode($return);;

			//$url	= JRoute::_($url, false);
			$mainframe->redirect($url, JText::_('You must login first'));
		}
		else if (!$user->get('guest'))
		{
			$document = &JFactory::getDocument();
			$document->setTitle($article->title);
			$document->setHeader($this->_getHeaderText($article, $params));
			echo '<h1>' . JText::_('ALERTNOTAUTH') . '</h1>';
			return;
		}

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
			$text .= JText::sprintf('Written by', ($article->created_by_alias ? $article->created_by_alias : $article->author));
		}

		if ($params->get('show_create_date') && $params->get('show_author')) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('show_create_date')) {
			// Display Created Date
			if (intval($article->created)) {
				$create_date = JHtml::_('date', $article->created, JText::_('DATE_FORMAT_LC2'));
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
				$mod_date = JHtml::_('date', $article->modified, JText::_('DATE_FORMAT_LC2'));
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		return $text;
	}
}
?>