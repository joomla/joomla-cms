<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\DefaultAssocLangHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Content HTML helper
 *
 * @since  3.0
 */
class AdministratorService
{

	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $articleid  The article item id
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  \Exception
	 */
	public function association($articleid)
	{
		// Defaults
		$html             = '';
		$defaultAssocLang = Associations::getDefaultAssocLang();

		// Check if versions are enabled
		$saveHistory = ComponentHelper::getParams('com_content')->get('save_history', 0);

		// Get the associations
		if ($associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.*')
				->select($db->quoteName(['l.lang_code', 'l.image']))
				->select($db->quoteName(['l.sef', 'cat.title', 'l.title'], ['lang_sef', 'category_title', 'language_title']))
				->from($db->quoteName('#__content', 'c'))
				->leftJoin($db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
				->whereIN($db->quoteName('c.id'), array_values($associations));

			// Don't get the id of the item itself when there is no default association language used.
			if (!$defaultAssocLang)
			{
				$query->where($db->quoteName('c.id') . ' != :id')
					->bind(':id', $articleid, ParameterType::INTEGER);
			}

			$query->leftJoin($db->quoteName('#__languages', 'l'),  $db->quoteName('c.language') . ' = ' .  $db->quoteName('l.lang_code'));
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($defaultAssocLang)
			{
				// Check if the current item is a parent.
				$isParent = (array_key_exists($articleid, $items) && ($items[$articleid]->lang_code === $defaultAssocLang))
					? true
					: false;

				// Check if there is a parent in the association and get its id if so
				$parentId = array_key_exists($defaultAssocLang, $associations)
					? $associations[$defaultAssocLang]
					: '';

				// Get parent dates of each item of associations.
				$assocParentDates = DefaultAssocLangHelper::getParentDates($associations, 'com_content.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$parentChildInfo = '';
					$labelClass      = 'badge-success';
					$url             = Route::_('index.php?option=com_content&task=article.edit&id=' . (int) $item->id);

					if ($defaultAssocLang)
					{
						// Don't continue for parent, because it has been set here before
						if ($key === 'parent')
						{
							continue;
						}

						$classParentInfoItems = DefaultAssocLangHelper::setParentAndChildInfos(
							$articleid, $items, $key, $item, $defaultAssocLang, $isParent, $parentId, $assocParentDates, $saveHistory
						);
						$labelClass      = $classParentInfoItems[0];
						$parentChildInfo = $classParentInfoItems[1];
						$items           = $classParentInfoItems[2];
						$needsUpdate     = $classParentInfoItems[3];

						$url = Route::_(
							DefaultAssocLangHelper::getAssociationUrl(
								$item->id, $defaultAssocLang, 'com_content.article', $item->lang_code, $key, $parentId, $needsUpdate
							)
						);
					}

					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title) . $parentChildInfo;
					$classes = 'badge ' . $labelClass;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the parent gets to the first place
					if ($item->lang_code === $defaultAssocLang)
					{
						$items = array('parent' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a parent doesn't exist, display that there is no association with the default association language.
				if ($defaultAssocLang && !$parentId)
				{
					$link = DefaultAssocLangHelper::addNotAssociatedParentLink($defaultAssocLang, $articleid, 'com_content.article');

					// Add this on the top of the array
					$items = array('parent' => array('link' => $link)) + $items;
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
