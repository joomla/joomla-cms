<?php
/**
 * @version $Id: section.html.php 3393 2006-05-05 23:26:10Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
		$menus	= JMenu::getInstance();
		$menu	= &$menus->getCurrent();
		$params	= &JComponentHelper::getMenuParams();
		$doc	= & $app->getDocument();

		// workaround
		$ctrl	= &$this->getController();
		$section = & $ctrl->getModel('section', 'JContentModel');
		$this->setModel($section, true);


		$Itemid = $menu->id;

		$gid 	= $user->get('gid');
		$task 	= JRequest::getVar('task');
		$id 	= JRequest::getVar('id');
		$option = JRequest::getVar('option');

		// Lets get our data from the model
		$section		= & $this->get( 'Section' );
		$categories	= & $this->get( 'Categories' );

		//add alternate feed link
		$link    = $app->getBaseURL() .'feed.php?option=com_content&task='.$task.'&id='.$id.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$doc->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$doc->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		/*
		 * Lets set the page title
		 */
		if (!empty ($menu->name)) {
			$app->setPageTitle($menu->name);
		}

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $app->getPathWay();
		$breadcrumbs->addItem($section->title, '');

		$template = 'default';

		include (dirname( __FILE__ ) . '/tmpl/' . $template . '.php' );
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

		//Initialize some variables
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$params		= &JComponentHelper::getMenuParams();
		$Itemid		= $menu->id;

		// Lets get our data from the model
		$rows = & $this->get( 'Section' );

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = $app->getItemid( $row->id );
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
