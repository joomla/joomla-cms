<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Breadcrumbs\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Helper for mod_breadcrumbs
 *
 * @since  1.5
 */
class BreadcrumbsHelper
{
	/**
	 * Retrieve breadcrumb items
	 *
	 * @param   Registry        $params  The module parameters
	 * @param   CMSApplication  $app     The application
	 *
	 * @return  array
	 */
	public static function getList(Registry $params, CMSApplication $app)
	{
		// Get the PathWay object from the application
		$pathway = $app->getPathway();
		$items   = $pathway->getPathWay();
		$lang    = $app->getLanguage();
		$menu    = $app->getMenu();

		// Look for the home menu
		if (Multilanguage::isEnabled())
		{
			$home = $menu->getDefault($lang->getTag());
		}
		else
		{
			$home  = $menu->getDefault();
		}

		$count = \count($items);

		// Don't use $items here as it references JPathway properties directly
		$crumbs = array();

		for ($i = 0; $i < $count; $i++)
		{
			$crumbs[$i]       = new \stdClass;
			$crumbs[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
			$crumbs[$i]->link = $items[$i]->link;
		}

		if ($params->get('showHome', 1))
		{
			$item       = new \stdClass;
			$item->name = htmlspecialchars($params->get('homeText', Text::_('MOD_BREADCRUMBS_HOME')), ENT_COMPAT, 'UTF-8');
			$item->link = 'index.php?Itemid=' . $home->id;
			array_unshift($crumbs, $item);
		}

		return $crumbs;
	}
}
