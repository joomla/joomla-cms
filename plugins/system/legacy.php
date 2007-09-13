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
	  * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemLegacy(& $subject, $config)
	{
		parent::__construct($subject, $config);

		global $mainframe;

		// Define the 1.0 legacy mode constant
		define('_JLEGACY', '1.0');

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
		DEFINE('_DATE_FORMAT_LC', JText::_('DATE_FORMAT_LC1') ); //Uses PHP's strftime Command Format

		/**
		 * Legacy constant, use DATE_FORMAT_LC2 instead
		 * @deprecated	As of version 1.5
		 */
		DEFINE('_DATE_FORMAT_LC2', JText::_('DATE_FORMAT_LC2'));

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
		$user	=& JFactory::getUser();
		$table	= clone($user->getTable());
		$table->load($user->id);
		
		$GLOBALS['my']      = $table;
		$GLOBALS['my']->gid	= $user->get('aid', 0);

		/**
		 * Insert configuration values into global scope (for backwards compatibility)
		 * @deprecated	As of version 1.5
		 */

		$temp = new JConfig;
		foreach (get_object_vars($temp) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$GLOBALS['mosConfig_live_site']		= substr_replace($url, '', -1, 1);
		$GLOBALS['mosConfig_absolute_path']	= JPATH_SITE;
		$GLOBALS['mosConfig_cachepath']	= JPATH_BASE.DS.'cache';

		$GLOBALS['mosConfig_offset_user']	= 0;

		$lang =& JFactory::getLanguage();
		$GLOBALS['mosConfig_lang']          = $lang->getBackwardLang();
		
		$config->setValue('config.live_site', 		$GLOBALS['mosConfig_live_site']);
		$config->setValue('config.absolute_path', 	$GLOBALS['mosConfig_absolute_path']);
		$config->setValue('config.lang', 			$GLOBALS['mosConfig_lang']);

		/**
		 * Legacy global, use JFactory::getUser() instead
		 * @name $acl
		 * @deprecated	As of version 1.5
		 */
		$acl =& JFactory::getACL();

		// Legacy ACL's for backward compat
		$acl->addACL( 'administration', 'edit', 'users', 'super administrator', 'components', 'all' );
		$acl->addACL( 'administration', 'edit', 'users', 'administrator', 'components', 'all' );
		$acl->addACL( 'administration', 'edit', 'users', 'super administrator', 'user properties', 'block_user' );
		$acl->addACL( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_users' );
		$acl->addACL( 'administration', 'manage', 'users', 'administrator', 'components', 'com_users' );
		$acl->addACL( 'administration', 'config', 'users', 'super administrator' );
		//$acl->addACL( 'administration', 'config', 'users', 'administrator' );

		$acl->addACL( 'action', 'add', 'users', 'author', 'content', 'all' );
		$acl->addACL( 'action', 'add', 'users', 'editor', 'content', 'all' );
		$acl->addACL( 'action', 'add', 'users', 'publisher', 'content', 'all' );
		$acl->addACL( 'action', 'edit', 'users', 'author', 'content', 'own' );
		$acl->addACL( 'action', 'edit', 'users', 'editor', 'content', 'all' );
		$acl->addACL( 'action', 'edit', 'users', 'publisher', 'content', 'all' );
		$acl->addACL( 'action', 'publish', 'users', 'publisher', 'content', 'all' );

		$acl->addACL( 'action', 'add', 'users', 'manager', 'content', 'all' );
		$acl->addACL( 'action', 'edit', 'users', 'manager', 'content', 'all' );
		$acl->addACL( 'action', 'publish', 'users', 'manager', 'content', 'all' );

		$acl->addACL( 'action', 'add', 'users', 'administrator', 'content', 'all' );
		$acl->addACL( 'action', 'edit', 'users', 'administrator', 'content', 'all' );
		$acl->addACL( 'action', 'publish', 'users', 'administrator', 'content', 'all' );

		$acl->addACL( 'action', 'add', 'users', 'super administrator', 'content', 'all' );
		$acl->addACL( 'action', 'edit', 'users', 'super administrator', 'content', 'all' );
		$acl->addACL( 'action', 'publish', 'users', 'super administrator', 'content', 'all' );

		$acl->addACL( 'com_syndicate', 'manage', 'users', 'super administrator' );
		$acl->addACL( 'com_syndicate', 'manage', 'users', 'administrator' );
		$acl->addACL( 'com_syndicate', 'manage', 'users', 'manager' );

		$GLOBALS['acl'] =& $acl;

		/**
		 * Legacy global
		 * @name $task
		 * @deprecated	As of version 1.5
		 */
		$GLOBALS['task'] = JRequest::getString('task');

		/**
		 * Load the site language file (the old way - to be deprecated)
		 * @deprecated	As of version 1.5
		 */
		global $mosConfig_lang;
		$mosConfig_lang = JFilterInput::clean($mosConfig_lang, 'cmd');
		$file = JPATH_SITE.DS.'language'.DS.$mosConfig_lang.'.php';
		if (file_exists( $file )) {
			require_once( $file);
		} else {
			$file = JPATH_SITE.DS.'language'.DS.'english.php';
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

		switch(JRequest::getCmd('option'))
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

		/**
		 * Legacy global, use JApplication::getTemplate() instead
		 * @name $cur_template
		 * @deprecated	As of version 1.5
		 */
		$GLOBALS['cur_template'] = $mainframe->getTemplate();
	}

	function routeContent()
	{
		$viewName	= JRequest::getCmd( 'view', 'article' );
		$layout		= JRequest::getCmd( 'layout', 'default' );

		// interceptors to support legacy urls
		switch( JRequest::getCmd('task'))
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
		$viewName = JRequest::getCmd( 'view', 'categories' );

		// interceptors to support legacy urls
		switch( JRequest::getCmd('task'))
		{
			//index.php?option=com_newsfeeds&task=x&catid=xid=x&Itemid=x
			case 'view':
				$viewName	= 'newsfeed';
				break;

			default:
			{
				if(JRequest::getInt('catid') && !JRequest::getCmd('view')) {
					$viewName = 'category';
				}
			}
		}

		JRequest::setVar('view', $viewName);
	}

	function routeWeblinks()
	{
		$viewName = JRequest::getCmd( 'view', 'categories' );

		// interceptors to support legacy urls
		switch( JRequest::getCmd('task'))
		{
			//index.php?option=com_weblinks&task=x&catid=xid=x
			case 'view':
				$viewName	= 'weblink';
				break;

			default:
			{
				if(($catid = JRequest::getInt('catid')) && !JRequest::getCmd('view')) {
					$viewName = 'category';
					JRequest::setVar('id', $catid);
				}
			}
		}

		JRequest::setVar('view', $viewName);
	}
}
