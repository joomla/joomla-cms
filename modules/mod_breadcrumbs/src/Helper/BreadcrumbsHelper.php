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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
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
		$items   = $pathway->getPathway();
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

	/**
	 * Set the breadcrumbs separator for the breadcrumbs display.
	 *
	 * @param   string  $custom  Custom xhtml compliant string to separate the items of the breadcrumbs
	 *
	 * @return  string	Separator string
	 *
	 * @since   1.5
	 */
	public static function setSeparator($custom = null)
	{
		$lang = Factory::getApplication()->getLanguage();

		// If a custom separator has not been provided we try to load a template
		// specific one first, and if that is not present we load the default separator
		if ($custom === null)
		{
			if ($lang->isRtl())
			{
				$_separator = HTMLHelper::_('image', 'system/arrow_rtl.png', null, null, true);
			}
			else
			{
				$_separator = HTMLHelper::_('image', 'system/arrow.png', null, null, true);
			}
		}
		else
		{
			$_separator = htmlspecialchars($custom, ENT_COMPAT, 'UTF-8');
		}

		return $_separator;
	}
}
