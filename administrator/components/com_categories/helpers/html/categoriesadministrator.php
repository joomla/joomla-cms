<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

/**
 * Administrator category HTML
 *
 * @since  3.2
 */
abstract class JHtmlCategoriesAdministrator
{
	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $catid      Category identifier to search its associations
	 * @param   string   $extension  Category Extension
	 *
	 * @return  string   The language HTML
	 *
	 * @since   3.2
	 * @throws  Exception
	 */
	public static function association($catid, $extension = 'com_content')
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = CategoriesHelper::getAssociations($catid, $extension))
		{
			$associations = ArrayHelper::toInteger($associations);

			// Get the associated categories
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.title')
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__categories as c')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->where('c.id != ' . $catid)
				->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text       = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$url        = Route::_('index.php?option=com_categories&task=category.edit&id=' . (int) $item->id . '&extension=' . $extension);
					$classes    = 'hasPopover badge badge-secondary';
					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			JHtml::_('bootstrap.popover');

			$html = \Joomla\CMS\Layout\LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
