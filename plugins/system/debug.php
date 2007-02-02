<?php
/**
* @version		$Id: joomla.request.php 6093 2006-12-26 16:57:58Z louis $
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
class  plgDebug extends JPlugin
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
	function plgDebug(& $subject)
	{
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('system', 'debug');
		$this->_params = new JParameter($this->_plugin->params);
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	function onAfterRender()
	{
		global $_PROFILER, $mainframe;

		if(!JDEBUG) { return; }

		$db			=& JFactory::getDBO();
		$profiler	=& $_PROFILER;

		ob_start();
		echo implode( '', $profiler->getBuffer() );

		if ($this->_params->get('memory', 1))
		{
			echo '<br />';
			echo $profiler->getMemory();
		}

		if ($this->_params->get('queries', 1))
		{
			echo '<br />';
			echo $db->_ticker . ' queries logged';
			echo '<pre>';
			foreach ($db->_log as $k=>$sql) {
				echo $k+1 . "\n" . $sql . '<hr />';
			}
			echo '</pre>';
		}

		$debug = ob_get_clean();
		JResponse::appendBody($debug);
	}
}

// Attach sef handler to event dispatcher
$dispatcher = & JEventDispatcher::getInstance();
$dispatcher->attach(new plgDebug($dispatcher));

?>