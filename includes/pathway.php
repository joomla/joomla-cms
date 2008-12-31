<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Class to manage the site application pathway
 *
 * @package 	Joomla
 * @since		1.5
 */
class JPathwaySite extends JPathway
{
	/**
	 * Class constructor
	 */
	public function __construct($options = array())
	{
		//Initialise the array
		$this->_pathway = array();
		$app = JFactory::getApplication();
		$menu   =& $app->getMenu();

		if($item = $menu->getActive())
		{
			$menus	= $menu->getMenu();
			$home	= $menu->getDefault();

			if(is_object($home) && ($item->id != $home->id))
			{
				foreach($item->tree as $menupath)
				{
					$url  = '';
					$link = $menu->getItem($menupath);

					switch($link->type)
					{
						case 'menulink':
						case 'url':
							$url = $link->link;
							break;
						case 'separator':
							$url = null;
							break;
						default:
							$url = 'index.php?Itemid='.$link->id;
					}

					$this->addItem( $menus[$menupath]->name, $url);

				} // end foreach
			}
		} // end if getActive
	}
}
