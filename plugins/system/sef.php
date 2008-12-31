<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

/**
* Joomla! SEF Plugin
*
* @package 		Joomla
* @subpackage	System
*/
class plgSystemSef extends JPlugin
{
	/**
	 * Converting the site URL to fit to the HTTP request
	 */
	public function onAfterRender()
	{
		$app =& JFactory::getApplication();

		if($app->getName() != 'site') {
			return true;
		}

		//Replace src links
		$base   = JURI::base(true).'/';
		$buffer = JResponse::getBody();

		$regex  = '#href="index.php\?([^"]*)#m';
		$buffer = preg_replace_callback( $regex, array($this, 'route'), $buffer );

		$protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex	 = '#(src|href)="(?!/|'.$protocols.'|\#)([^"]*)"#m';
		$buffer	= preg_replace($regex, "$1=\"$base\$2\"", $buffer);
		$regex	 = '#(onclick="window.open\(\')(?!/|'.$protocols.'|\#)([^/]+[^\']*?\')#m';
		$buffer	= preg_replace($regex, '$1'.$base.'$2', $buffer);

		JResponse::setBody($buffer);
		return true;
	}

	/**
	 * Replaces the matched tags
	 *
	 * @param array An array of matches (see preg_match_all)
	 * @return string
	 */
   	 protected function route( &$matches )
	 {
		$original	= $matches[0];
		$url		= $matches[1];
		$url = str_replace('&amp;','&',$url);
		$route		= JRoute::_('index.php?'.$url);
		return 'href="'.$route;
	}
}
