<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Helper;

\defined('_JEXEC') or die;

/**
 * Category Component Association Helper
 *
 * @since  3.0
 */
abstract class CategoryAssociationHelper
{
	/**
	 * Flag if associations are present for categories
	 *
	 * @var    boolean
	 * @since  3.0
	 */
	public static $category_association = true;

	/**
	 * Method to get the associations for a given category
	 *
	 * @param   integer      $id         Id of the item
	 * @param   string       $extension  Name of the component
	 * @param   string|null  $layout     Category layout
	 *
	 * @return  array    Array of associations for the component categories
	 *
	 * @since  3.0
	 */
	public static function getCategoryAssociations($id = 0, $extension = 'com_content', $layout = null)
	{
		$return = array();

		if ($id)
		{
			$helperClassname = ucfirst(substr($extension, 4)) . 'HelperRoute';

			$associations = CategoriesHelper::getAssociations($id, $extension);

			foreach ($associations as $tag => $item)
			{
				if (class_exists($helperClassname) && \is_callable(array($helperClassname, 'getCategoryRoute')))
				{
					$return[$tag] = $helperClassname::getCategoryRoute($item, $tag, $layout);
				}
				else
				{
					$viewLayout = $layout ? '&layout=' . $layout : '';

					$return[$tag] = 'index.php?option=' . $extension . '&view=category&id=' . $item . $viewLayout;
				}
			}
		}

		return $return;
	}
}
