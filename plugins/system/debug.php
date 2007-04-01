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
class  plgSystemDebug extends JPlugin
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
	function plgSystemDebug(& $subject)
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
		$lang		=& JFactory::getLanguage();
		$lang->load( 'plg_system_debug', JPATH_ADMINISTRATOR );

		ob_start();
		echo '<div id="system-debug" class="profiler">';
		echo implode( '', $profiler->getBuffer() );

		if ($this->_params->get('memory', 1)) {
			echo '<p><h4>'.JText::_( 'Memory Usage' ).'</h4>';
			echo $profiler->getMemory().'</p>';
		}

		if ($this->_params->get('queries', 1))
		{
			echo '<p>';
			echo '<h4>'.JText::sprintf( 'Queries logged',  $db->_ticker ).'</h4>';
			echo '<ol>';
			foreach ($db->_log as $k=>$sql) {
				$text = $db->beautify( $sql );
				echo '<li><pre>'.$text.'</pre></li>';
			}
			echo '</ol></p>';
		}

		if ($this->_params->get('language', 1))
		{
			echo '<p><h4>'.JText::_( 'Untranslated strings' ).'</h4>';
			echo '<pre>';
			$orphans = array_unique( $lang->getOrphans() );
			if (count( $orphans ))
			{
				sort( $orphans );
				foreach ($orphans as $string) {
					echo strtoupper( $string ).'='.$string."\n";
				}
			}
			else {
				echo JText::_( 'None' );
			}
			echo '</pre>';
		}
		echo '</div>';

		$debug = ob_get_clean();
		JResponse::appendBody($debug);
	}
}