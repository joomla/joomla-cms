<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Associations component helper.
 *
 * @since  3.7.0
 */
class AssociationsHelper extends ContentHelper
{
	/**
	 * Array of Registry objects of extensions
	 *
	 * var      array   $extensionsSupport
	 *
	 * @since  3.7.0
	 */
	public static $extensionsSupport = null;

	/**
	 * List of extensions name with support
	 *
	 * var      array   $supportedExtensionsList
	 *
	 * @since  3.7.0
	 */
	public static $supportedExtensionsList = array();

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  array
	 *
	 * @since  3.7.0
	 */
	public static function getAssociationList($extensionName, $typeName, $itemId)
	{
		if (!self::hasSupport($extensionName))
		{
			return array();
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		return $helper->getAssociationList($typeName, $itemId);

	}

	/**
	 * Get the the instance of the extension helper class
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return  \Joomla\CMS\Association\AssociationExtensionHelper|null
	 *
	 * @since  3.7.0
	 */
	public static function getExtensionHelper($extensionName)
	{
		if (!self::hasSupport($extensionName))
		{
			return null;
		}

		$support = self::$extensionsSupport[$extensionName];

		return $support->get('helper');
	}

	/**
	 * Get item information
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  \Joomla\CMS\Table\Table|null
	 *
	 * @since  3.7.0
	 */
	public static function getItem($extensionName, $typeName, $itemId)
	{
		if (!self::hasSupport($extensionName))
		{
			return array();
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		return $helper->getItem($typeName, $itemId);
	}

	/**
	 * Check if extension supports associations
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return  boolean
	 *
	 * @since  3.7.0
	 */
	public static function hasSupport($extensionName)
	{
		if (is_null(self::$extensionsSupport))
		{
			self::getSupportedExtensions();
		}

		return in_array($extensionName, self::$supportedExtensionsList);
	}

	/**
	 * Loads the helper for the given class.
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return  AssociationExtensionInterface|null
	 *
	 * @since  4.0.0
	 */
	private static function loadHelper($extensionName)
	{
		$component = Factory::getApplication()->bootComponent($extensionName);

		if ($component instanceof AssociationServiceInterface)
		{
			return $component->getAssociationsExtension();
		}

		// Check if associations helper exists
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/' . $extensionName . '/helpers/associations.php'))
		{
			return null;
		}

		require_once JPATH_ADMINISTRATOR . '/components/' . $extensionName . '/helpers/associations.php';

		$componentAssociationsHelperClassName = self::getExtensionHelperClassName($extensionName);

		if (!class_exists($componentAssociationsHelperClassName, false))
		{
			return null;
		}

		// Create an instance of the helper class
		return new $componentAssociationsHelperClassName;
	}

	/**
	 * Get the extension specific helper class name
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return  boolean
	 *
	 * @since  3.7.0
	 */
	private static function getExtensionHelperClassName($extensionName)
	{
		$realName = self::getExtensionRealName($extensionName);

		return ucfirst($realName) . 'AssociationsHelper';
	}

	/**
	 * Get the real extension name. This means without com_
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return  string
	 *
	 * @since  3.7.0
	 */
	private static function getExtensionRealName($extensionName)
	{
		return strpos($extensionName, 'com_') === false ? $extensionName : substr($extensionName, 4);
	}

	/**
	 * Get the associated language edit links Html.
	 *
	 * @param   string   $extensionName   Extension Name
	 * @param   string   $typeName        ItemType
	 * @param   integer  $itemId          Item id.
	 * @param   string   $itemLanguage    Item language code.
	 * @param   boolean  $addLink         True for adding edit links. False for just text.
	 * @param   boolean  $assocLanguages  True for showing non associated content languages. False only languages with associations.
	 * @param   string   $assocState      The filter association state, enabled when a global master language is used.
	 *
	 * @return  string   The language HTML
	 *
	 * @since  3.7.0
	 */
	public static function getAssociationHtmlList($extensionName, $typeName, $itemId, $itemLanguage,
		$addLink = true, $assocLanguages = true, $assocState = 'all')
	{
		// Get the associations list for this item.
		$items   = self::getAssociationList($extensionName, $typeName, $itemId);

		$titleFieldName = self::getTypeFieldName($extensionName, $typeName, 'title');

		// Get all content languages.
		$languages = LanguageHelper::getContentLanguages(array(0, 1));

		$canEditReference = self::allowEdit($extensionName, $typeName, $itemId);
		$canCreate        = self::allowAdd($extensionName, $typeName);

		// Get the global master language, empty if not used
		$globalMasterLang = Associations::getGlobalMasterLanguage();

		// Check if versions are enabled
		$saveHistory      = ComponentHelper::getParams($extensionName)->get('save_history', 0);
		$context          = ($typeName === 'category') ? 'com_categories.item' : $extensionName . '.item';

		if ($items)
		{
			// Get master dates of each item of an association and the master id.
			$assocMasterDates = MasterAssociationsHelper::getMasterDates($items, $context);
			$masterId = $items[$globalMasterLang]['id'] ?? null;
		}

		// Create associated items list.
		foreach ($languages as $langCode => $language)
		{
			// Defaults
			$update     = false;
			$masterInfo = '';

			if (!$globalMasterLang)
			{
				// Don't do for the reference language.
				if ($langCode == $itemLanguage)
				{
					continue;
				}

				// Don't show languages with associations, if we don't want to show them.
				if ($assocLanguages && isset($items[$langCode]))
				{
					unset($items[$langCode]);
					continue;
				}

				// Don't show languages without associations, if we don't want to show them.
				if (!$assocLanguages && !isset($items[$langCode]))
				{
					continue;
				}
			}

			// Get html parameters for associated items.
			if (isset($items[$langCode]))
			{
				if ($globalMasterLang)
				{
					// Don't display any other children to the child item.
					if (($itemLanguage !== $globalMasterLang) && ($langCode !== $globalMasterLang) && ($langCode !== $itemLanguage))
					{
						unset($items[$langCode]);
						continue;
					}
				}

				$title      = $items[$langCode][$titleFieldName];
				$additional = '';

				if (isset($items[$langCode]['catid']))
				{
					$db = Factory::getDbo();

					// Get the category name
					$query = $db->getQuery(true)
						->select($db->quoteName('title'))
						->from($db->quoteName('#__categories'))
						->where($db->quoteName('id') . ' = ' . $db->quote($items[$langCode]['catid']));

					$db->setQuery($query);
					$category_title = $db->loadResult();

					$additional = '<strong>' . Text::sprintf('JCATEGORY_SPRINTF', $category_title) . '</strong> <br>';
				}
				elseif (isset($items[$langCode]['menutype']))
				{
					$db = Factory::getDbo();

					// Get the menutype name
					$query = $db->getQuery(true)
						->select($db->quoteName('title'))
						->from($db->quoteName('#__menu_types'))
						->where($db->quoteName('menutype') . ' = ' . $db->quote($items[$langCode]['menutype']));

					$db->setQuery($query);
					$menutype_title = $db->loadResult();

					$additional = '<strong>' . Text::sprintf('COM_MENUS_MENU_SPRINTF', $menutype_title) . '</strong><br>';
				}

				$labelClass  = 'badge-success';
				$target      = $langCode . ':' . $items[$langCode]['id'] . ':edit';
				$allow       = $canEditReference
								&& self::allowEdit($extensionName, $typeName, $items[$langCode]['id'])
								&& self::canCheckinItem($extensionName, $typeName, $items[$langCode]['id']);

				$masterInfoSpace = $additional && !$addLink ? '<br>' : '<br><br>';

				if ($globalMasterLang)
				{
					// Settings for the master language
					if ($globalMasterLang === $langCode)
					{
						$labelClass .= ' master-item';
						$additional .= $addLink && $allow ? Text::_('COM_ASSOCIATIONS_EDIT_ASSOCIATION') : '';
						$masterInfo  = $masterInfoSpace . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_ITEM');

						if ($globalMasterLang === $itemLanguage)
						{
							// Do not define any child target as there can be more than one
							$target = '';
						}
						else
						{
							$target = $itemLanguage . ':' . $itemId . ':edit';
						}
					}
					// Setting for children
					else
					{
						// When there is no associated master item, set it to target
						if (!$masterId)
						{
							$target        = $globalMasterLang . ':0:add';
						}

						if (array_key_exists($items[$langCode]['id'], $assocMasterDates) && array_key_exists($masterId, $assocMasterDates))
						{
							$associatedModifiedMaster = $assocMasterDates[$items[$langCode]['id']];
							$lastModifiedMaster = $assocMasterDates[$masterId];

							if ($associatedModifiedMaster < $lastModifiedMaster)
							{
								// Don't display not corresponding item
								if ($assocState !== 'all' && $assocState !== 'out_of_date')
								{
									unset($items[$langCode]);
									continue;
								}

								$additional .= $addLink && $allow ? Text::_('COM_ASSOCIATIONS_UPDATE_ASSOCIATION') : '';
								$labelClass    = 'badge-warning';
								$target = $langCode . ':' . $items[$langCode]['id'] . ':edit';
								$update = true;

								/*
								When versions are disabled then the modified date is used for the master item.
								That means that when no changes were made and the master item has been saved the modified date has been changed.
								So the out of date state means in that case there might have been made changes and it is necessary to check manually and update the target.
								*/
								$masterInfo = $saveHistory
									? $masterInfoSpace . Text::_('JGLOBAL_ASSOCIATIONS_STATE_OUT_OF_DATE_DESC')
									: $masterInfoSpace . Text::_('JGLOBAL_ASSOCIATIONS_STATE_MIGHT_BE_OUT_OF_DATE_DESC');
							}
							else
							{
								// Don't display not corresponding item
								if ($assocState !== 'all' && $assocState !== 'up_to_date')
								{
									unset($items[$langCode]);
									continue;
								}

								$additional .= $addLink && $allow ? Text::_('COM_ASSOCIATIONS_EDIT_ASSOCIATION') : '';
								$masterInfo  = $masterInfoSpace . Text::_('JGLOBAL_ASSOCIATIONS_STATE_UP_TO_DATE_DESC');

								// For item types that do not use modified date or versions like menu items
								if (!$associatedModifiedMaster)
								{
									$masterInfo  = '';
								}
							}
						}
					}
				}
				else
				{
					$additional .= $addLink && $allow ? Text::_('COM_ASSOCIATIONS_EDIT_ASSOCIATION') : '';
				}
			}
			// Get html parameters for not associated items
			else
			{
				// Don't display any other children to the child item
				if ($globalMasterLang && ($itemLanguage != $globalMasterLang)
					&& ($langCode != $itemLanguage)
					&& ($langCode != $globalMasterLang))
				{
					continue;
				}

				$items[$langCode] = array();

				$title      = Text::_('COM_ASSOCIATIONS_NO_ASSOCIATION');
				$additional = $addLink ? Text::_('COM_ASSOCIATIONS_ADD_NEW_ASSOCIATION') : '';
				$labelClass = 'badge-secondary';
				$target     = $langCode . ':0:add';
				$allow      = $canCreate;

				if ($globalMasterLang)
				{
					if ($globalMasterLang === $langCode)
					{
						$labelClass .= ' master-item';
						$masterInfoSpace = $addLink ? '<br><br>' : '';
						$masterInfo  = $masterInfoSpace . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_ITEM');
						$target      = '';
					}
					else
					{
						// Don't display not corresponding item
						if ($assocState !== 'all' && $assocState !== 'not_associated')
						{
							unset($items[$langCode]);
							continue;
						}
					}

					// Change target, when there is no association with the global master language for the child item
					if ($globalMasterLang !== $itemLanguage)
					{
						$target = $globalMasterLang . ':0:add';
					}
				}
			}

			// Generate item Html.
			$options   = array(
				'option'   => 'com_associations',
				'view'     => 'association',
				'layout'   => $update ? 'update' : 'edit',
				'itemtype' => $extensionName . '.' . $typeName,
				'task'     => 'association.edit',
				'id'       => $masterId ?? $itemId,
				'target'   => $target,
			);

			$url     = Route::_('index.php?' . http_build_query($options));
			$url     = $allow && $addLink ? $url : '';
			$text    = strtoupper($language->sef);

			$tooltip = '<strong>' . htmlspecialchars($language->title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
				. htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '<br><br>' . $additional . $masterInfo;
			$classes = 'badge ' . $labelClass;

			$items[$langCode]['link'] = '<a href="' . $url . '" title="' . $language->title . '" class="' . $classes . '">' . $text . '</a>'
				. '<div role="tooltip">' . $tooltip . '</div>';

			// Reorder the array, so the master item gets to the first place
			if ($langCode === $globalMasterLang)
			{
				$items = array('master' => $items[$langCode]) + $items;
				unset($items[$langCode]);
			}
		}

		return LayoutHelper::render('joomla.content.associations', $items);
	}

	/**
	 * Get all extensions with associations support.
	 *
	 * @return  array  The extensions.
	 *
	 * @since  3.7.0
	 */
	public static function getSupportedExtensions()
	{
		if (!is_null(self::$extensionsSupport))
		{
			return self::$extensionsSupport;
		}

		self::$extensionsSupport = array();

		$extensions = self::getEnabledExtensions();

		foreach ($extensions as $extension)
		{
			$support = self::getSupportedExtension($extension->element);

			if ($support->get('associationssupport') === true)
			{
				self::$supportedExtensionsList[] = $extension->element;
			}

			self::$extensionsSupport[$extension->element] = $support;
		}

		return self::$extensionsSupport;
	}

	/**
	 * Get item context based on the item key.
	 *
	 * @param   string  $extensionName  The extension identifier.
	 *
	 * @return  \Joomla\Registry\Registry  The item properties.
	 *
	 * @since  3.7.0
	 */
	public static function getSupportedExtension($extensionName)
	{
		$result = new Registry;

		$result->def('component', $extensionName);
		$result->def('associationssupport', false);
		$result->def('helper', null);

		$helper = self::loadHelper($extensionName);

		if (!$helper)
		{
			return $result;
		}

		$result->set('helper', $helper);

		if ($helper->hasAssociationsSupport() === false)
		{
			return $result;
		}

		$result->set('associationssupport', true);

		// Get the translated titles.
		$languagePath = JPATH_ADMINISTRATOR . '/components/' . $extensionName;
		$lang         = Factory::getLanguage();

		$lang->load($extensionName . '.sys', JPATH_ADMINISTRATOR);
		$lang->load($extensionName . '.sys', $languagePath);
		$lang->load($extensionName, JPATH_ADMINISTRATOR);
		$lang->load($extensionName, $languagePath);

		$result->def('title', Text::_(strtoupper($extensionName)));

		// Get the supported types
		$types  = $helper->getItemTypes();
		$rTypes = array();

		foreach ($types as $typeName)
		{
			$details     = $helper->getType($typeName);
			$context     = 'component';
			$title       = $helper->getTypeTitle($typeName);
			$languageKey = $typeName;

			$typeNameExploded = explode('.', $typeName);

			if (array_pop($typeNameExploded) === 'category')
			{
				$languageKey = strtoupper($extensionName) . '_CATEGORIES';
				$context     = 'category';
			}

			if ($lang->hasKey(strtoupper($extensionName . '_' . $title . 'S')))
			{
				$languageKey = strtoupper($extensionName . '_' . $title . 'S');
			}

			$title = $lang->hasKey($languageKey) ? Text::_($languageKey) : Text::_('COM_ASSOCIATIONS_ITEMS');

			$rType = new Registry;

			$rType->def('name', $typeName);
			$rType->def('details', $details);
			$rType->def('title', $title);
			$rType->def('context', $context);

			$rTypes[$typeName] = $rType;
		}

		$result->def('types', $rTypes);

		return $result;
	}

	/**
	 * Get all installed and enabled extensions
	 *
	 * @return  mixed
	 *
	 * @since  3.7.0
	 */
	private static function getEnabledExtensions()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('enabled') . ' = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get all the content languages.
	 *
	 * @return  array  Array of objects all content languages by language code.
	 *
	 * @since  3.7.0
	 */
	public static function getContentLanguages()
	{
		return LanguageHelper::getContentLanguages(array(0, 1));
	}

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  boolean
	 *
	 * @since  3.7.0
	 */
	public static function allowEdit($extensionName, $typeName, $itemId)
	{
		if (!self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		if (method_exists($helper, 'allowEdit'))
		{
			return $helper->allowEdit($typeName, $itemId);
		}

		return Factory::getUser()->authorise('core.edit', $extensionName);
	}

	/**
	 * Check if user is allowed to create items.
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  3.7.0
	 */
	public static function allowAdd($extensionName, $typeName)
	{
		if (!self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		if (method_exists($helper, 'allowAdd'))
		{
			return $helper->allowAdd($typeName);
		}

		return Factory::getUser()->authorise('core.create', $extensionName);
	}

	/**
	 * Check if an item is checked out
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  boolean  True if item is checked out.
	 *
	 * @since   3.7.0
	 */
	public static function isCheckoutItem($extensionName, $typeName, $itemId)
	{
		if (!self::hasSupport($extensionName))
		{
			return false;
		}

		if (!self::typeSupportsCheckout($extensionName, $typeName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		if (method_exists($helper, 'isCheckoutItem'))
		{
			return $helper->isCheckoutItem($typeName, $itemId);
		}

		$item = self::getItem($extensionName, $typeName, $itemId);

		$checkedOutFieldName = $helper->getTypeFieldName($typeName, 'checked_out');

		return $item->{$checkedOutFieldName} != 0;
	}

	/**
	 * Check if user can checkin an item.
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since   3.7.0
	 */
	public static function canCheckinItem($extensionName, $typeName, $itemId)
	{
		if (!self::hasSupport($extensionName))
		{
			return false;
		}

		if (!self::typeSupportsCheckout($extensionName, $typeName))
		{
			return true;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		if (method_exists($helper, 'canCheckinItem'))
		{
			return $helper->canCheckinItem($typeName, $itemId);
		}

		$item = self::getItem($extensionName, $typeName, $itemId);

		$checkedOutFieldName = $helper->getTypeFieldName($typeName, 'checked_out');

		$userId = Factory::getUser()->id;

		return ($item->{$checkedOutFieldName} == $userId || $item->{$checkedOutFieldName} == 0);
	}

	/**
	 * Check if the type supports checkout
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  3.7.0
	 */
	public static function typeSupportsCheckout($extensionName, $typeName)
	{
		if (!self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		$support = $helper->getTypeSupport($typeName);

		return !empty($support['checkout']);
	}

	/**
	 * Get a table field name for a type
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   string  $fieldName      The item type
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  3.7.0
	 */
	public static function getTypeFieldName($extensionName, $typeName, $fieldName)
	{
		if (!self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		return $helper->getTypeFieldName($typeName, $fieldName);
	}

	/**
	 * Gets the language filter system plugin extension id.
	 *
	 * @return  integer  The language filter system plugin extension id.
	 *
	 * @since   3.7.2
	 */
	public static function getLanguagefilterPluginId()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}
}
