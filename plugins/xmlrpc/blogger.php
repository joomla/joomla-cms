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
	global $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;
	return array(
			'blogger.getUsersBlogs' => array(
			'function' => 'getUserBlogs',
			'docstring' => 'Returns a list of weblogs to which an author has posting privileges.',
			'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString ))
		),
			'blogger.getUserInfo' => array(
			'function' => 'getUserInfo',
			'docstring' => 'Returns information about an author in the system.',
			'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString))
		),
			'blogger.getPost' => array(
			'function' => 'getPost',
			'docstring' => 'Returns information about a specific post.',
			'signature' => array(array())
		),
			'blogger.getRecentPosts' => array(
			'function' => 'getRecentPosts',
			'docstring' => 'Returns a list of the most recent posts in the system.',
			'signature' => array(array())
		),
			'blogger.getTemplate' => array(
			'function' => 'getTemplate',
			'docstring' => '',
			'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString)) 
		),
			'blogger.setTemplate' => array(
			'function' => 'setTemplate',
			'docstring' => '',
			'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString))
		),
			'blogger.newPost' => array(
			'function' => 'newPost',
			'docstring' => 'Creates a new post, and optionally publishes it.',
			'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
		),
			'blogger.deletePost' => array(
			'function' => 'deletePost',
			'docstring' => 'Deletes a post.',
			'signature' => array(array())
		),
			'blogger.editPost' => array(
			'function' => 'editPost',
			'docstring' => 'Updates the information about an existing post.',
			'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))
		)
	);
}

/* 
 * Note : blogger.getUsersBlogs will make more sense once we support multiple blogs 
 */
function getUserBlogs($msg)
{
	global $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;
	$appkey = php_xmlrpc_decode($msg->getParam(0));
	$username = php_xmlrpc_decode($msg->getParam(1));
	$password = php_xmlrpc_decode($msg->getParam(2));
	global $mainframe;

	if(!JBloggerHelper::authenticateUser($username, $password)) {	
		return new xmlrpcresp(0, $xmlrpcerruser+1, "Login Failed");		
	}
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
	
	$structarray = array();
	
	$blog = new xmlrpcval(array(
	    'url'      => new xmlrpcval($mainframe->getBaseURL(), 'string'),
	    'blogid'   => new xmlrpcval('1', 'string'),
	    'blogName' => new xmlrpcval('Joomla Content Items', 'string')
	  ), 'array');
	  
	array_push($structarray, $blog);
	return new xmlrpcresp(new xmlrpcval( $structarray , "array"));		
}

function getUserInfo($appkey, $username, $password)
{
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
	
	$user =& JUser::getInstance($username);
	
	$struct = array(
	    'nickname'  => $user->get('username'),
	    'userid'    => $user->get('id'),
	    'url'       => '',
	    'email'     => $user->get('email'),
	    'lastname'  => $user->get('name'),
	    'firstname' => $user->get('name')
	  );
	  
	 return $struct;
}

function getPost($appkey, $postid, $username, $password)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
		
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
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
	
	$db   =& $mainframe->getDBO();
	
	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('xmlrpc','blogger'); 
 	$params = new JParameter( $plugin->params );
	
	// load the category
	$cat =& JModel::getInstance('category', $db);
	$cat->load($params->get( 'catid', 1 ));
	
	// create a new content item
	$item =& JModel::getInstance('content', $db );
	
	$item->title     = JBloggerHelper::getPostTitle($content);
	$item->introtext = JBloggerHelper::getPostIntroText($content);
	$item->fulltext  = JBloggerHelper::getPostFullText($content);

	$item->catid     = $cat->id;
	$item->sectionid = $cat->section;
	
	$item->created = date('Y-m-d H:i:s');
	$item->created_by = $user->get('id');
	
	$item->publish_up   = $publish ? date('Y-m-d H:i:s') : $db->getNullDate();
	$item->publish_down = $db->getNullDate();
	
	$item->state = $publish;
	
	if (!$item->check()) {
		return new dom_xmlrpc_fault( '500', 'Post check failed' );
	}
	
	$item->version++;
	
	if (!$item->store()) {
		return new dom_xmlrpc_fault( '500', 'Post store failed' );
	}
	
	return $item->id;
}

function editPost($appkey, $postid, $username, $password, $content, $publish)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
	
	// load the row from the db table
	$item =& JModel::getInstance('content', $mainframe->getDBO() );
	if(!$item->load( $postid )) {
		return new dom_xmlrpc_fault( '404', 'Sorry, no such post' );
	}
	
	if($item->isCheckedOut($user->get('id'))) {
		return new dom_xmlrpc_fault( '500', 'Sorry, post is already being edited' );
	}
	
	//TODO::implement content access check
	
	//lock the item
	$item->checkout();
 		
	$item->title     = JBloggerHelper::getPostTitle($content);
	$item->introtext = JBloggerHelper::getPostIntroText($content);
	$item->fulltext  = JBloggerHelper::getPostFullText($content);
	
	if (!$item->check()) {
		return new dom_xmlrpc_fault( '500', 'Post check failed' );
	}
	
	$item->version++;
	
	if (!$item->store()) {
		return new dom_xmlrpc_fault( '500', 'Post store failed' );
	}
	
	//lock the item
	$item->checkout();
	
	return true;
}

function deletePost($appkey, $postid, $username, $password, $publish)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
	
	// load the row from the db table
	$item =& JModel::getInstance('content', $mainframe->getDBO() );
	if(!$item->load( $postid )) {
		return new dom_xmlrpc_fault( '404', 'Sorry, no such post' );
	}
	
	if($item->isCheckedOut($user->get('id'))) {
		return new dom_xmlrpc_fault( '500', 'Sorry, post is already being edited' );
	}
	
	//TODO::implement content access check
	
	//lock the item
	$item->checkout();
	
	if (!$item->delete()) {
		return new dom_xmlrpc_fault( '500', 'Post delete failed' );
	}
	
	//lock the item
	$item->checkout();
	
	return true;
}


function getRecentPosts($appkey, $blogid, $username, $password, $numposts)
{
	global $mainframe;
	
	if(!JBloggerHelper::authenticateUser($username, $password)) {
		return new dom_xmlrpc_fault( '-1', 'Login Failed' );
	}
	
	$user =& JUser::getInstance($username);
	//TODO::implement generic access check
	
	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('xmlrpc','blogger'); 
 	$params = new JParameter( $plugin->params );
	
	$db =& $mainframe->getDBO();
	
	// Lets get a list of the recents content items
	$where = '';
	echo $params->get('sectionid', 0);
	if($params->get('sectionid', 0)) {
		$where = "\n WHERE sectionid = ".$params->get('sectionid');
	}
	
	echo $where;
	
	$query = "SELECT *" 
		. "\n FROM #__content" 
		. $where
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
		//$content .= '<category>'.$item->catid.'</category>'; //doesn't seem to work
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
		
		return $auth->authenticate($credentials);
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
		$string = JBloggerHelper::removePostData($content); 
		return substr($string, 0, strpos($string, '<!--more-->'));
	}
	
	function getPostFullText($content) 
	{
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