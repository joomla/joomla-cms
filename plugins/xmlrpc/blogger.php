<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgXMLRPCBlogger extends JPlugin
{
	function plgXMLRPCBlogger(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	* @return array An array of associative arrays defining the available methods
	*/
	function onGetWebServices()
	{
		global $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		return array
		(
				'blogger.getUsersBlogs' => array(
				'function' => 'plgXMLRPCBloggerServices::getUserBlogs',
				'docstring' => JText::_('Returns a list of weblogs to which an author has posting privileges.'),
				'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString ))
			),
				'blogger.getUserInfo' => array(
				'function' => 'plgXMLRPCBloggerServices::getUserInfo',
				'docstring' => JText::_('Returns information about an author in the system.'),
				'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.getPost' => array(
				'function' => 'plgXMLRPCBloggerServices::getPost',
				'docstring' => JText::_('Returns information about a specific post.'),
				'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.getRecentPosts' => array(
				'function' => 'plgXMLRPCBloggerServices::getRecentPosts',
				'docstring' => JText::_('Returns a list of the most recent posts in the system.'),
				'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcInt))
			),
				'blogger.getTemplate' => array(
				'function' => 'plgXMLRPCBloggerServices::getTemplate',
				'docstring' => '',
				'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.setTemplate' => array(
				'function' => 'plgXMLRPCBloggerServices::setTemplate',
				'docstring' => '',
				'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.newPost' => array(
				'function' => 'plgXMLRPCBloggerServices::newPost',
				'docstring' => JText::_('Creates a new post, and optionally publishes it.'),
				'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
			),
				'blogger.deletePost' => array(
				'function' => 'plgXMLRPCBloggerServices::deletePost',
				'docstring' => JText::_('Deletes a post.'),
				'signature' => array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
			),
				'blogger.editPost' => array(
				'function' => 'plgXMLRPCBloggerServices::editPost',
				'docstring' => JText::_('Updates the information about an existing post.'),
				'signature' => array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
			)
		);
	}
}

class plgXMLRPCBloggerServices
{
	/*
	 * Note : blogger.getUsersBlogs will make more sense once we support multiple blogs
	 */
	function getUserBlogs($appkey, $username, $password)
	{
		global $mainframe, $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );
		
		// Handle the access permissions part of the main database query
		if ($user->authorize('com_content', 'edit', 'content', 'all')) {
			$xwhere = '';
		} else {
			$xwhere = ' AND a.published = 1 AND b.published = 1';
		}
		$gid		= $user->get('aid', 0);
		$access_check = ' AND a.access <= '.(int) $gid .
						' AND b.access <= '.(int) $gid;
		// Query of categories within section
		$query = 'SELECT a.id, a.title, a.section, ' .
				' CONCAT_WS(\'/\', a.title, b.title) AS catName' .
				' FROM #__categories AS a' .
				' LEFT JOIN #__sections AS b ON a.section = b.id' .
				$xwhere.
				$access_check;
		$db = &JFactory::getDBO();
		$db->setQuery( $query );
		$categories = $db->loadObjectList();
		$structarray = array();

		foreach( $categories AS $category ) {
			if (intval($category->section) > 0) {
				$blog = new xmlrpcval(array(
					'url'		=> new xmlrpcval(JURI::base(), $xmlrpcString),
					'blogid'	=> new xmlrpcval($category->id, $xmlrpcString),
					'blogName'	=> new xmlrpcval($category->catName, $xmlrpcString)
					), 'struct');
				array_push($structarray, $blog);
			}
		}
		return new xmlrpcresp(new xmlrpcval( $structarray , $xmlrpcArray));
	}

	function getUserInfo($appkey, $username, $password)
	{
		global $xmlrpcerruser, $xmlrpcStruct;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );

		$struct = new xmlrpcval(
		array(
			'nickname'	=> new xmlrpcval($user->get('username')),
			'userid'	=> new xmlrpcval($user->get('id')),
			'url'		=> new xmlrpcval(''),
			'email'		=> new xmlrpcval($user->get('email')),
			'lastname'	=> new xmlrpcval($user->get('name')),
			'firstname'	=> new xmlrpcval($user->get('name'))
		), $xmlrpcStruct);

		return new xmlrpcresp($struct);
	}

	function getPost($appkey, $postid, $username, $password)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );

		$db = &JFactory::getDBO();

		$where = 'a.id = ' . (int) $postid;

		$canReadUnpublished = $user->authorize('com_content', 'edit', 'content', 'all');
		if ($canReadUnpublished) {
			$publishedWhere = '';
		} else {
			$publishedWhere = ' AND u.published = 1 AND b.published = 1';
		}
		
		$nullDate 		= $db->getNullDate();
		$date =& JFactory::getDate();
		$now = $date->toMySQL();

		$query = 'SELECT a.title AS title,'
		. ' a.created AS created,'
		. ' a.introtext AS introtext,'
		. ' a.fulltext AS ftext,'
		. ' a.id AS id,'
		. ' a.created_by AS created_by'
		. ' FROM #__content AS a'
		. ' INNER JOIN #__categories AS b ON b.id=a.catid'
		. ' INNER JOIN #__sections AS u ON u.id = a.sectionid'
		. ' WHERE '.$where
		. $publishedWhere
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' AND b.access <= '.(int) $user->get( 'aid' )
		. ' AND u.access <= '.(int) $user->get( 'aid' )
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		;

		$db->setQuery( $query );
		$item = $db->loadObject();

		if ($item === null) {
			return new xmlrpcresp(0, $xmlrpcerruser+2, JText::_("Access Denied"));
		}
		
		$content	= '<title>'.$item->title.'</title>';
		$content	.= $item->introtext.'<more_text>'.$item->ftext.'</more_text>';

		$struct = new xmlrpcval(
		array(
			'userid'			=> new xmlrpcval($item->created_by),
			'dateCreated'	=> new xmlrpcval($item->created),
			'content'		=> new xmlrpcval($content),
			'postid'			=> new xmlrpcval($item->id)
		), $xmlrpcStruct);

		return new xmlrpcresp($struct);
	}

	function newPost($appkey, $blogid, $username, $password, $content, $publish)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );

		if ($user->get('gid') < 19) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('ALERTNOTAUTH'));
		}

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('com_content', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('com_content', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('com_content', 'publish', 'content', 'all');

		if (!($access->canEdit || $access->canEditOwn)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('ALERTNOTAUTH'));
		}

		$db =& JFactory::getDBO();

		// load plugin params info
	 	$plugin =& JPluginHelper::getPlugin('xmlrpc','blogger');
	 	$params = new JParameter( $plugin->params );

		$blogid = (int) $blogid;

		// load the category
		$cat =& JTable::getInstance('category');
		$cat->load($blogid);

		// create a new content item
		$item =& JTable::getInstance('content');

		$item->title	 	= plgXMLRPCBloggerHelper::getPostTitle($content);
		$item->introtext	= plgXMLRPCBloggerHelper::getPostIntroText($content);
		$item->fulltext		= plgXMLRPCBloggerHelper::getPostFullText($content);

		$item->catid	 	= $blogid;
		$item->sectionid 	= $cat->section;

		$date =& JFactory::getDate();

		$item->created		= $date->toMySQL();
		$item->created_by	= $user->get('id');

		$item->publish_up	= $date->toMySQL();
		$item->publish_down	= $db->getNullDate();

		$item->state		= ($publish && $access->canPublish) ? 1 : 0;

		if (!$item->check()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Post check failed') );
		}

		$item->version++;

		if (!$item->store()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Post store failed') );
		}

		return new xmlrpcresp(new xmlrpcval($item->id, $xmlrpcString));
	}

	function editPost($appkey, $postid, $username, $password, $content, $publish)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );
		
		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('com_content', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('com_content', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('com_content', 'publish', 'content', 'all');

		if (!($access->canEdit || $access->canEditOwn)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('ALERTNOTAUTH'));
		}

		// load the row from the db table
		$item =& JTable::getInstance('content');
		if(!$item->load( $postid )) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Sorry, no such post') );
		}

		if($item->isCheckedOut($user->get('id'))) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Sorry, post is already being edited') );
		}

		//lock the item
		$item->checkout($user->id);

		$item->title	 = plgXMLRPCBloggerHelper::getPostTitle($content);
		$item->introtext = plgXMLRPCBloggerHelper::getPostIntroText($content);
		$item->fulltext  = plgXMLRPCBloggerHelper::getPostFullText($content);

		if (!$item->check()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Post check failed') );
		}

		$item->version++;

		if (!$item->store()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Post store failed') );
		}

		$item->state	= ($publish && $access->canPublish) ? 1 : 0;

		//lock the item
		$item->checkout();

		return new xmlrpcresp(new xmlrpcval('true', $xmlrpcBoolean));
	}

	function deletePost($appkey, $postid, $username, $password, $publish)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );
		
		if ($user->get('gid') < 23) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('ALERTNOTAUTH'));
		}

		// load the row from the db table
		$item =& JTable::getInstance('content');
		if(!$item->load( $postid )) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Sorry, no such post') );
		}

		if($item->isCheckedOut($user->get('id'))) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Sorry, post is already being edited') );
		}

		//lock the item
		$item->checkout();

		$item->state = -2;
		$item->ordering = 0;

		if (!$item->store()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Post delete failed') );
		}

		return new xmlrpcresp(new xmlrpcval('true', $xmlrpcBoolean));
	}


	/**
	 * Blogger API - blogger.getRecentPosts
	 *
	 * @param xmlrpcmessage XML-RPC message passed to the method
	 * @return xmlrpcresp XML-RPC response
	 */
	function getRecentPosts($appkey, $blogid, $username, $password, $numposts)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JFactory::getUser($username);
		plgXMLRPCBloggerHelper::getUserAid( $user );
		
		// load plugin params info
	 	$plugin =& JPluginHelper::getPlugin('xmlrpc','blogger');
	 	$params = new JParameter( $plugin->params );

		$db =& JFactory::getDBO();

		$nullDate 		= $db->getNullDate();
		$date =& JFactory::getDate();
		$now = $date->toMySQL();

		$blogid = (int) $blogid;

		$canReadUnpublished = $user->authorize('com_content', 'edit', 'content', 'all');
		if ($canReadUnpublished) {
			$publishedWhere = '';
			$publishTimeWhere = '';
		} else {
			$publishedWhere = ' AND u.published = 1 AND b.published = 1';
			$publishTimeWhere = ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
			. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';
		}
		
		$query = 'SELECT a.title AS title,'
		. ' a.created AS created,'
		. ' a.introtext AS introtext,'
		. ' a.fulltext AS ftext,'
		. ' a.id AS id,'
		. ' a.created_by AS created_by'
		. ' FROM #__content AS a'
		. ' INNER JOIN #__categories AS b ON b.id=a.catid'
		. ' INNER JOIN #__sections AS u ON u.id = a.sectionid'
		. ' WHERE a.catid = '. $blogid
		. $publishedWhere
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' AND b.access <= '.(int) $user->get( 'aid' )
		. ' AND u.access <= '.(int) $user->get( 'aid' )
		. $publishTimeWhere
		;
			
		$db->setQuery($query, 0, $numposts);
		$items = $db->loadObjectList();

		if ($items === null) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('No posts available, or an error has occured.') );
		}

		$structArray = array();
		foreach ($items as $item)
		{
			$content	= '<title>'.$item->title.'</title>';
			$content	.= $item->introtext.'<more_text>'.$item->ftext.'</more_text>';

			$structArray[] = new xmlrpcval(array(
				'userid'		=> new xmlrpcval($item->created_by),
				'dateCreated'	=> new xmlrpcval($item->created),
				'content'		=> new xmlrpcval($content),
				'postid'		=> new xmlrpcval($item->id)
			), 'struct');
		}

		return new xmlrpcresp(new xmlrpcval( $structArray , $xmlrpcArray));
	}

	function getTemplate($appkey, $blogid, $username, $password, $templateType)
	{
		global $xmlrpcerruser;
		return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Method not implemented') );
	}

	function setTemplate($appkey, $blogid, $username, $password, $template, $templateType)
	{
		global $xmlrpcerruser;		
		return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_('Method not implemented') );
	}
}

class plgXMLRPCBloggerHelper
{
	function getUserAid( &$user ) {

		$acl = &JFactory::getACL();

		//Get the user group from the ACL
		$grp = $acl->getAroGroup($user->get('id'));

		// Mark the user as logged in
		$user->set('guest', 0);
		$user->set('aid', 1);

		// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
		if ($acl->is_group_child_of($grp->name, 'Registered')      ||
			$acl->is_group_child_of($grp->name, 'Public Backend')) {
 			$user->set('aid', 2);
 		}
	}
	
	function authenticateUser($username, $password)
	{
		// Get the global JAuthentication object
		jimport( 'joomla.user.authentication');
		$auth = & JAuthentication::getInstance();
		$credentials = array( 'username' => $username, 'password' => $password );
		$options = array();
		$response = $auth->authenticate($credentials, $options);
		return $response->status === JAUTHENTICATE_STATUS_SUCCESS;
	}

	function getPostTitle($content)
	{
		$title = '';
		if ( preg_match('/<title>(.+?)<\/title>/is', $content, $matchtitle) )
		{
			$title = $matchtitle[0];
			$title = preg_replace('/<title>/si', '', $title);
			$title = preg_replace('/<\/title>/si', '', $title);
		}
		if (empty( $title )) {
			$title = substr( $content, 0, 20 );
		}
		return $title;
	}

	function getPostCategory($content)
	{
		$category = 0;

		$match = array();
		if ( preg_match('/<category>(.+?)<\/category>/is', $content, $match) )
		{
			$category = trim($match[1], ',');
			$category = explode(',', $category);
		}

		return $category;
	}

	function getPostIntroText($content)
	{
		return plgXMLRPCBloggerHelper::removePostData($content); //substr($string, 0, strpos($string, '<more_text>'));
	}

	function getPostFullText($content)
	{
		$match = array();
		if ( preg_match('/<more_text>(.+?)<\/more_text>/is', $content, $match) )
		{
			$fulltext = $match[0];
			$fulltext = preg_replace('/<more_text>/si', '', $fulltext);
			$fulltext = preg_replace('/<\/more_text>/si', '', $fulltext);
		}

		return $fulltext;
	}

	function removePostData($content)
	{
		$content = preg_replace('/<title>(.+?)<\/title>/si', '', $content);
		$content = preg_replace('/<category>(.+?)<\/category>/si', '', $content);
		$content = preg_replace('/<more_text>(.+?)<\/more_text>/si', '', $content);
		$content = trim($content);
		return $content;
	}
}
