<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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
				'docstring' => 'Returns a list of weblogs to which an author has posting privileges.',
				'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString ))
			),
				'blogger.getUserInfo' => array(
				'function' => 'plgXMLRPCBloggerServices::getUserInfo',
				'docstring' => 'Returns information about an author in the system.',
				'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.getPost' => array(
				'function' => 'plgXMLRPCBloggerServices::getPost',
				'docstring' => 'Returns information about a specific post.',
				'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.getRecentPosts' => array(
				'function' => 'plgXMLRPCBloggerServices::getRecentPosts',
				'docstring' => 'Returns a list of the most recent posts in the system.',
				'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcInt))
			),
				'blogger.getTemplate' => array(
				'function' => 'plgXMLRPCBloggerServices::getTemplate',
				'docstring' => '',
				'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.setTemplate' => array(
				'function' => 'plgXMLRPCBloggerServices::setTemplate',
				'docstring' => '',
				'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
			),
				'blogger.newPost' => array(
				'function' => 'plgXMLRPCBloggerServices::newPost',
				'docstring' => 'Creates a new post, and optionally publishes it.',
				'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
			),
				'blogger.deletePost' => array(
				'function' => 'plgXMLRPCBloggerServices::deletePost',
				'docstring' => 'Deletes a post.',
				'signature' => array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
			),
				'blogger.editPost' => array(
				'function' => 'plgXMLRPCBloggerServices::editPost',
				'docstring' => 'Updates the information about an existing post.',
				'signature' => array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
			),
			'blogger.helloworld' => array(
				'function' => 'plgXMLRPCBloggerServices::helloworld',
				'docstring' => 'Updates the information about an existing post.',
				'signature' => array(array($xmlrpcBoolean))
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
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		$structarray = array();

		$blog = new xmlrpcval(array(
			'url'		=> new xmlrpcval(JURI::base(), $xmlrpcString),
			'blogid'	=> new xmlrpcval('1', $xmlrpcString),
			'blogName'	=> new xmlrpcval('Joomla Articles', $xmlrpcString)
			), 'struct');

		array_push($structarray, $blog);
		return new xmlrpcresp(new xmlrpcval( $structarray , $xmlrpcArray));
	}

	function getUserInfo($appkey, $username, $password)
	{
		global $xmlrpcerruser, $xmlrpcStruct;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		$user =& JUser::getInstance($username);

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
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		// load the row from the db table
		$item =& JTable::getInstance('content' );
		$item->load( $postid );

		$content	= '<title>'.$item->title.'</title>';
		//$content	.= '<category>'.$item->catid.'</category>';
		$content	.= $item->introtext.'<more_text>'.$item->fulltext.'</more_text>';

		$struct = new xmlrpcval(
		array(
			'userid'			=> new xmlrpcval($item->created_by),
			'dateCreated'	=> new xmlrpcval('0'), //TODO
			'content'		=> new xmlrpcval($content),
			'postid'			=> new xmlrpcval($item->id)
		), $xmlrpcStruct);

		return new xmlrpcresp($struct);
	}

	function newPost($appkey, $blogid, $username, $password, $content, $publish)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		$db =& JFactory::getDBO();

		// load plugin params info
	 	$plugin =& JPluginHelper::getPlugin('xmlrpc','blogger');
	 	$params = new JParameter( $plugin->params );

		// load the category
		$cat =& JTable::getInstance('category');
		$cat->load($params->get( 'catid', 1 ));

		// create a new content item
		$item =& JTable::getInstance('content');

		$item->title	 	= plgXMLRPCBloggerHelper::getPostTitle($content);
		$item->introtext	= plgXMLRPCBloggerHelper::getPostIntroText($content);
		$item->fulltext		= plgXMLRPCBloggerHelper::getPostFullText($content);

		$item->catid	 	= $cat->id;
		$item->sectionid 	= $cat->section;
		// TODO: Should this be JDate?
		$item->created		= date('Y-m-d H:i:s');
		$item->created_by	= $user->get('id');

		$item->publish_up	= $publish ? date('Y-m-d H:i:s') : $db->getNullDate();
		$item->publish_down	= $db->getNullDate();

		$item->state		= $publish;

		if (!$item->check()) {
			return new dom_xmlrpc_fault( '500', 'Post check failed' );
		}

		$item->version++;

		if (!$item->store()) {
			return new dom_xmlrpc_fault( '500', 'Post store failed' );
		}

		return new xmlrpcresp(new xmlrpcval($item->id, $xmlrpcString));
	}

	function editPost($appkey, $postid, $username, $password, $content, $publish)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		// load the row from the db table
		$item =& JTable::getInstance('content');
		if(!$item->load( $postid )) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Sorry, no such post' );
		}

		if($item->isCheckedOut($user->get('id'))) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Sorry, post is already being edited' );
		}

		//TODO::implement content access check

		//lock the item
		$item->checkout($user->id);

		$item->title	 = plgXMLRPCBloggerHelper::getPostTitle($content);
		$item->introtext = plgXMLRPCBloggerHelper::getPostIntroText($content);
		$item->fulltext  = plgXMLRPCBloggerHelper::getPostFullText($content);

		if (!$item->check()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Post check failed' );
		}

		$item->version++;

		if (!$item->store()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Post store failed' );
		}

		//lock the item
		$item->checkout();

		return new xmlrpcresp(new xmlrpcval('true', $xmlrpcBoolean));
	}

	function deletePost($appkey, $postid, $username, $password, $publish)
	{
		global $xmlrpcerruser, $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		if(!plgXMLRPCBloggerHelper::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		// load the row from the db table
		$item =& JTable::getInstance('content');
		if(!$item->load( $postid )) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Sorry, no such post' );
		}

		if($item->isCheckedOut($user->get('id'))) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Sorry, post is already being edited' );
		}

		//TODO::implement content access check

		//lock the item
		$item->checkout();

		if (!$item->delete()) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'Post delete failed' );
		}

		//lock the item
		$item->checkout();

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
			return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");
		}

		$user =& JUser::getInstance($username);
		//TODO::implement generic access check

		// load plugin params info
	 	$plugin =& JPluginHelper::getPlugin('xmlrpc','blogger');
	 	$params = new JParameter( $plugin->params );

		$db =& JFactory::getDBO();

		// Lets get a list of the recents articles
		$where = '';
		//echo $params->get('sectionid', 0);
		if($params->get('sectionid', 0)) {
			$where = ' WHERE sectionid = '.$params->get('sectionid');
		}

		$query = 'SELECT *'
			. ' FROM #__content'
			. ' WHERE state =1'
			. ' ORDER BY created'
			;
		$db->setQuery($query, 0, $numposts);
		$items = $db->loadObjectList();

		if (!$items) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, 'No posts available, or an error has occured.' );
		}


		$structArray = array();
		foreach ($items as $item)
		{
			$content	= '<title>'.$item->title.'</title>';
			//$content	.= '<category>'.$item->catid.'</category>'; //doesn't seem to work
			$content	.= $item->introtext.'<more_text>'.$item->fulltext.'</more_text>';

			$structArray[] = new xmlrpcval(array(
				'userid'		=> new xmlrpcval($item->created_by),
				'dateCreated'	=> new xmlrpcval('0'),
				'content'		=> new xmlrpcval($content),
				'postid'		=> new xmlrpcval($item->id)
			), 'struct');
		}

		return new xmlrpcresp(new xmlrpcval( $structArray , $xmlrpcArray));
	}

	function getTemplate($appkey, $blogid, $username, $password, $templateType)
	{
		global $xmlrpcerruser;
		return new xmlrpcresp(0, $xmlrpcerruser+1, 'Method not implemented' );
	}

	function setTemplate($appkey, $blogid, $username, $password, $template, $templateType)
	{
		global $xmlrpcerruser;
		return new xmlrpcresp(0, $xmlrpcerruser+1, 'Method not implemented' );
	}
}

class plgXMLRPCBloggerHelper
{
	function authenticateUser($username, $password)
	{
		// Get the global JAuthentication object
		jimport( 'joomla.user.authentication');
		$auth = & JAuthentication::getInstance();
		return $auth->authenticate($username, $password);
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
			$fulltext = trim($match[1], ',');
			$fulltext = explode(',', $fulltext);
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