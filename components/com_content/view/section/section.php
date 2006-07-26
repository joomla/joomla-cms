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

jimport( 'joomla.application.view');

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentViewSection extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Section';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		$document	= &$this->getDocument();
		switch ($document->getType())
		{
			case 'feed':
				$this->displayFeed();
				break;
			default:
				$this->displayHtml();
				break;
		}
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayHtml()
	{
		// Initialize some variables
		$app	= &$this->getApplication();
		$user	= &$app->getUser();
		$doc	= &$app->getDocument();

		$gid 	= $user->get('gid');

		// Model workaround
		$ctrl	= &$this->getController();
		$model	= & $ctrl->getModel('section', 'JContentModel');
		$this->setModel($model, true);

		// Get some data from the model
		$section	= & $this->get( 'Section' );

		// Request variables
		$task 	= JRequest::getVar('task');
		$id 	= JRequest::getVar('id');
		$option = JRequest::getVar('option');
		$Itemid = JRequest::getVar('Itemid');

		// Get the menu object of the active menu item
		$menus	= &JMenu::getInstance();
		$menu	= &$menus->getItem($Itemid);

		//add alternate feed link
		$link    = $app->getBaseURL() .'feed.php?option=com_content&task='.$task.'&id='.$id.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$doc->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$doc->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Set the page title and breadcrumbs
		$breadcrumbs = & $app->getPathWay();
		$breadcrumbs->addItem($section->title, '');

		if (!empty ($menu->name)) {
			$app->setPageTitle($menu->name);
		}

		$cParams = &JComponentHelper::getControlParams();
		$template = JRequest::getVar( 'tpl', $cParams->get( 'template_name', 'list' ) );
		$template = preg_replace( '#\W#', '', $template );
		$tmplPath = dirname( __FILE__ ) . '/tmpl/' . $template . '.php';

		if (!file_exists( $tmplPath ))
		{
			$tmplPath = dirname( __FILE__ ) . '/tmpl/list.php';
		}

		require($tmplPath);
	}

	function showItem( &$row, &$access, $showImages = false )
	{
		require_once( JPATH_COM_CONTENT . '/helpers/article.php' );
		JContentArticleHelper::showItem( $this, $row, $access, $showImages );
	}

	function showLinks(& $rows, $links, $total, $i = 0)
	{
		require_once( JPATH_COM_CONTENT . '/helpers/article.php' );
		JContentArticleHelper::showLinks( $rows, $links, $total, $i );
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayFeed()
	{
		$app =& $this->getApplication();
		$doc = $app->getDocument();

		// Lets get our data from the model
		$rows = & $this->get( 'Section' );

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = JContentHelper::getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}

			$link = 'index.php?option=com_content&task=view&id='. $row->id . $_Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->introtext;
			@$date = ( $row->created ? date( 'r', $row->created ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $row->category;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}
}
?>
