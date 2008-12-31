<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once dirname(__FILE__).DS.'legacy'.DS.'legacy.php';

jimport( 'joomla.plugin.plugin' );

/**
 * Joomla! Legacy plugin
 *
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemLegacy extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param	object		$subject The object to observe
	 * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
	}
}