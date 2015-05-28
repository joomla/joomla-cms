<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

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
	 */
	public static function association($catid, $extension = 'com_content')
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = CategoriesHelper::getAssociations($catid, $extension))
		{
			JArrayHelper::toInteger($associations);

			// Get the associated categories
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.title')
				->select('l.sef as lang_sef')
				->from('#__categories as c')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
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
				throw new Exception($e->getMessage(), 500);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_categories&task=category.edit&id=' . (int) $item->id . '&extension=' . $extension);
					$tooltipParts = array(
						JHtml::_(
							'image', 'mod_languages/' . $item->image . '.gif',
							$item->language_title,
							array('title' => $item->language_title),
							true
						),
						$item->title
					);

					$item->link = JHtml::_(
						'tooltip',
						implode(' ', $tooltipParts),
						null,
						null,
						$text,
						$url,
						null,
						'hasTooltip label label-association label-' . $item->lang_sef
					);
				}
			}

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
