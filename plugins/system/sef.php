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
	function plgSystemSef(&$subject, $config)  {
		parent::__construct($subject, $config);
	}

	function onAfterRender()
	{
		global $mainframe;
		// check to see of SEF is enabled
		if(!$mainframe->getCfg('sef')) {
			return true;
		}
		if($mainframe->isAdmin()) {
			return true;
		}
		$document = JResponse::getBody();
		// check whether plugin has been unpublished
		
		//Replace src links
		$base = JURI::base(true).'/';
		$document = preg_replace("/(src)=\"(?!http|ftp|https|\/)([^\"]*)\"/", "$1=\"$base\$2\"", $document);

		//Replace href links
		$regex = "#href=\"(.*?)\"#s";

		// perform the replacement
		$document = preg_replace_callback( $regex, array($this, 'replaceHREF'), $document );
		JResponse::setBody($document);
		
		return true;
	}

	/**
	* Replaces the matched tags
	* 
	* @param array An array of matches (see preg_match_all)
	* @return string
	*/
	function replaceHREF( &$matches )
	{
		// original text that might be replaced
		$original = 'href="'. $matches[1] .'"';

		//Make sure we are dealing with HTTP urls...
		if(strpos($matches[1], 'http:') === false && strpos($matches[1], 'https:') === false && strpos($matches[1], ':')!== false) 
		{
			return $origional;
		}

		$uriLocal	=& JFactory::getURI();
		$uriHREF	=& JFactory::getURI($matches[1]);
	
		//disbale bot from being applied to external links
		if($uriLocal->getHost() !== $uriHREF->getHost() && !is_null($uriHREF->getHost()))
		{
			return $original;
		}
		if ( JString::strpos( $matches[1], 'index.php?option' ) !== false )
		{
			if ($qstring = $uriHREF->getQuery())
			{
				$qstring = '?' . $qstring;
			}
			if ($anchor = $uriHREF->getFragment())
			{
				$anchor = '#' . $anchor;
			}
			return 'href="'. JRoute::_( 'index.php' . $qstring ) . $uriHREF->getFragment() .'"';
		}
		
		if(is_null($uriHREF->getHost())) 
		{
                        //Relative link
                        $base = JFactory::getURI(JURI::base());
                        $baseURL = $base->getPath();
                        $base->setPath('/'.$this->combine($baseURL, $matches[1]));
                        $href = 'href="'.$base->toString().'"';
                        //Must set back so next link starts the same...
                        $base->setPath($baseURL);
                        return $href;
                }

                return $original;
        }

	function combine($ur1, $ur2) 
	{
                $ret = array();
                $arr1 = explode("/", $ur1);
                $arr2 = explode("/", $ur2);
                //strip null values
		foreach($arr1 AS $key => $val) 
		{
			if(!empty($val)) 
			{
                                $ret[] = $val;
                        }
                }
                $num_same = 0;
		foreach($arr2 AS $key => $val) 
		{
			if(!empty($val)) 
			{
				if(!in_array($val, $ret)) 
				{
                                        $ret[] = $val;
				}
                        }
                }
                return implode("/", $ret);
        }
}
