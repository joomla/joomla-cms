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

/**
 * Joomla! Debug plugin
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	System
 */
class  plgLegacy extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object		$subject The object to observe
	 * @since	1.0
	 */
	function plgLegacy(& $subject)
	{
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('system', 'legacy');
		$this->_params = new JParameter($this->_plugin->params);
	}
	
	function onAfterRoute()
	{
		global $mainframe;
		if ($mainframe->isAdmin())
		{
			return;
		}

		switch(JRequest::getVar('option'))
		{
			case 'com_content'   :
				$this->routeContent();
				break;
			case 'com_newsfeeds' :
				$this->routeNewsfeeds();
				break;
			case 'com_weblinks' :
				$this->routeWeblinks();
				break;
		}
	}
	
	function routeContent()
	{	
		$viewName	= JRequest::getVar( 'view', 'article' );
		$layout		= JRequest::getVar( 'layout', 'default' );
		
		// interceptors to support legacy urls
		switch( JRequest::getVar('task'))
		{
			//index.php?option=com_content&task=x&id=x&Itemid=x
			case 'blogsection':
				$viewName	= 'section';
				$layout = 'blog';
				break;
			case 'section':
				$viewName	= 'section';
				break;
			case 'category':
				$viewName	= 'category';
				break;
			case 'blogcategory':
				$viewName	= 'category';
				$layout = 'blog';
				break;
			case 'archivesection':
			case 'archivecategory':
				$viewName	= 'archive';
				break;
			case 'frontpage' :
				$viewName = 'frontpage';
				break;
			case 'view':
				$viewName	= 'article';
				break;
		}
		
		JRequest::setVar('layout', $layout);
		JRequest::setVar('view', $viewName);
	}
	
	function routeNewsfeeds()
	{
		$viewName = JRequest::getVar( 'view', 'categories' );

		// interceptors to support legacy urls
		switch( JRequest::getVar('task'))
		{
			//index.php?option=com_newsfeeds&task=x&catid=xid=x&Itemid=x
			case 'view':
				$viewName	= 'newsfeed';
				break;

			default:
			{
				if(JRequest::getVar( 'catid', 0)) {
					$viewName = 'category';
				}
			}
		}

		JRequest::setVar('view', $viewName);
	}
	
	function routeWeblinks()
	{
		$viewName = JRequest::getVar( 'view', 'categories' );

		// interceptors to support legacy urls
		switch( JRequest::getVar('task'))
		{
			//index.php?option=com_weblinks&task=x&catid=xid=x
			case 'view':
				$viewName	= 'weblink';
				break;

			default:
			{
				if($catid = JRequest::getVar( 'catid', 0)) {
					$viewName = 'category';
					JRequest::setVar('id', $catid);
				}
			}
		}

		JRequest::setVar('view', $viewName);
	}
}

// Attach sef handler to event dispatcher
$dispatcher = & JEventDispatcher::getInstance();
$dispatcher->attach(new plgLegacy($dispatcher));

?>