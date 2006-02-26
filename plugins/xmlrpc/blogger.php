<?php
/**
* @version $Id: joomla.php 2418 2006-02-16 19:31:39Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onGetWebServices', 'wsGetBloggerWebServices' );

/**
* @return array An array of associative arrays defining the available methods
*/
function wsGetBloggerWebServices() 
{
	return array(
		array(
			'name' => 'blogger.getUsersBlogs',
			'method' => 'getUserBlogs',
			'help' => 'Returns a list of weblogs to which an author has posting privileges.',
			'signature' => array('string', 'string', 'string')
		),
		array(
			'name' => 'blogger.getUserInfo',
			'method' => 'getUserInfo',
			'help' => 'Returns information about an author in the system.',
			'signature' => array('string', 'string', 'string')
		),
		array(
			'name' => 'blogger.getPost',
			'method' => 'getPost',
			'help' => 'Returns information about a specific post.',
			'signature' => array() 
		),
		array(
			'name' => 'blogger.getRecentPosts',
			'method' => 'getRecentPosts',
			'help' => 'Returns a list of the most recent posts in the system.',
			'signature' => array() 
		),
		array(
			'name' => 'blogger.getTemplate',
			'method' => 'getTemplate',
			'help' => '',
			'signature' => array('string', 'string', 'string', 'string', 'string') 
		),
		array(
			'name' => 'blogger.setTemplate',
			'method' => 'setTemplate',
			'help' => '',
			'signature' => array('string', 'string', 'string', 'string', 'string', 'string') 
		),
		array(
			'name' => 'blogger.newPost',
			'method' => 'newPost',
			'help' => 'Creates a new post, and optionally publishes it.',
			'signature' => array('string', 'string', 'string', 'string', 'string', 'boolean') 
		),
		array(
			'name' => 'blogger.deletePost',
			'method' => 'deletePost',
			'help' => 'Deletes a post.',
			'signature' => array() 
		),
		array(
			'name' => 'blogger.editPost',
			'method' => 'editPost',
			'help' => 'Updates the information about an existing post.',
			'signature' => array('string', 'string', 'string', 'string', 'string', 'boolean') 
		)
	);
}

/* 
 * blogger.getUsersBlogs will make more sense once we support multiple blogs 
 */
function getUserBlogs($appkey, $username, $password)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$struct = array(
	    'url'      => $mainframe->getBaseURL(),
	    'blogid'   => '1',
	    'blogName' => 'Joomla Content Items'
	  );

	  return array($struct);
}

function getUserInfo($appkey, $username, $password)
{
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$struct = array(
	    'nickname'  => 'test',
	    'userid'    => '1',
	    'url'       => 'url',
	    'email'     => 'email',
	    'lastname'  => 'test',
	    'firstname' => 'test'
	  );
	  
	 return $struct;
}

function getPost($appkey, $postid, $username, $password)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
		
	// load the row from the db table
	$item =& JModel::getInstance('content', $mainframe->getDBO() );
	$item->load( $postid );
	
	$content  = '<title>'.$item->title.'</title>';
	//$content .= '<category>'.$item->catid.'</category>';
	$content .= $item->introtext.'<!--more-->'.$item->fulltext;
	
	$struct = array(
	   'userid'    => $item->created_by,
	   'dateCreated' => '0', //TODO
	   'content'     => $content,
	   'postid'  => $item->id
	);

	return $struct;
}

function newPost($appkey, $blogid, $username, $password, $content, $publish)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	// load the row from the db table
	$item =& JModel::getInstance('content', $mainframe->getDBO() );
	
	$item->title     = JBloggerHelper::getPostTitle($content);
	//$item->catid     = JBloggerHelper::getPostCategory($content); 
	$item->introtext = JBloggerHelper::getPostIntroText($content);
	$item->fulltext  = JBloggerHelper::getPostFullText($content);
	
	$item->created = date('Y-m-d H:i:s');
	$item->created_by = $user->get('id');
	
	//if (!$item->check()) {
		
	//}
	//$content->version++;
	if (!$item->store()) {
		return new dom_xmlrpc_fault( '500', 'Post store failed' );
	}
	
	return true;
}

function editPost($appkey, $postid, $username, $password, $content, $publish)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	// load the row from the db table
	$item =& JModel::getInstance('content', $mainframe->getDBO() );
	if(!$item->load( $postid )) {
		return new dom_xmlrpc_fault( '404', 'Sorry, no such post' );
	}
 	
	//TODO::implement access and checkout check
	
	$item->title     = JBloggerHelper::getPostTitle($content);
	//$item->catid     = JBloggerHelper::getPostCategory($content); 
	$item->introtext = JBloggerHelper::getPostIntroText($content);
	$item->fulltext  = JBloggerHelper::getPostFullText($content);
	
	if (!$item->check()) {
		return new dom_xmlrpc_fault( '500', 'Post check failed' );
	}
	
	$item->version++;
	
	if (!$item->store()) {
		return new dom_xmlrpc_fault( '500', 'Post store failed' );
	}
	
	return true;
}

function deletePost($appkey, $postid, $username, $password, $publish)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	// load the row from the db table
	$item =& JModel::getInstance('content', $mainframe->getDBO() );
	if(!$item->load( $postid )) {
		return new dom_xmlrpc_fault( '404', 'Sorry, no such post' );
	}
	
	//TODO::implement access and checkout check
	
	if (!$item->delete()) {
		return new dom_xmlrpc_fault( '500', 'Post delete failed' );
	}
	
	return true;
}


function getRecentPosts($appkey, $blogid, $username, $password, $numposts)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$db =& $mainframe->getDBO();
	
	/*
	 * Lets get a list of the recents content items
	 */
	$query = "SELECT *" 
		. "\n FROM #__content" 
		. "\n ORDER BY created"
		. "\n LIMIT ".$numposts
		;
	$db->setQuery($query);
	$items = $db->loadObjectList();
	
	if (!$items) {
		return new dom_xmlrpc_fault( 500, 'No posts available, or an error has occured.' );
	 }
	
	 foreach ($items as $item) 
	 { 
	    $content  = '<title>'.$item->title.'</title>';
		//$content .= '<category>'.$item->catid.'</category>';
		$content .= $item->introtext;
	
		$struct[] = array(
	    	'userid'    => $item->created_by,
	    	'dateCreated' => '0', //TODO
	    	'content'     => $content,
	    	'postid'  => $item->id
		);
	}
	
	$recent_posts = array();
	for ($j=0; $j < count($struct); $j++) {
	    array_push($recent_posts, $struct[$j]);
	}
	
	 return $recent_posts;
}


function getTemplate($appkey, $blogid, $username, $password, $templateType)
{
	return new dom_xmlrpc_fault( '500', 'Method not implemented' );
}

function setTemplate($appkey, $blogid, $username, $password, $template, $templateType)
{
	return new dom_xmlrpc_fault( '500', 'Method not implemented' );
}



class JBloggerHelper 
{
	function authenticateUser($username, $password)
	{
		// Build the credentials array
		$credentials['username'] = $username;
		$credentials['password'] = $password;
	
		// Get the global JAuthenticate object
		$auth = & JAuthenticate::getInstance();
		$result = $auth->authenticate($credentials);
		
		return $result;
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
	
	function getPostIntroText($content) {
		$string = JBloggerHelper::removePostData($content); 
		return substr($string, 0, strpos($string, '<!--more-->'));
	}
	
	function getPostFullText($content) {
		$string = JBloggerHelper::removePostData($content); 
		return substr($string, strpos($string, '<!--more-->') + 11);
	}

	function removePostData($content) 
	{
		$content = preg_replace('/<title>(.+?)<\/title>/si', '', $content);
		$content = preg_replace('/<category>(.+?)<\/category>/si', '', $content);
		$content = trim($content);
		return $content;
	}
}
?>