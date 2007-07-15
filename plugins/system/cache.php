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

jimport('joomla.cache.cache');

/**
 * Joomla! Page Cache Plugin
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemCache extends JPlugin
{

	var $_cache = null;

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
	function plgSystemCache(& $subject)
	{
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('system', 'cache');
		$this->_params = new JParameter($this->_plugin->params);

		$user =& JFactory::getUser();

		$options = array(
			'cachebase' 	=> JPATH_BASE.DS.'cache',
			'defaultgroup' 	=> 'page',
			'lifetime' 		=> $this->_params->get('cachetime', 15) * 60,
			'browsercache'	=> $this->_params->get('browsercache', false)
		);

		$this->_cache =& JCache::getInstance( 'page', $options );

		if (!$user->get('aid') && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->_cache->setCaching(true);
		}
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	function onAfterInitialise()
	{
		global $mainframe, $_PROFILER;

		 if($mainframe->isAdmin()) {
		 	return;
		 }

		$data  = $this->_cache->get();

		if($data !== false)
		{
			JResponse::setBody($data);
			echo JResponse::toString($mainframe->getCfg('gzip'));

			if(JDEBUG)
			{
				$_PROFILER->mark('afterCache');
				echo implode( '', $_PROFILER->getBuffer());
			}

			$mainframe->close();
		}
	}

	function onAfterRender()
	{
		global $mainframe;

		if($mainframe->isAdmin()) {
			return;
		}

		$this->_cache->store();
	}
}