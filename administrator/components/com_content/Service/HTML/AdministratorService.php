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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\MasterAssociationsHelper;
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
		$globalMasterLang = Associations::getGlobalMasterLanguage();

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
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__content as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no master language used
			if (!$globalMasterLang)
			{
				$query->where('c.id != ' . $articleid);
			}

			$query->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($globalMasterLang)
			{
				// Check if the current item is a master item.
				$isMaster = (array_key_exists($articleid, $items) && ($items[$articleid]->lang_code === $globalMasterLang))
					? true
					: false;

				// Check if there is a master item in the association and get its id if so
				$masterId = array_key_exists($globalMasterLang, $associations)
					? $associations[$globalMasterLang]
					: '';

				// Get master dates of each item of associations.
				$assocMasterDates = MasterAssociationsHelper::getMasterDates($associations, 'com_content.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$masterInfo = '';
					$labelClass = 'badge-success';
					$url        = Route::_('index.php?option=com_content&task=article.edit&id=' . (int) $item->id);

					if ($globalMasterLang)
					{
						// Don't continue for master, because it has been set here before
						if ($key === 'master')
						{
							continue;
						}

						$classMasterInfoItems = MasterAssociationsHelper::setMasterAndChildInfos(
							$articleid, $items, $key, $item, $globalMasterLang, $isMaster, $masterId, $assocMasterDates, $saveHistory
						);
						$labelClass  = $classMasterInfoItems[0];
						$masterInfo  = $classMasterInfoItems[1];
						$items       = $classMasterInfoItems[2];
						$needsUpdate = $classMasterInfoItems[3];

						$url = Route::_(
							MasterAssociationsHelper::getAssociationUrl(
								$item->id, $globalMasterLang, 'com_content.article', $item->lang_code, $key, $masterId, $needsUpdate
							)
						);
					}

					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title) . $masterInfo;
					$classes = 'badge ' . $labelClass;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the master item gets to the first place
					if ($item->lang_code === $globalMasterLang)
					{
						$items = array('master' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a master item doesn't exist, display that there is no association with the master language
				if ($globalMasterLang && !$masterId)
				{
					$link = MasterAssociationsHelper::addNotAssociatedMasterLink($globalMasterLang, $articleid, 'com_content.article');

					// Add this on the top of the array
					$items = array('master' => array('link' => $link)) + $items;
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Show the feature/unfeature links
	 *
	 * @param   integer  $value      The state value
	 * @param   integer  $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public function featured($value = 0, $i = 0, $canChange = true)
	{
		if ($i === 0)
		{
			throw new \InvalidArgumentException('$i is not allowed to be 0');
		}

		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'articles.featured', 'COM_CONTENT_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'articles.unfeatured', 'COM_CONTENT_FEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="tbody-icon hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. HTMLHelper::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
