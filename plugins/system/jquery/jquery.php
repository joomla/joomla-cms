<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * jQuery plugin class.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.jQuery
 */
class plgSystemjQuery extends JPlugin
{
	/**
	 * Plugin that registers the jQuery javascript framework
	 */
	public function onAfterDispatch()
	{
		jimport('joomla.html.html.behavior');

		JHtmlBehavior::register('jquery', array(
			'script' => array('media/jquery/jquery.js' => array())
			)
		);
	}
}
