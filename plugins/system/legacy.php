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
class  plgSystemLegacy extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	 * @since	1.0
	 */
	function plgSystemLegacy(& $subject)
	{
		parent::__construct($subject);
		
		// Define the 1.0 legacy mode constant
		define('_J_LEGACY', '1.0');

		// Set global configuration var for legacy mode
		$config = &JFactory::getConfig();
		$config->setValue('config.legacy', 1);

		// Import library dependencies
		require_once(dirname(__FILE__).DS.'legacy'.DS.'classes.php');
		require_once(dirname(__FILE__).DS.'legacy'.DS.'functions.php');
		require_once(dirname(__FILE__).DS.'legacy'.DS.'toolbar.php');

		/**
		 * Legacy define, _ISO define not used anymore. All output is forced as utf-8.
		 * @deprecated	As of version 1.5
		 */
		define('_ISO','charset=utf-8');

		/**
		 * Legacy constant, use _JEXEC instead
		 * @deprecated	As of version 1.5
		 */
		define( '_VALID_MOS', 1 );

		/**
		 * Legacy constant, use _JEXEC instead
		 * @deprecated	As of version 1.5
		 */
		define( '_MOS_MAMBO_INCLUDED', 1 );

		/**
		 * Legacy constant, use DATE_FORMAT_LC instead
		 * @deprecated	As of version 1.5
		 */
		DEFINE('_DATE_FORMAT_LC',"%A, %d %B %Y"); //Uses PHP's strftime Command Format

		/**
		 * Legacy constant, use DATE_FORMAT_LC2 instead
		 * @deprecated	As of version 1.5
		 */
		DEFINE('_DATE_FORMAT_LC2',"%A, %d %B %Y %H:%M");

		/**
		 * Legacy global, use JVersion->getLongVersion() instead
		 * @name $_VERSION
		 * @deprecated	As of version 1.5
		 */
		 $GLOBALS['_VERSION']	= new JVersion();
		 $version				= $GLOBALS['_VERSION']->getLongVersion();

		/**
		 * Legacy global, use JFactory::getDBO() instead
		 * @name $database
		 * @deprecated	As of version 1.5
		 */
		$conf =& JFactory::getConfig();
		$GLOBALS['database'] = new database($conf->getValue('config.host'), $conf->getValue('config.user'), $conf->getValue('config.password'), $conf->getValue('config.db'), $conf->getValue('config.dbprefix'));
		$GLOBALS['database']->debug($conf->getValue('config.debug'));

		/**
		 * Legacy global, use JFactory::getUser() [JUser object] instead
		 * @name $my
		 * @deprecated	As of version 1.5
		 */
		$user				=& JFactory::getUser();
		$GLOBALS['my']		= clone($user->getTable());
		$GLOBALS['my']->gid	= $user->get('aid', 0);

		/**
		 * Legacy global, use JApplication::getTemplate() instead
		 * @name $cur_template
		 * @deprecated	As of version 1.5
		 */
		global $mainframe;
		$GLOBALS['cur_template']	= $mainframe->getTemplate();

		//Insert configuration values into global scope (for backwards compatibility)
		$temp = new JConfig;
		foreach (get_object_vars($temp) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}

		$GLOBALS['mosConfig_live_site']		= substr_replace($mainframe->getSiteURL(), '', -1, 1);
		$GLOBALS['mosConfig_absolute_path']	= JPATH_SITE;
		/**
		 * Legacy global, use JFactory::getUser() instead
		 * @name $acl
		 * @deprecated	As of version 1.5
		 */
		$GLOBALS['acl'] =& JFactory::getACL();

		/**
		 * Legacy global
		 * @name $task
		 * @deprecated	As of version 1.5
		 */
		$GLOBALS['task'] = JRequest::getVar('task');

		/**
		 * Load the site language file (the old way - to be deprecated)
		 * @deprecated	As of version 1.5
		 */
		global $mosConfig_lang;
		$file = JPATH_SITE .'/language/' . $mosConfig_lang .'.php';
		if (file_exists( $file )) {
			require_once( $file);
		} else {
			$file = JPATH_SITE .'/language/english.php';
			if (file_exists( $file )) {
				require_once( $file );
			}
		}

		/**
		 *  Legacy global
		 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
		 *  use JPlugingHelper::importPlugin to load bot code
		 *  @deprecated As of version 1.5
		 */
		$GLOBALS['_MAMBOTS'] = new mosMambotHandler();
	}

	function onAfterRoute()
	{
		global $mainframe;
		if ($mainframe->isAdmin()) {
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
			case 'com_frontpage' :
				JRequest::setVar('option', 'com_content');
				JRequest::setVar('view', 'frontpage');
				break;
			case 'com_login'     : 
				JRequest::setVar('option', 'com_user');
				JRequest::setVar('view', 'login');
				break;
			case 'com_registration'     : 
				JRequest::setVar('option', 'com_user');
				JRequest::setVar('view', 'register');
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
?>