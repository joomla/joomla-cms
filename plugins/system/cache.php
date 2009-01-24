<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Joomla! Page Cache Plugin
 *
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemCache extends JPlugin
{

	var $_cache = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.6
	 */
	function __construct($subject, $config)
	{
		$user =& JFactory::getUser();

		$options = array(
			'cachebase' 	=> JPATH_BASE.DS.'cache',
			'defaultgroup' 	=> 'page',
			'lifetime' 		=> $this->params->get('cachetime', 15) * 60,
			'browsercache'	=> $this->params->get('browsercache', false),
			'caching'		=> false
		);

		jimport('joomla.cache.cache');
		$this->_cache =& JCache::getInstance( 'page', $options );

		if (!$user->get('aid') && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->_cache->setCaching(true);
		}

		parent::__construct($subject, $config);
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	function onAfterInitialise()
	{
		global $mainframe, $_PROFILER;

		 if($mainframe->isAdmin() || JDEBUG) {
		 	return;
		 }


		$data  = $this->_cache->get();

		if($data !== false)
		{
			// the following code searches for a token in the cached page and replaces it with the
			// proper token.
			$user	= &JFactory::getUser();
			$app = JFactory::getApplication();
			$token	= JUtility::getToken();
			$search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="'.$token.'" value="1" />';
			$data = preg_replace( $search, $replacement, $data );

			JResponse::setBody($data);

			$app->triggerEvent('onAfterCacheRender');

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

		if($mainframe->isAdmin() || JDEBUG) {
			return;
		}

		$this->_cache->store();
	}
}
