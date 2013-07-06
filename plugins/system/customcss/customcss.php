<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Joomla! custom.css Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.customcss
 * @since       3.2.0
 */
class plgSystemcustomcss extends JPlugin
{
	/**
	 * Add, if exists, the custom.css file from the css folder from the template
	 *
	 * @return  void
	 * @since   3.2.0
	 */
	public function onBeforeCompileHead()
	{	
		$app = JFactory::getApplication();		
		$doc = JFactory::getDocument();
		
		$custom = 'templates/'.$app->getTemplate().'/css/custom.css';
		if (is_file($custom)) 
		{
			$doc->addStyleSheet($custom);
		}
	}
}
