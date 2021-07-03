<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;

/**
 * Newsfeeds Component Association Helper
 *
 * @since  3.0
 */
abstract class AssociationHelper extends CategoryAssociationHelper
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		$jinput = Factory::getApplication()->input;
		$view   = $view ?? $jinput->get('view');
		$id     = empty($id) ? $jinput->getInt('id') : $id;

		if ($view === 'newsfeed')
		{
			if ($id)
			{
				$associations = Associations::getAssociations('com_newsfeeds', '#__newsfeeds', 'com_newsfeeds.item', $id);

				$return = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = RouteHelper::getNewsfeedRoute($item->id, (int) $item->catid, $item->language);
				}

				return $return;
			}
		}

		if ($view === 'category' || $view === 'categories')
		{
			return self::getCategoryAssociations($id, 'com_newsfeeds');
		}

		return array();
	}
}
