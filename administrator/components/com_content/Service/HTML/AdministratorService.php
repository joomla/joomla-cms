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
		$html                 = '';
		$globalMasterLanguage = Associations::getGlobalMasterLanguage();

		// Get the associations
		if ($associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.*')
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__content as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no master language used
			if (!$globalMasterLanguage)
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

			if ($globalMasterLanguage)
			{
				// Check whether the current article is written in the global master language
				$masterElement = (array_key_exists($articleid, $items)
					&& ($items[$articleid]->lang_code === $globalMasterLanguage))
					? true
					: false;

				// Check if there is a master item in the association and get his id if so
				$masterId = array_key_exists($globalMasterLanguage, $associations)
					? $associations[$globalMasterLanguage]
					: '';

				$assocParams = MasterAssociationsHelper::getAssociationsParams($associations, 'com_content.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$labelClass    = 'badge-success';
					$languageTitle = $item->language_title;
					$text          = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$title         = $item->title;
					$url           = Route::_('index.php?option=com_content&task=article.edit&id=' . (int) $item->id);

					if ($globalMasterLanguage)
					{

						// Don't continue for master, because it has been set here before
						if ($key === 'master')
						{
							continue;
						}

						// Don't display other children if the current item is a child of the master language.
						if (($key !== $articleid)
							&& ($globalMasterLanguage !== $item->lang_code)
							&& !$masterElement)
						{
							unset($items[$key]);
						}

						if ($key === $masterId)
						{
							$labelClass    .= ' master-item';
							$languageTitle = $item->language_title . ' - ' . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_LANGUAGE');
						}
						else
						{
							// get association state of child
							if ($masterId && array_key_exists($key, $assocParams) && array_key_exists($masterId, $assocParams))
							{
								$associatedModifiedMaster = $assocParams[$key];
								$lastModifiedMaster       = $assocParams[$masterId];

								if ($associatedModifiedMaster < $lastModifiedMaster)
								{
									$labelClass = 'badge-warning';
									$title      .= '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_OUTDATED_DESC') . '<br>';
								}
								else
								{
									$title .= '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_UP_TO_DATE_DESC') . '<br>';
								}
							}
						}
					}

					$classes = 'badge ' . $labelClass;
					$tooltip = '<strong>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);

					$item->link = '<a href="' . $url . '" title="' . $languageTitle . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the master item gets to the first place
					if ($item->lang_code === $globalMasterLanguage)
					{
						$items = array('master' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a master item doesn't exist, display that there is no association with the master language
				if ($globalMasterLanguage && !$masterId)
				{
					$link = MasterAssociationsHelper::addNotAssociatedMasterLink($globalMasterLanguage);

					// add this on the top of the array
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
	public function featured($value = 0, $i, $canChange = true)
	{
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
