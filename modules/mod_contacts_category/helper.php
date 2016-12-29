<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_contacts_category
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

$com_path = JPATH_SITE . '/components/com_contact/';

JLoader::register('ContactHelperRoute', $com_path . 'helpers/route.php');
JModelLegacy::addIncludePath($com_path . 'models', 'ContactModel');

/**
 * Helper for mod_contacts_category
 *
 * @package     Joomla.Site
 * @subpackage  mod_contacts_category
 *
 * @since       __DEPLOY_VERSION__
 */
abstract class ModContactsCategoryHelper
{
	/**
	 * Get a list of contacts from a specific category
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getList(&$params)
	{
		// Get an instance of the generic contacts model
		$contacts = JModelLegacy::getInstance('Contacts', 'ContactModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = JFactory::getApplication();
		$appParams = $app->getParams();
		$contacts->setState('params', $appParams);

		// Set the filters based on the module params
		$contacts->setState('list.start', 0);
		$contacts->setState('list.limit', (int) $params->get('count', 0));
		$contacts->setState('filter.published', 1);

		// Access filter
		$access     = !JComponentHelper::getParams('com_contact')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$contacts->setState('filter.access', $access);

		// Prep for Normal or Dynamic Modes
		$mode = $params->get('mode', 'normal');

		switch ($mode)
		{
			case 'dynamic' :
				$option = $app->input->get('option');
				$view   = $app->input->get('view');

				if ($option === 'com_contact')
				{
					switch ($view)
					{
						case 'category' :
							$catids = array($app->input->getInt('id'));
							break;
						case 'categories' :
							$catids = array($app->input->getInt('id'));
							break;
						case 'contact' :
							if ($params->get('show_on_contact_page', 1))
							{
								$contact_id = $app->input->getInt('id');
								$catid      = $app->input->getInt('catid');

								if (!$catid)
								{
									// Get an instance of the generic contact model
									$contact = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));

									$contact->setState('params', $appParams);
									$contact->setState('filter.published', 1);
									$contact->setState('contact.id', (int) $contact_id);
									$item   = $contact->getItem();
									$catids = array($item->catid);
								}
								else
								{
									$catids = array($catid);
								}
							}
							else
							{
								// Return right away if show_on_contact_page option is off
								return;
							}
							break;

						case 'featured' :
						default:
							// Return right away if not on the category or contact views
							return;
					}
				}
				else
				{
					// Return right away if not on a com_contact page
					return;
				}

				break;

			case 'normal' :
			default:
				$catids = $params->get('catid');
				$contacts->setState('filter.category_id.include', (bool) $params->get('category_filtering_type', 1));
				break;
		}

		// Category filter
		if ($catids)
		{
			if ($params->get('show_child_category_contacts', 0) && (int) $params->get('levels', 0) > 0)
			{
				// Get an instance of the generic categories model
				$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$categories->setState('params', $appParams);
				$levels = $params->get('levels', 1) ? $params->get('levels', 1) : 9999;
				$categories->setState('filter.get_children', $levels);
				$categories->setState('filter.published', 1);
				$categories->setState('filter.access', $access);
				$additional_catids = array();

				foreach ($catids as $catid)
				{
					$categories->setState('filter.parentId', $catid);
					$recursive = true;
					$items     = $categories->getItems($recursive);

					if ($items)
					{
						foreach ($items as $category)
						{
							$condition = (($category->level - $categories->getParent()->level) <= $levels);

							if ($condition)
							{
								$additional_catids[] = $category->id;
							}
						}
					}
				}

				$catids = array_unique(array_merge($catids, $additional_catids));
			}

			$contacts->setState('filter.category_id', $catids);
		}

		// Ordering
		$ordering = $params->get('contact_ordering', 'a.ordering');

		switch ($ordering)
		{
			case 'random':
				$contacts->setState('list.ordering', JFactory::getDbo()->getQuery(true)->Rand());
				break;
			default:
				$contacts->setState('list.ordering', $ordering);
				$contacts->setState('list.direction', $params->get('contact_ordering_direction', 'ASC'));
				break;
		}

		// New Parameters
		$contacts->setState('filter.featured', $params->get('show_front', 'show'));
		$excluded_contacts = $params->get('excluded_contacts', '');

		if ($excluded_contacts)
		{
			$excluded_contacts = explode("\r\n", $excluded_contacts);
			$contacts->setState('filter.contact_id', $excluded_contacts);

			// Exclude
			$contacts->setState('filter.contact_id.include', false);
		}

		// Filter by language
		$contacts->setState('filter.language', $app->getLanguageFilter());

		$items = $contacts->getItems();

		// Display options
		$show_category    = $params->get('show_category', 0);
		$show_hits        = $params->get('show_hits', 0);

		// Find current Article ID if on an contact page
		$option = $app->input->get('option');
		$view   = $app->input->get('view');

		if ($option === 'com_contact' && $view === 'contact')
		{
			$active_contact_id = $app->input->getInt('id');
		}
		else
		{
			$active_contact_id = 0;
		}

		// Prepare data for display using display options
		foreach ($items as &$item)
		{
			$item->slug    = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the contact
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$menu      = $app->getMenu();
				$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');

				if (isset($menuitems[0]))
				{
					$Itemid = $menuitems[0]->id;
				}
				elseif ($app->input->getInt('Itemid') > 0)
				{
					// Use Itemid from requesting page only if there is no existing menu
					$Itemid = $app->input->getInt('Itemid');
				}

				$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
			}

			// Used for styling the active contact
			$item->active      = $item->id == $active_contact_id ? 'active' : '';

			if ($item->catid)
			{
				$item->displayCategoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));
				$item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
			}
			else
			{
				$item->displayCategoryTitle = $show_category ? $item->category_title : '';
			}

			$item->displayHits       = $show_hits ? $item->hits : '';
		}

		return $items;
	}


	/**
	 * Groups items by field
	 *
	 * @param   array   $list                        list of items
	 * @param   string  $fieldName                   name of field that is used for grouping
	 * @param   string  $contact_grouping_direction  ordering direction
	 * @param   null    $fieldNameToKeep             field name to keep
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function groupBy($list, $fieldName, $contact_grouping_direction, $fieldNameToKeep = null)
	{
		$grouped = array();

		if (!is_array($list))
		{
			if ($list == '')
			{
				return $grouped;
			}

			$list = array($list);
		}

		foreach ($list as $key => $item)
		{
			if (!isset($grouped[$item->$fieldName]))
			{
				$grouped[$item->$fieldName] = array();
			}

			if (is_null($fieldNameToKeep))
			{
				$grouped[$item->$fieldName][$key] = $item;
			}
			else
			{
				$grouped[$item->$fieldName][$key] = $item->$fieldNameToKeep;
			}

			unset($list[$key]);
		}

		$contact_grouping_direction($grouped);

		return $grouped;
	}
}
