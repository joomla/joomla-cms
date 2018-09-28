<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pathway;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Multilanguage;

/**
 * Class to manage the site application pathway.
 *
 * @since  1.5
 */
class SitePathway extends Pathway
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $options  The class options.
	 *
	 * @since   1.5
	 */
	public function __construct($options = array())
	{
		$this->_pathway = array();

		$app  = CMSApplication::getInstance('site');
		$menu = $app->getMenu();
		$lang = \JFactory::getLanguage();

		if ($item = $menu->getActive())
		{
			$menus = $menu->getMenu();

			// Look for the home menu
			if (Multilanguage::isEnabled())
			{
				$home = $menu->getDefault($lang->getTag());
			}
			else
			{
				$home  = $menu->getDefault();
			}

			if (is_object($home) && ($item->id != $home->id))
			{
				foreach ($item->tree as $menupath)
				{
					$link = $menu->getItem($menupath);

					switch ($link->type)
					{
						case 'separator':
						case 'heading':
							$url = null;
							break;

						case 'url':
							if ((strpos($link->link, 'index.php?') === 0) && (strpos($link->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$url = $link->link . '&Itemid=' . $link->id;
							}
							else
							{
								$url = $link->link;
							}
							break;

						case 'alias':
							// If this is an alias use the item id stored in the parameters to make the link.
							$url = 'index.php?Itemid=' . $link->params->get('aliasoptions');
							break;

						default:
							$url = $link->link . '&Itemid=' . $link->id;
							break;
					}

					$this->addItem($menus[$menupath]->title, $url);
				}
			}
		}
	}
}
