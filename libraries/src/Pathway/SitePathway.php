<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pathway;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
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
     * @param   SiteApplication  $app  Application Object
     *
     * @since   1.5
     */
    public function __construct(SiteApplication $app = null)
    {
        $this->pathway = array();

        $app  = $app ?: Factory::getContainer()->get(SiteApplication::class);
        $menu = $app->getMenu();
        $lang = Factory::getLanguage();

        if ($item = $menu->getActive()) {
            $menus = $menu->getMenu();

            // Look for the home menu
            if (Multilanguage::isEnabled()) {
                $home = $menu->getDefault($lang->getTag());
            } else {
                $home  = $menu->getDefault();
            }

            if (\is_object($home) && ($item->id != $home->id)) {
                foreach ($item->tree as $menupath) {
                    $link = $menu->getItem($menupath);

                    switch ($link->type) {
                        case 'separator':
                        case 'heading':
                            $url = null;
                            break;

                        case 'url':
                            if ((strpos($link->link, 'index.php?') === 0) && (strpos($link->link, 'Itemid=') === false)) {
                                // If this is an internal Joomla link, ensure the Itemid is set.
                                $url = $link->link . '&Itemid=' . $link->id;
                            } else {
                                $url = $link->link;
                            }
                            break;

                        case 'alias':
                            // If this is an alias use the item id stored in the parameters to make the link.
                            $url = 'index.php?Itemid=' . $link->getParams()->get('aliasoptions');
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
